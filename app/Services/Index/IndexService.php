<?php

namespace App\Services\Index;

use App\Models\Students;
use App\Services\Service;

class IndexService extends Service
{
    protected $model = null;

    public function __construct(Students $model)
    {
        $this->model = $model;
    }

    public function model()
    {
        return $this->model;
    }

    public function find(array $params)
    {
        $fields = [
            'id',
            'name',
            'mobile',
            'age',
            'sex',
            'create_time',
            'update_time',
        ];
        $list = $this->model->select($fields)->orderBy('create_time', 'desc')->paginate(10);
        return $list;
    }

    public function get($id)
    {
        return $this->model->find($id);
    }

    public function create(array $params)
    {
        return $this->model->create($params);
    }

    public function update(array $params, $id)
    {
        $data = $this->get($id);
        return $data->update($params);
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

}
