<?php

namespace App\Http\Middleware;

use App\Repositories\Api\User\UserRepositoryInterface;
use App\Repositories\Backend\Admin\AdminRepositoryInterface;
use Closure;

class JWTCheck {
    private $adminRepository;
    private $userRepository;

    public function __construct(AdminRepositoryInterface $adminRepository , UserRepositoryInterface $userRepository)
    {
        $this->adminRepository = $adminRepository;
        $this->userRepository = $userRepository;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next , $type = 'admin') {
        $token = $request->header('authorization');
        if ($token != null) {
            $token = \Authorization::validateToken($token);
            if ($token) {
                $userId = $token->id;
                $expire = $token->expire;
                if ($expire > strtotime('now')) {
                    switch ($type) {
                        case 'user':
                            $user = $this->userRepository->authUserCheck();
                            break;
                        
                        default:
                            $user = $this->adminRepository->authAdminCheck();
                            break;
                    }
        
                    if (!$user) {
                        return _api_json('', ['message' => 'user not found'],401);
                    }
                } else {
                    return _api_json('',['message' => 'token expire'],401);
                }
            } else {
                return _api_json('',['message' => 'invalid token'],401);
            }
        } else {
            return _api_json('', ['message' => 'token not provided'],401);
        }
        return $next($request);
    }

}
