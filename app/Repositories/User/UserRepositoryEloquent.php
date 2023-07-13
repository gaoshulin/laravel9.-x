<?php

namespace App\Repositories\User;

use App\Constants\CommonConstant;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\DB;
use App\Repositories\User\UserRepository;
use App\Models\User;
use App\Validators\User\UserValidator;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace App\Repositories\User;
 */
class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {
        return UserValidator::class;
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
