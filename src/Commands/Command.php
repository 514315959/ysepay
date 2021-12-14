<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shion\YsePay\Utils\Signature;
use Illuminate\Support\Facades\Log;

abstract class Command
{
    /**
     * 平台商户号
     * 
     * @var string
     */
    protected $partner_id;

    /**
     * 私钥证书
     * 
     * @var string
     */
    protected $private_key;

    /**
     * 私钥证书密码
     * 
     * @var string
     */
    protected $private_key_passwd;

    /**
     * 网关地址
     * 
     * @param string
     */
    protected $api_url;

    /**
     * 接口方法
     * 
     * @var string
     */
    protected $method;

    /**
     * 编码
     * 
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * 签名方式
     * 
     * @var string
     */
    protected $sign_type = 'RSA';

    /**
     * 业务参数
     * 
     * @var array
     */
    protected $biz_content = [];

    /**
     * 异步回调地址
     * 
     * @var string
     */
    protected $notify_url = 'http://127.0.0.1';

    /**
     * 交易类型，说明：1或者空：即时到账，2：担保交易
     * 
     * @var string
     */
    protected $tran_type;

    /**
     * API接口版本
     * 
     * @var string
     */
    protected $version = '3.0';

    /**
     * 构造业务参数
     * 
     * @param array $params
     */
    abstract public function build($params);

    /**
     * 解析接口返回内容
     * 
     * @param Response $response
     */
    abstract public function parser(Response $response);

    /**
     * 设置平台商户号
     * 
     * @param string $partner_id
     * 
     * @return Command
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * 设置私钥证书
     * 
     * @param string $private_key
     * 
     * @return Command
     */
    public function setPrivateKey($private_key)
    {
        $this->private_key = $private_key;
        return $this;
    }

    /**
     * 设置私钥证书密码
     * 
     * @param string $private_key_passwd
     * 
     * @return Command
     */
    public function setPrivateKeyPasswd($private_key_passwd)
    {
        $this->private_key_passwd = $private_key_passwd;
        return $this;
    }

    /**
     * 设置网关地址
     * 
     * @param string $api_url
     */
    public function setApiUrl($api_url)
    {
        $this->api_url = $api_url;
        return $this;
    }

    /**
     * 设置接口方法
     * 
     * @param string @method
     * 
     * @return Command
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * 设置接口版本
     * 
     * @param string @version
     * 
     * @return Command
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * 设置业务参数
     * 
     * @param array $biz_content
     * 
     * @return Command
     */
    public function setBizContent($biz_content)
    {
        $this->biz_content = $biz_content;
        return $this;
    }

    /**
     * 设置异步回调地址
     * 
     * @param string $notify_url
     * 
     * @return Command
     */
    public function setNotifyUrl($notify_url)
    {
        $this->notify_url = $notify_url;
        return $this;
    }

    /**
     * 设置交易类型
     * 
     * @param string $tran_type
     */
    public function setTranType($tran_type)
    {
        $this->tran_type = $tran_type;
        return $this;
    }

    /**
     * 获取指定key的参数
     * 
     * @param array $params
     * @param array $keys
     * 
     * @return array
     */
    public function getParams($params, $keys)
    {
        $res = [];
        foreach ($params as $k => $v) {
            if (in_array($k, $keys)) {
                $res[$k] = $v;
            }
        }

        return $res;
    }

    /**
     * 执行请求接口
     * 
     * @param array $params
     * 
     * @return array
     */
    public function exec($params)
    {
        $this->build($params);
        $data = $this->getRequestParams();

        $options = [];
        if ($this->method == 'ysepay.merchant.register.image.upload') {
            $options['multipart'] = $this->biz_content;
        } else {
            $options['form_params'] = $this->getRequestParams();
        }

        Log::info('ysepay参数:', $options);

        $client = new Client();
        $response = $client->request('POST', $this->api_url, $options);

        return $this->parser($response);
    }

    /**
     * 获取请求参数
     * 
     * @return array
     */
    private function getRequestParams()
    {
        $params = [
            'method' => $this->method,
            'partner_id' => $this->partner_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'charset' => $this->charset,
            'sign_type' => $this->sign_type,
            'notify_url' => $this->notify_url,
            'version' => $this->version,
            'biz_content' => empty($this->biz_content) ? json_encode($this->biz_content, JSON_FORCE_OBJECT) : json_encode($this->biz_content),
        ];

        if (!empty($this->tran_type)) {
            $params['tran_type'] = $this->tran_type;
        }

        $params['sign'] = Signature::RSASign($params, $this->private_key, $this->private_key_passwd);

        return $params;
    }
}
