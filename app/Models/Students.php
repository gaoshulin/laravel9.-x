<?php

namespace App\Models;

use App\Models\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Students$.
 *
 * @package $NAMESPACE$
 */
class Students extends Model implements Transformable
{
    use TransformableTrait;

    const SEX_UN = 0;
    const SEX_BOY = 1;
    const SEX_GRID = 2;

    // Table name
    protected $table = 'students';

    // The primary key for the model.
    // 需要更改为当前 Model 实际主键名
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'mobile', 'age', 'sex'];

    public function sexAttr($ind = null)
    {
        $arr = [
            self::SEX_UN => '保密',
            self::SEX_BOY => '男',
            self::SEX_GRID => '女',
        ];

        if ($ind !== null) {
            return array_key_exists($ind, $arr) ? $arr[$ind] : $arr[self::SEX_UN];
        }
        return $arr;
    }
}
