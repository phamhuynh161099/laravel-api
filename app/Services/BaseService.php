<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Classes\FileUploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;


class BaseService
{

    protected $model;
    protected $fileUploader;

    public function __construct(
        Model $model
    ) {
        $this->model = $model;
    }

    protected function _request($request, $auth = null, $except = [], $customFolder = ['avatar'], $imageType = 'image')
    {

        $payload = $request->except(['_method', ...$except]);

        if ($auth) {
            if ($request->file('image')) {
                $this->fileUploader = new FileUploader($auth->email);
                $payload['image'] = $this->fileUploader->uploadFile($request->file('image'), $imageType, $customFolder);
            } else {
                if ($request->input('image')) {
                    $payload['image'] = str_replace(config('app.url') . 'storage', 'public', $payload['image']);
                }
            }
        }

        if ($request->input('password') && !empty($request->input('password'))) {
            $payload['password'] = Hash::make($payload['password']);
        }

        return $payload;
    }


    //* Hàm xử lý phân trang Model Eloquent
    public function pagination($params = [])
    {
        $params = $params[0];
        $query = $this->model->newQuery();


        $query->select($params['select'])
            ->condition($params['condition'] ?? [])
            ->keyword($params['keyword'] ?? '')
            ->relationCount($params['relationCount'] ?? [])
            ->orderBy($params['orderBy'][0], $params['orderBy'][1]);

        if ($params['perpage']) {
            return $query->paginate($params['perpage']);
        }

        return $query->get();
    }


    public function _findById(
        $modelId,
        $column = ['*'],
        $relation = []
    ) {
        return $this->model->select($column)->with($relation)->find($modelId);
    }

    public function delete($id)
    {
        return $this->_findById($id)->delete();
    }

    public function _deleteBatch($ids = [])
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function _updateBatch($payload = [], $whereIn = [], $condition = [])
    {
        return $this->model->whereIn($whereIn['whereInField'], $whereIn['whereInValue'])->update($payload);
    }

    public function _all($select = ['*'])
    {
        return $this->model->all($select);
    }

    public function _findByParentId($parentId, $field, $select)
    {
        return $this->model->where($field, '=', $parentId)->get($select);
    }
}
