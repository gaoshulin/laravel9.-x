<?php

namespace App\Http\Controllers\Api\Common;

use Exception;
use App\Enums\StatusCode;
use App\Exceptions\BizException;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Common\WechatService;
use App\Services\User\UserService;

/**
 * Class WechatController.
 *
 * @package namespace App\Http\Controllers\Api\Common;
 */
class WechatController extends Controller
{
    /**
     * @var WechatService
     */
    protected $service;

    /**
     * 用户 Service
     */
    protected $userService;

    /**
     * WechatController constructor.
     *
     * @param WechatService $service
     */
    public function __construct(WechatService $service, UserService $userService)
    {
        $this->service  = $service;
        $this->userService = $userService;
    }

    /**
     * 接收微信推送消息
     */
    public function serve()
    {
        return $this->service->serve();
    }

    /**
     * 生成微信二维码
     *
     * @param Request $request
     */
    public function qrcode(Request $request)
    {
        $params = $request->all();

        // 查询 cookie，如果没有就重新生成一次
        $sessionFlag = $params['session_flag'] ?? $this->service->generateSessionFlag(['ip' => $request->ip()]);

        $result = $this->service->qrcode($sessionFlag);
        return $this->success($result);
    }

    /**
     * 获取微信网页授权
     *
     * @param Request $request
     */
    public function getUserinfo(Request $request)
    {
        $params = $request->all();

        // 微信回调URL携带的 code验证
        $rules = ['code' => ['required']];
        Validator::make($params, $rules)->validate();

        // 微信回调URL携带的 code
        $code = $params['code'];
        $ret = $this->service->getUserinfo($code);
        return $this->success($ret);
    }

    /**
     * 获取公众号菜单
     * @return mixed
     */
    public function getMenu()
    {
        $response = $this->service->getMenu();
        return $this->success($response);
    }

    /**
     * 创建微信菜单
     */
    public function createMenu(Request $request)
    {
        $response = $this->service->createMenu($params = $request->all());
        return $this->success($response);
    }

    /**
     * 获取微信永久素材
     */
    public function getMaterial()
    {
        $response = $this->service->getMaterial();
        return $this->success($response);
    }

    /**
     * 获取 JS-SDK 配置
     * @param Request $request
     */
    public function getConfig(Request $request)
    {
        $params = $request->all();

        $result = $this->service->getJsSdkConfig($params);
        return $this->success($result);
    }

}
