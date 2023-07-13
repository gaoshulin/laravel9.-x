<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * 模型基类
 *
 * Class BaseModel
 * @package App\Models
 */
class Model extends BaseModel
{
    use HasFactory;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    // 设置默认写入时间为 int
    protected $dateFormat = 'U';

}
