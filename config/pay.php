<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/11 17:42
 */

return [
    'wechat_pay' => [
        'app_id'         => 'wx118783685d1c3c61', // appid
        'mch_id'         => '1617492772', // 商户号
        'private_key'    => '535f8a7542fd280038c235686372966e', // api秘钥
        'serial_no'      => '61933C5DB69FD818411176E030A6954DD229AEF5', // 证书序列号
        'apiclient_key'  => storage_path('pay/cert/wechat_apiclient_key.pem'), // 证书文件路径-私钥
        'apiclient_cert' => storage_path('pay/cert/wechat_apiclient_cert.pem'), // 证书文件路径-公钥
        'platform_cert'  => storage_path('pay/cert/wechat_platform_cert.pem'), // 微信支付平台证书
    ],

    'alipay' => [
        'app_id' => '2021003102620198', // appid
        'private_key' => storage_path('pay/cert/alipayCertPrivateKey.pem'), // 应用私钥文件路径
        'public_key'  => storage_path('pay/cert/alipayCertPublicKey.pem'), // 应用公钥文件路径
        'alipayCertPath' => '', // 支付宝公钥证书文件路径
        'alipayRootCertPath'  => '', // 支付宝根证书文件路径
    ]
];
