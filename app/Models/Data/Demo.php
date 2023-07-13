<?php

namespace App\Models\Data;

use App\Models\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Demo.
 *
 * @package namespace App\Models\Data;
 */
class Demo extends Model implements Transformable
{
    use TransformableTrait;

    // Table name
    protected $table = 'demo';

    // The primary key for the model.
    // 需要更改为当前 Model 实际主键名
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'is_completed'];
}
