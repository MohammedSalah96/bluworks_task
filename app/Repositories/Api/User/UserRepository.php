<?php 

namespace App\Repositories\Api\User;

use App\Models\User;
use App\Helpers\AUTHORIZATION;
use Illuminate\Http\Request;
use App\Repositories\Api\BaseRepository;
use App\Repositories\Api\BaseRepositoryInterface;

class UserRepository extends BaseRepository implements BaseRepositoryInterface, UserRepositoryInterface{
    
    private $user;
    public $types;
    
    public function __construct(User $user)
    {
        Parent::__construct();
        $this->user = $user;
        $this->types = $this->user->types;
    }

    public function userProfile()
    {
       return $this->user->join('location_translations', function($query){
            $query->on('users.city_id','=', 'location_translations.location_id')
            ->where('locale', $this->langCode);
       })
       ->where('users.id', $this->authUser()->id)
       ->select('users.*', 'location_translations.name as city')
       ->first();
    }

    public function register(Request $request)
    {
        $user = new $this->user;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->mobile = $request->input('mobile');
        $user->password = bcrypt($request->input('password'));
        $user->city_id = $request->input('city');
        $user->image = 'default.png';
       
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

    public function canPost()
    {
        return $this->authUser()->transform()->allowed_to_post;
    }

    public function authUserCheck()
    {
        $user = $this->user->where('users.active', true)
                    ->where('users.id', $this->authUser()->id)
                    ->first();

        return $user ?: false;
    }

    public function checkAuth($credentials)
    {
        if (is_numeric($credentials['username'])) {
            $field = 'mobile';
        } elseif (filter_var($credentials['username'], FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        }
        $user = $this->user->where('active', true)
            ->where($field, $credentials['username'])
            ->first();
        if ($user) {
            if (password_verify($credentials['password'], $user->password)) {
                return $user;
            }
        }
        return false;
    }


     public function checkUserForResest($mobile){
         return $this->user->where('mobile',$mobile)
                           ->where('active',true)
                           ->first();
     }

    public function updatePassword($user, $password){
        $user->password = bcrypt($password);
        $user->save();
    }

    public function updateProfile(Request $request)
    {
        $user = $this->authUser();
       
        if ($request->input('name')) {
            $user->name = $request->input('name');
        }
        if ($request->input('email')) {
            $user->email = $request->input('email');
        }
        if ($request->input('mobile')) {
            $user->mobile = $request->input('mobile');
        }
        if ($request->input('city')) {
            $user->city_id = $request->input('city');
        }

        if ($request->input('new_password')) {
            $user->password = bcrypt($request->input('new_password'));
        }
        if ($request->file('image')) {
            if ($user->image != 'default.png') {
                $user->deleteUploaded('users', $user->image);
            }
            $user->image = $this->user->upload($request->file('image'), 'users');
        }
        $user->save();
        
        return $user;
    }

    
}