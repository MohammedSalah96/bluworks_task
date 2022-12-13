<?php

namespace App\Http\Controllers\Api;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Repositories\Api\User\UserRepositoryInterface;
use DB;

class UsersController extends ApiController
{

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        try {
            $users = $this->userRepository->list($request);
            $users->getCollection()->transform(function ($user, $key) {
                return $user->transform();
            });
            return _api_json($users);
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json([], ['message' => $message], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = $this->userRepository->find($id);
            if (!$user) {
                $message = _lang('app.not_found');
                return _api_json(new \stdClass(), ['message' => $message], 404);
            }
            $user = $user->transform();
            return _api_json($user);
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $this->userRepository->find($id);
            if (!$user) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }
            $rules = [];
            if ($request->input('username')) {
                $rules['username'] = 'unique:users,username,' . $user->id;
            }
            if ($request->input('email')) {
                $rules['email'] = 'email|unique:users,email,' . $user->id;
            }
            if ($request->input('phone')) {
                $rules['phone'] = 'numeric|unique:users,phone,' . $user->id;
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors], 400);
            }
            $this->userRepository->update($request, $id, $user);

            $message = _lang('app.updated_successfully');
            return _api_json('', ['message' => $message]);
        } catch (\Exception $ex) {
            dd($ex);
            $message = _lang('app.something_went_wrong');
            return _api_json('', ['message' => $message], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $this->userRepository->find($id);
            if (!$user) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }
            DB::beginTransaction();
            $this->userRepository->delete($request, $id, $user);
            DB::commit();
            $message = _lang('app.deleted_successfully');
            return _api_json('', ['message' => $message]);
        } catch (\Exception $ex) {
            DB::rollback();
            if ($ex->getCode() == 23000) {
                $message = _lang('app.this_record_can_not_be_deleted_for_linking_to_other_records');
                return _api_json('', ['message' => $message], 400);
            }
            $message = _lang('app.something_went_wrong');
            return _api_json('', ['message' => $message], 400);
        }
    }
}
