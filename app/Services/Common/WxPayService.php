<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/11 17:40
 */
namespace App\Services\Common;

use App\Support\AesUtils;
use App\Support\Utils;
use App\Services\Service;
use Illuminate\Support\Facades\Log;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;
use WeChatPay\Formatter;
use WeChatPay\Crypto\AesGcm;

class WxPayService extends Service
{
    static $config = [];
    static $instance = null;

    /**
     * WxPayService constructor.
     */
    public function __construct()
    {
        self::$config = config('pay.wechat_pay');

        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = 'file://'.self::$config['apiclient_key'];
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        // 备注：这里是一个大坑，官方文档说明不清楚。这里需要的是微信支付平台的证书，不是下载的商户私钥、公钥文件的正式。
        // 可以使用下面的方法 getWechatCertificates() 生成微信支付平台证书,然后保存到文件中
        $platformCertificateFilePath = 'file://'.self::$config['platform_cert'];
        $platformPublicKeyInstance   = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = self::$config['serial_no'];

        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

        $params = [
            'mchid'      => self::$config['mch_id'],
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformCertificateSerial  => $platformPublicKeyInstance,
            ],
        ];

        // 构造一个 APIv3 客户端实例
        self::$instance = Builder::factory($params);
    }

    /**
     * 微信支付统一下单接口
     *
     * @param array $params
     */
    public function wechatPay(array $params)
    {
        // TODO: 实际业务逻辑处理

        try {
            // 微信支付参数
            $data = [
                'appid' => self::$config['app_id'],
                'mchid' => self::$config['mch_id'],
                'out_trade_no' => Utils::orderNo(),
                'description' => '微信支付',
                'notify_url' => config('app.url').'/api/wechat_pay/receive', // 支付回调地址
                'amount' => [
                    'total' => 1,
                    'currency' => 'CNY'
                ],
                'payer' => [
                    'openid' => 'ohh0j5GuvFA2LZ1pWcTXi5Ui5Uqk',
                ]
            ];

            // 调用jsapi 统一下单接口
            $resp = self::$instance->chain('v3/pay/transactions/jsapi')->post([
                'json' => $data
            ]);

            $result = json_decode($resp->getBody(), true);

            // 获取前端支付需要的参数
            $prepayData = $this->prepayParams($result['prepay_id']);

            Log::info('Wechat reserve payment.', $prepayData);

            return $prepayData;
        } catch (\Exception $e) {
            // 异常错误处理
            $error = $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                $error = $r->getStatusCode().' '.$r->getReasonPhrase().':'.$r->getBody();
            }

            Log::error('Wechat reserve failed: '. $error);

            return $error;
        }
    }

    /**
     * 微信退款接口
     *
     * @param array $params
     */
    public function wechatRefund(array $params)
    {
        // TODO: 根据实际业务逻辑处理

        try {
            // 微信退款参数
            $data = [
                // 'out_trade_no' => '', // 商户订单号 - 支付订单的订单号
                'transaction_id' => '4200001532202205068164304563', // 商户订单号 - out_trade_no 二选一
                'out_refund_no' => Utils::orderNo(), // 退款订单号
                'funds_account' => 'AVAILABLE', // 退款资金来源
                'reason' => '用户申请退款', // 退款原因
                'amount' => [
                    'total' => 1, // 订单总金额
                    'refund' => 1, // 退款金额
                    'currency' => 'CNY', // 退款币种
                ]
            ];
            // 调用退款接口
            $resp = self::$instance->chain('v3/refund/domestic/refunds')->post([
                'json' => $data
            ]);

            $result = json_decode($resp->getBody(), true);

            Log::info('Wechat refund successful.', $result);

            return $result;
        } catch (\Exception $e) {
            // 进行错误处理
            $message = $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                $message = $r->getStatusCode() . ' ' . $r->getReasonPhrase().':'.$r->getBody();
            }
            Log::error('Wechat refund failed: '.$message);

            return $message;
        }
    }

    /**
     * 微信支付回调地址
     *
     * @param array $params
     */
    public function wechatPayReceive(array $params)
    {
        // 使用PHP7的数据解构语法，从Array中解构并赋值变量
        ['resource' => [
            'ciphertext'      => $ciphertext,
            'nonce'           => $nonce,
            'associated_data' => $aad
        ]] = $params;

        // 加密文本消息解密
        $inBodyResource = AesGcm::decrypt($ciphertext, self::$config['private_key'], $nonce, $aad);
        // 把解密后的文本转换为数组
        $result = json_decode($inBodyResource, true);
        // var_dump($result);exit();

        if (isset($result['trade_state']) && $result['trade_state'] == 'SUCCESS') {
            // 支付成功 操作

            Log::info('wechat pay successfully.', $result);

            // 通知微信服务端支付成功
            $this->returnSuccess();
        } else {
            // 支付失败
            Log::error('wechat pay failed.', $result);

            echo "微信支付失败"; exit;
        }
    }

    /**
     * 返回前端调取支付的参数
     *
     * @param $prepayId
     * @return array
     */
    public function prepayParams($prepayId)
    {
        $params = [
            'appId'     => self::$config['app_id'],
            'timeStamp' => (string)Formatter::timestamp(),
            'nonceStr'  => Formatter::nonce(),
            'package'   => 'prepay_id='.$prepayId,
            'signType'  => 'RSA'
        ];

        // 获取私钥内容
        $merchantPrivateKeyFilePath = 'file://'.self::$config['apiclient_key'];
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        // 前端调起支付需要的签名
        $params['paySign'] = Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($params)),
            $merchantPrivateKeyInstance
        );

        return $params;
    }

    /**
     * 下载微信支付平台证书
     *
     * @return mixed
     */
    public function getWechatCertificates()
    {
        $AesUtils = new AesUtils(self::$config);
        $result = $AesUtils->getWechatCertificates();

        // 返回的内容就是 证书内容
        var_dump($result);
    }

    /**
     * 给微信发送确认订单金额和签名正确，SUCCESS信息
     */
    public function returnSuccess()
    {
        $return["code"]    = "SUCCESS";
        $return["message"] = "成功";
        $ext = json_encode($return);
        echo $ext;exit;
    }
}
