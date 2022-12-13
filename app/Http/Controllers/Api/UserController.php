<?php

namespace App\Http\Controllers\Api;

use Validator;
use Illuminate\Http\Request;
use App\Helpers\Authorization;
use App\Http\Controllers\ApiController;
use App\Repositories\Api\User\UserRepositoryInterface;

class UserController extends ApiController
{

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function getToken(Request $request)
    {
        try {
            $oldToken = $request->header('authorization');
            if ($oldToken) {
                $oldToken = Authorization::validateToken($oldToken);
                if ($oldToken) {
                    $newToken = new \stdClass();
                    $user = $this->userRepository->authUserCheck($oldToken->id);
                    if ($user) {
                        $newToken = $this->userRepository->generateToken($user->id);
                        return _api_json('', $newToken);
                    } else {
                        return _api_json('', ['message' => 'user not found'], 401);
                    }
                } else {
                    return _api_json('', ['message' => 'invalid token'], 401);
                }
            } else {
                return _api_json('', ['message' => 'token not provided'], 401);
            }
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json('', ['message' => $message], 400);
        }
    }

    protected function updateUser(Request $request)
    {
        try {
            $rules = array();
            $user = $this->userRepository->authUser();

            if ($request->input('username')) {
                $rules['username'] = "unique:users,username," . $user->id;
            }

            if ($request->input('email')) {
                $rules['email'] = "email|unique:users,email," . $user->id;
            }

            if ($request->input('phone')) {
                $rules['phone'] =  "unique:users,phone," . $user->id;
            }

            if ($request->input('old_password')) {
                $rules['new_password'] = "required|confirmed";
                $rules['new_password_confirmation'] =  "required";
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 400);
            }

            if ($request->input('old_password')) {
                if (!password_verify($request->input('old_password'), $user->password)) {
                    $message = _lang('app.old_password_is_not_correct');
                    return _api_json(new \stdClass(), ['message' => $message], 400);
                }
                if (password_verify($request->input('new_password'), $user->password)) {
                    $message = _lang('app.the_new_password_is_the_same_as_the_old_password');
                    return _api_json(new \stdClass(), ['message' => $message], 400);
                }
            }
            $user = $this->userRepository->updateProfile($request);
            $message = _lang('app.updated_successfully');
            return _api_json($user->profileTransform(), ['message' => $message]);
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            if ($ex instanceof \Twilio\Exceptions\RestException && $ex->getCode() == 21211) {
                $message =  _lang('app.this_mobile_number_is_not_valid');
            }
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }

    public function getUser()
    {
        try {
            $user = $this->userRepository->userProfile();
            return _api_json($user->profileTransform());
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }
}
