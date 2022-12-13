<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Repositories\Api\User\UserRepositoryInterface;
use Validator;

class LoginController extends ApiController
{

    private $rules = array(
        'username' => 'required',
        'password' => 'required'
    );

    private $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function login(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), $this->rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 400);
            }
            $user = $this->userRepository->checkAuth($request->only(['username', 'password']));
            if ($user) {
                $tokenDetails = $this->userRepository->issueToken($user);
                $message = _lang('app.all_is_cool_welcome_back');
                return _api_json(
                    $user->profileTransform(),
                    [
                        'message' => $message,
                        'token' => $tokenDetails['token'],
                        'expiration' => $tokenDetails['expiration']
                    ]
                );
            } else {
                $message = _lang('app.invalid_credentials');
                return _api_json(new \stdClass(), ['message' => $message], 400);
            }
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }
}
