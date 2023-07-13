<?php

namespace App\Repositories\Data;

use App\Constants\CommonConstant;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Data\DemoRepository;
use App\Models\Data\Demo;
use App\Validators\Data\DemoValidator;

/**
 * Class DemoRepositoryEloquent.
 *
 * @package namespace App\Repositories\Data;
 */
class DemoRepositoryEloquent extends BaseRepository implements DemoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Demo::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return DemoValidator::class;
    }

    /**
     * Find all results by params
     * @param  array      $params query params
     * @return mixed
     */
    public function findModels(array $params)
    {
        // your query

        return $this->paginate($params['per_page'] ?? CommonConstant::DEFAULT_PAGE_SIZE);
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
