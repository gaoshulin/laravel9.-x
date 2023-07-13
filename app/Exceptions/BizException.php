<?php

namespace App\Exceptions;

use App\Enums\StatusCode;

/**
 * BizException
 */
class BizException extends \Exception
{
    /**
     * 自定义状态码
     *
     * @var
     */
    protected $code;

    /**
     * 动态参数
     *
     * @var array
     */
    protected $params = [];

    // 需要随异常返回的数据
    protected $data = [];

    /**
     * 产生异常时的指定字段
     * @var string
     */
    protected $field = '';

    public function __construct($message = "", $code = -1, $params = [], $data = [], $field = '', \Throwable $previous = null)
    {
        // 读取状态码文本信息
        $message = StatusCode::getDesc($code, $params) ? : $message;

        $this->params = $params;
        $this->field = $field;
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getData()
    {
        return $this->data;
    }
}
