<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/13 15:48
 */
namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Api\Controller;
use App\Services\Common\AlipayService;
use Illuminate\Http\Request;

/**
 * 支付宝支付
 *
 * Class AlipayController
 * @package App\Http\Controllers\Api\Common
 */
class AlipayController extends Controller
{
    protected $service;

    /**
     * @param AlipayService $service
     */
    public function __construct(AlipayService $service)
    {
        $this->service  = $service;
    }

    /**
     * 支付宝支付接口
     *
     * @param Request $request
     */
    public function aliPay(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->service->aliPay($params);
        } catch (\Throwable $e) {
            return $this->errBadRequest($e->getMessage());
        }

        return $this->success($result);
    }

}
