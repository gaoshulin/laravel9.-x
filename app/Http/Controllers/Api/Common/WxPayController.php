<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/11 17:39
 */
namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Api\Controller;
use App\Services\Common\WxPayService;
use Illuminate\Http\Request;

/**
 * 微信支付类 - V3
 *
 * Class WxPayController
 * @package App\Http\Controllers\Api\Common
 */
class WxPayController extends Controller
{
    protected $service;

    /**
     * @param WxPayService $service
     */
    public function __construct(WxPayService $service)
    {
        $this->service  = $service;
    }

    /**
     * 微信支付统一下单接口
     *
     * @param Request $request
     */
    public function wechatPay(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->service->wechatPay($params);
        } catch (\Throwable $e) {
            return $this->errBadRequest($e->getMessage());
        }

        return $this->success($result);
    }

    /**
     * 微信支付 申请退款
     * @param Request $request
     */
    public function applyRefund(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->service->wechatRefund($params);
        } catch (\Throwable $e) {
            return $this->errBadRequest($e->getMessageBag());
        }

        return $this->success($result);
    }

    /**
     * 微信支付成功回调方法
     *
     * @param Request $request
     */
    public function wechatPayReceive(Request $request)
    {
        // $params = $request->all();

        // 此处接收微信服务端回调的 json 数据
        $input = file_get_contents('php://input');
        $params = json_decode($input,true);

        $this->service->wechatPayReceive($params);
    }

}

