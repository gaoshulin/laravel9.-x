<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/13 15:50
 */
namespace App\Services\Common;

use App\Services\Service;
use App\Support\Utils;
use Illuminate\Support\Facades\Log;
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Alipay\EasySDK\Kernel\Config;

class AlipayService extends Service
{

    /**
     * 构造支付宝支付参数
     *
     * AlipayService constructor.
     */
    public function __construct()
    {
        $config = config('pay.alipay');

        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';
        $options->appId = $config['app_id']; // 应用ID

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $privateKeyPath = $config['private_key'];
        $options->merchantPrivateKey = file_get_contents($privateKeyPath);

        // 注：采用非证书模式，设置支付宝公钥证书内容 - 这里需要支付宝公钥，而不是应用公钥
        // $publicKeyPath = $config['public_key'];
        $publicCertPath = $config['alipayCertPath'];
        $options->alipayPublicKey = file_get_contents($publicCertPath);

        // 注：采用证书模式，需要设置下面三个参数
        // $options->merchantCertPath = '<-- 请填写您的应用公钥证书文件路径 -->';
        // $options->alipayCertPath = '<-- 请填写您的支付宝公钥证书文件路径 -->';
        // $options->alipayRootCertPath = '<-- 请填写您的支付宝根证书文件路径 -->';

        // 设置参数（全局只需设置一次）
        Factory::setOptions($options);
    }

    /**
     * 统一收单交易创建接口 - 扫码支付
     *
     * @param array $params
     */
    public function aliPay(array $params)
    {
        try {
            // 统一收单交易接口
            $subject = 'iPhone13 128G'; // 订单标题
            $outTradeNo = Utils::orderNo(); // 订单号
            $totalAmount = '4999'; // 订单金额
            $buyerId = '2088002656718920'; // 买家支付宝用户ID
            $notifyUrl = ''; // 支付通知回调地址

            $result = Factory::payment()->common()->asyncNotify($notifyUrl)->create($subject, $outTradeNo, $totalAmount, $buyerId);
            $responseChecker = new ResponseChecker();

            // 处理响应或异常
            if ($responseChecker->success($result)) {
                return $result;
            } else {
                return "调用失败，原因：". $result->msg."，".$result->subMsg;
            }
        } catch (\Exception $e) {
            Log::error('Alipay failed.'. $e->getMessage());

            return "调用失败: ".$e->getMessage();
        }

    }

}
