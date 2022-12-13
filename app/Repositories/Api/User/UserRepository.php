<?php

namespace App\Repositories\Api\User;

use App\Models\User;
use App\Helpers\AUTHORIZATION;
use Illuminate\Http\Request;
use App\Repositories\Api\BaseRepository;
use App\Repositories\Api\BaseRepositoryInterface;

class UserRepository extends BaseRepository implements BaseRepositoryInterface, UserRepositoryInterface
{

    private $user;

    public function __construct(User $user)
    {
        Parent::__construct();
        $this->user = $user;
    }

    public function userProfile()
    {
        return $this->authUser()->profileTransform();
    }

    public function register(Request $request)
    {
        $user = new $this->user;
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->password = bcrypt($request->input('password'));
        $user->dob = $request->input('dob');
        $user->save();
        return $user;
    }


    public function issueToken($user)
    {
        return $this->generateToken($user->id);
    }

    public function generateToken($id = null)
    {
        $expireNo = 1;
        $expireType = 'day';
        $expirationInSeconds =  strtotime('+' . $expireNo . $expireType);

        $token = new \stdClass();
        $token->id = $id ?: $this->authUser()->id;
        $token->expire =  $expirationInSeconds;

        return [
            'token' =>  AUTHORIZATION::generateToken($token),
            'expiration' =>   $expirationInSeconds
        ];
    }

    public function authUserCheck()
    {
        $user = $this->user->where('active', true)
            ->where('id', $this->authUser()->id)
            ->first();

        return $user ?: false;
    }

    public function checkAuth($credentials)
    {
        $user = $this->user->where('active', true)
            ->where('username', $credentials['username'])
            ->first();
        if ($user) {
            if (password_verify($credentials['password'], $user->password)) {
                return $user;
            }
        }
        return false;
    }

    public function updatePassword($user, $password)
    {
        $user->password = bcrypt($password);
        $user->save();
    }

    public function updateProfile(Request $request)
    {
        $user = $this->authUser();

        if ($request->input('username')) {
            $user->username = $request->input('username');
        }
        if ($request->input('email')) {
            $user->email = $request->input('email');
        }
        if ($request->input('phone')) {
            $user->phone = $request->input('phone');
        }
        if ($request->input('dob')) {
            $user->dob = $request->input('dob');
        }

        if ($request->input('new_password')) {
            $user->password = bcrypt($request->input('new_password'));
        }
        $user->save();

        return $user;
    }


    public function list(Request $request)
    {
        $users =  $this->user->select('users.*');
        if ($request->input('search')) {
            $users->whereRaw(handleKeywordWhere(['users.username', 'users.email', 'users.phone'], $request->input('search')));
        }
        return $users = $users->paginate($this->limit);
    }

    public function find($id, array $conditions = [])
    {
        if (!empty($conditions)) {
            return $this->user->where($conditions)->where('id', $id)->first();
        }
        return $this->user->find($id);
    }

    public function update(Request $request, $id, $user)
    {
        if ($request->input('name')) {
            $user->name = $request->input('name');
        }
        if ($request->input('email')) {
            $user->email = $request->input('email');
        }
        if ($request->input('phone')) {
            $user->mobile = $request->input('phone');
        }
        if ($request->input('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        if ($request->has('active')) {
            $user->active = $request->input('active');
        }
        if ($request->input('dob')) {
            $user->dob = $request->input('dob');
        }

        $user->save();

        return $user;
    }

    public function delete(Request $request, $id, $user)
    {
        return $user->delete();
    }
}
