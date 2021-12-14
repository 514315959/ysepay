<?php

namespace Shion\YsePay\Utils;

use Shion\YsePay\Exception\YsePayException;

/**
 * 银盛签名
 */
class Signature
{
    /**
     * 获取私钥证书
     * 
     * @param string $private_key
     * @param string 
     */
    private static function getPrivateKey($private_key, $private_key_passwd)
    {
        $cert_info = '';

        if (!$cert_store = file_get_contents($private_key)) {
            throw new YsePayException('私钥证书文件不存在');
        }

        $res = openssl_pkcs12_read($cert_store, $cert_info, $private_key_passwd);
        if ($res === false) {
            throw new YsePayException('读取私钥证书文件失败');
        }

        return $cert_info;
    }

    /**
     * 获取公钥证书
     * @param string $public_key
     * @param string 
     */
    private static function getPublicKey($public_key)
    {
        $file_res = file_get_contents($public_key);

        // 格式转化为标准PEM证书格式
        $encryptPubCertKey = '-----BEGIN CERTIFICATE-----' . PHP_EOL
            . chunk_split(base64_encode($file_res), 64, PHP_EOL)
            . '-----END CERTIFICATE-----' . PHP_EOL;
        $res = openssl_pkey_get_public($encryptPubCertKey);

        if ($res === false) {
            throw new YsePayException('读取公钥证书失败');
        }

        return $res;
    }

    /**
     * 获取参数的待签名字符串
     * 
     * @param array $data 待签名数据
     * 
     * @return string
     */
    private static function getSignStr($data)
    {
        if (!empty($data)) {

            unset($data['sign']);
            $filtrfunction = function ($val) {
                if ($val === '' || $val === null) {
                    return false;
                }
                return true;
            };
            $data = array_filter($data, $filtrfunction);
            ksort($data);
            $paramsToBeSigned = [];

            foreach ($data as $k => $v) {
                if (is_array($data[$k])) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                } else if (trim($v) == "") {
                    continue;
                }
                if (is_bool($v)) {
                    $paramsToBeSigned[] = $v ? "$k=true" : "$k=false";
                } else {
                    $paramsToBeSigned[] = $k . '=' . $v;
                }
            }

            unset($k, $v);
            //签名字符串
            $stringToBeSigned = implode('&', $paramsToBeSigned);
            return $stringToBeSigned;
        }

        return null;
    }

    /**
     * 生成RSA签名
     * 
     * @param array $data 待签名数据
     * @param array $private_key 私钥证书
     * @param string $private_key_passwd 私钥证书密码
     * 
     * @return string
     */
    public static function RSASign($data, $private_key, $private_key_passwd)
    {
        $unsign_str = self::getSignStr($data);
        $private_key = self::getPrivateKey($private_key, $private_key_passwd);

        $signature = '';
        $res = openssl_sign($unsign_str, $signature, $private_key['pkey'], OPENSSL_ALGO_SHA1);
        if ($res === false) {
            throw new YsePayException('证书签名失败');
        }

        return base64_encode($signature);
    }


    /**
     * RSA签名验证
     * 
     * @param array $data 待签名数据
     * @param array $public_key 公钥证书
     * 
     * @return string
     */
    public static function verifySign($data, $public_key, $sign)
    {
        $unsign_str = self::getSignStr($data);

        if (!is_string($unsign_str) || !is_string($sign)) {
            return false;
        }

        return (bool)openssl_verify(
            $unsign_str,
            base64_decode($sign),
            self::getPublicKey($public_key),
            OPENSSL_ALGO_SHA1
        );
    }

    /**
     * DES加密（DES/ECB/PKCS5Padding）
     */
    public static function DesEncrypt($data, $key)
    {
        $res = @openssl_encrypt($data, 'DES-ECB', $key, 1, null);
        return base64_encode($res);
    }
}
