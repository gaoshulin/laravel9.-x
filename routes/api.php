<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Data\DemosController;
use App\Http\Controllers\Api\User\UsersController;
use App\Http\Controllers\Api\Common\WechatController;
use App\Http\Controllers\Api\Common\WxPayController;
use App\Http\Controllers\Api\Common\AlipayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 接口访问地址：http://127.0.0.1:8000/api/demo/index
Route::middleware(['response', 'logger'])->group(function () {
    // 用户
    Route::namespace('Api\User')->group(function () {
        // 注册
        Route::post('/users/signup', [UsersController::class, 'signup']);
        // 登录
        Route::post('/users/login', [UsersController::class, 'login']);

        // 需要登录鉴权的接口
        Route::middleware('auth:sanctum')->group(function () {
            // 获取用户信息
            Route::get('/users/info', [UsersController::class, 'userinfo']);
            // 更改当前用户信息
            Route::put('/users/update', [UsersController::class, 'update']);
            // 退出登录
            Route::get('/users/logout', [UsersController::class, 'logout']);
        });
    });

    // 数据
    Route::namespace('Api\Data')->group(function () {
        // 不需要登录的接口
        Route::get('/demo/index', [DemosController::class, 'list']);
        Route::get('/demo/details/{id}', [DemosController::class, 'show']);
        Route::post('/demo/add', [DemosController::class, 'store']);
        Route::post('/demo/update/{id}', [DemosController::class, 'update']);
        Route::delete('/demo/delete/{id}', [DemosController::class, 'destroy']);
        // 调用队列
        Route::get('/demo/jobs', [DemosController::class, 'jobs']);

        // 订单支付
        Route::prefix('orders')->group(function () {
            // 微信支付
            Route::post('/pay/wechat-pay', [WxPayController::class, 'wechatPay']);
            Route::post('/pay/wechat-refund', [WxPayController::class, 'applyRefund']);

            // 支付宝支付
            Route::post('/pay/alipay', [AlipayController::class, 'aliPay']);
        });
    });
});

// 数据导出，无需标准响应格式路由
//Route::middleware(['web', 'token'])->group(function () {
//    Route::get('demo/list/export', [IndexController::class, 'exportList']);
//});

Route::middleware(['web'])->group(function () {
    Route::get('demo/list/export', [DemosController::class, 'exportList']);
});

// 无需标准响应格式路由
Route::namespace('Api\Common')->group(function () {
    // 微信授权 http://127.0.0.1:8000/api/wechat/notify
    Route::any('/wechat/notify', [WeChatController::class, 'serve']);

    // 微信支付回调地址
    Route::any('/wechat_pay/receive', [WxPayController::class, 'wechatPayReceive']);
});

