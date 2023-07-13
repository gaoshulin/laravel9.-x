<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/12 15:55
 */

namespace App\Support;

/**
 * 微信支付方法
 */
class AesUtils
{
    const KEY_LENGTH_BYTE = 32;
    const AUTH_TAG_LENGTH_BYTE = 16;

    protected $config = [];

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 微信支付签名
     *
     * @param string $method 请求方式GET|POST
     * @param string $url url
     * @param string $body 报文主体
     * @return array
     */
    public function createSign($method = 'POST', $url = '', $body = '')
    {
        $timestamp = time(); // 时间戳
        $privateKey = $this->getMchKey(); //私钥
        $nonceStr = $this->getRandomStr();//随机串
        $urlParts = parse_url($url);
        $url = ($urlParts['path'] . (!empty($urlParts['query']) ? "?${$urlParts['query']}" : ""));

        // 构造签名串
        $str = $method."\n".$url."\n".$timestamp."\n".$nonceStr."\n".$body."\n"; //报文主体

        // 计算签名值
        openssl_sign($str, $rawSign, $privateKey, 'sha256WithRSAEncryption');
        $sign = base64_encode($rawSign);

        // 设置HTTP头
        $token = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->config['mchid'], $nonceStr, $timestamp, $this->config['serial_no'], $sign
        );
        $headers = [
            'Accept: application/json',
            'User-Agent: */*',
            'Content-Type: application/json; charset=utf-8',
            'Authorization: WECHATPAY2-SHA256-RSA2048 '.$token
        ];

        return $headers;
    }

    // 获取私钥
    public function getMchKey()
    {
        // path->私钥文件存放路径
        return openssl_pkey_get_private(file_get_contents($this->config['apiclient_key']));
    }

    /**
     * 获得随机字符串
     *
     * @param $len      integer       需要的长度
     * @param $special  bool      是否需要特殊符号
     * @return string       返回随机字符串
     */
    public function getRandomStr($len = self::KEY_LENGTH_BYTE, $special = false)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );

        if ($special) {
            $chars = array_merge($chars, array(
                "!", "@", "#", "$", "?", "|", "{", "/", ":", ";",
                "%", "^", "&", "*", "(", ")", "-", "_", "[", "]",
                "}", "<", ">", "~", "+", "=", ",", "."
            ));
        }

        $charsLen = count($chars) - 1;
        shuffle($chars);                            //打乱数组顺序
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $charsLen)];    //随机取出一位
        }
        return $str;
    }

    /**
     * curl get
     *
     * @param $url
     * @param array $headers
     */
    public function curlGet($url, $headers = array())
    {
        $info = curl_init();
        curl_setopt($info, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($info, CURLOPT_HEADER, 0);
        curl_setopt($info, CURLOPT_NOBODY, 0);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYHOST, false);
        //设置header头
        curl_setopt($info, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($info, CURLOPT_URL, $url);
        $output = curl_exec($info);
        curl_close($info);
        return $output;
    }

    /**
     * curl post
     *
     * @param $url
     * @param $data
     * @param $header
     */
    public function curlPostHttps($url, $data, $header = [])
    {
        // 模拟提交数据函数
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $tmpInfo;
    }

    /**
     * Decrypt AEAD_AES_256_GCM ciphertext
     *
     * @param string $associatedData AES GCM additional authentication data
     * @param string $nonceStr AES GCM nonce
     * @param string $ciphertext AES GCM cipher text
     *
     * @return string|bool      Decrypted string on success or FALSE on failure
     */
    public function decryptToString($associatedData, $nonceStr, $ciphertext)
    {
        $aesKey = $this->config['private_key']; // APIV3秘钥
        $ciphertext = \base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }

        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }

        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

            return \openssl_decrypt($ctext, 'aes-256-gcm', $aesKey, \OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
        }

        throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }

    /**
     * 获取微信支付平台证书内容
     *
     * @return bool|string
     */
    public function getWechatCertificates()
    {
        $headers = $this->createSign('GET', 'https://api.mch.weixin.qq.com/v3/certificates');
        $response = $this->curlGet('https://api.mch.weixin.qq.com/v3/certificates', $headers);
        $result = json_decode($response, true);

        $ret = '';
        if (isset($result['data']) && $result['data'][0]) {
            // 解密后的内容，就是证书内容
            $data = $result['data'][0];
            $ret = $this->decryptToString($data['encrypt_certificate']['associated_data'], $data['encrypt_certificate']['nonce'], $data['encrypt_certificate']['ciphertext']);
        }

        return $ret;
    }
}
