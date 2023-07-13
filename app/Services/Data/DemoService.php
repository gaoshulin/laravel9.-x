<?php

namespace App\Services\Data;

use App\Repositories\Data\DemoRepositoryEloquent as DemoRepository;
use App\Services\Service;
use App\Jobs\DemoJobs;

class DemoService extends Service
{
     /**
      * @var DemoRepository
      */
     protected $repository;

	public function __construct(DemoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function find(array $params)
    {
        $list = $this->repository->findModels($params);
        return $list;
    }

    public function get($id)
    {
        return $this->repository->findOrFail($id);
    }

    public function getBy($field, $value)
    {
        return $this->repository->findByField($field, $value)->first();
    }

    public function create(array $params)
    {
        return $this->repository->create($params);
    }

    public function update(array $params, $id)
    {
        return $this->repository->update($params, $id);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    /**
     * 队列处理
     *
     * @param array $params
     */
    public function demoJobs(array $params)
    {
        $data = $this->find($params);

        // 调用队列
        DemoJobs::dispatch($data);

        // 延迟调度
        //  DemoJobs::dispatch($data)->delay(now()->addMinutes(3));
    }
}
