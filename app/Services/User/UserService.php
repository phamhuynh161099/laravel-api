<?php

namespace App\Services\User;

use App\Classes\FileUploader;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Enums\Status;
use App\Models\User;
use Hash;

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
            $payload = $this->createPayload($request, $auth);
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
            $payload = $this->updatePayload($request, $auth);
            // dd($this->model->find($id));

            $user = $this->model->find($id)->update($payload);

            DB::commit();
            return [
                'user' => $this->model->find($id),
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


    public function createPayload($request, $auth) {

        $payload = [
            'name'=> $request->input('name'),
            'email'=>$request->input('email'),
            'password'=>Hash::make($request->input('password')),
            'created_by'=>auth()->user()->name,
        ];

        if($request->file('images')){
            $arrFileImage = $request->file('images');
            $this->fileUploader = new FileUploader($auth->email);
            for ($i=0; $i < count($arrFileImage); $i++) {
                $image = $arrFileImage[$i];
                $payload['image'] = $this->fileUploader->uploadFile($image, 'image', ['avatar']);
            }
        }else{
            $payload['image'] = null;
        }

        return $payload;
    }


    public function updatePayload($request, $auth) {

        $payload = [
            'name'=> $request->input('name'),
            'email'=>$request->input('email'),
            'updated_by'=>auth()->user()->name,
        ];

        if ($request->input('password')) {
            $payload['password'] = Hash::make($request->input('password'));
        }

        if($request->file('images')){
            $arrFileImage = $request->file('images');
            $this->fileUploader = new FileUploader($auth->email);
            for ($i=0; $i < count($arrFileImage); $i++) {
                $image = $arrFileImage[$i];
                $payload['image'] = $this->fileUploader->uploadFile($image, 'image', ['avatar']);
            }
        }else{
            // do nothing
        }

        return $payload;
    }
}
