<?php

namespace App\Http\Controllers\Api\Auth;

use DB;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Repositories\Api\User\UserRepositoryInterface;

class RegisterController extends ApiController
{

    private $rules = [
        'username' => 'required|regex:/^\S*$/u',
        'email' => 'email|unique:users,email',
        'phone' => 'numeric|unique:users,phone',
        'password' => 'required',
        'dob' => 'required'
    ];


    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 400);
            }
            $user = $this->userRepository->register($request);
            $tokenDetails = $this->userRepository->issueToken($user);
            $message = _lang('app.all_is_cool_welcome');
            return _api_json(
                $user->profileTransform(),
                [
                    'message' => $message,
                    'token' => $tokenDetails['token'],
                    'expiration' => $tokenDetails['expiration']
                ]
            );
        } catch (\Exception $ex) {
            dd($ex);
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }
}
