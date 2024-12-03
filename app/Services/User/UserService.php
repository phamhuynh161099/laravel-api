<?php

namespace App\Services\User;

use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Enums\Status;
use App\Models\User;

class UserService extends BaseService
{

    protected $model;
    protected $fileUploader;

    public function __construct(
        User $user
    ) {
        $this->model = $user;
    }

    private function paginateAgrument($request)
    {
        return [
            'perpage' => $request->input('perpage') ?? 10,
            'keyword' => [
                'search' => $request->input('keyword') ?? '',
                'field' => ['name', 'email', 'address', 'phone']
            ],
            'condition' => [
                // 'publish' => $request->integer('publish'),
                // 'user_catalogue_id' => $request->integer('user_catalogue_id'),
            ],
            'select' => ['*'],
            'orderBy' => $request->input('sort') ? explode(',', $request->input('sort')) : ['id', 'desc'],
        ];
    }

    public function paginate($request)
    {

        $agrument = $this->paginateAgrument($request);
        $users = $this->pagination([$agrument]);
        return $users;
    }


    public function create($request, $auth)
    {
        DB::beginTransaction();
        try {
            $except = ['confirmPassword'];
            $payload = $this->_request($request, $auth, $except);

            $user = $this->model->create($payload);
            DB::commit();

            return [
                'user' => $user,
                'code' => 'SUCCESS'
            ];
        } catch (\Exception $e) {

            DB::rollback();
            return [
                'code' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }

    public function update($request, $id, $auth)
    {
        DB::beginTransaction();
        try {
            $except = ['confirmPassword'];
            $payload = $this->_request($request, $auth, $except);

            $user = $this->model->update($id, $payload);
            DB::commit();
            return [
                'user' => $user,
                'code' => 'SUCCESS'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'code' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $this->model->delete($id);
            DB::commit();
            return [
                'code' => 'SUCCESS'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'code' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }
}
