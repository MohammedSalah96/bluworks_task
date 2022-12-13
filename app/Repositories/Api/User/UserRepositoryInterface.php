<?php

namespace App\Repositories\Api\User;

use Illuminate\Http\Request;

interface UserRepositoryInterface
{

    public function register(Request $reuqest);
    public function userProfile();
    public function checkAuth(array $credentials);
    public function issueToken($user);

    public function updatePassword($user, $password);

    public function updateProfile(Request $request);
    public function authUserCheck();
    public function generateToken($id = null);

    public function list(Request $request);
    public function find($id, array $conditions = []);
    public function update(Request $request, $id, $user);
    public function delete(Request $request, $id, $user);
}
