<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 商户进件图片上传接口
 */
class MerchantRegisterImageUploadCommand extends Command
{
    public $api_url = 'https://uploadApi.ysepay.com:2443/yspay-upload-service?method=upload';

    private $param_keys = ['picType', 'picFile', 'token', 'superUsercode'];

    private function buildMultipart($params)
    {
        $res = [];
        foreach ($params as $k => $v) {

            if ($k == 'picFile') {
                $data = [
                    'name' => $k,
                    'contents' => fopen($v, 'r'),
                    'filename' => md5($v) . time() . '.jpg'
                ];
            } else {
                $data = [
                    'name' => $k,
                    'contents' => $v,
                ];
            }
            $res[] = $data;
        }
        return $res;
    }

    public function build($params)
    {
        $params = $this->getParams($params, $this->param_keys);
        $params = $this->buildMultipart($params);

        $this->setApiUrl($this->api_url)
            ->setBizContent($params);
    }

    public function parser(Response $response)
    {
        $res = $response->getBody()->getContents();
        $data = json_decode($res);
        if (!isset($data->isSuccess) || $data->isSuccess == false) {
            return [
                'status' => false,
                'message' => $data->errorMsg ?? '上传失败',
                'code' => $data->errorCode ?? '0',
                'sub_code' => '500'
            ];
        }

        return [
            'status' => true,
            'message' => '上传图片成功',
            'token' => $data->token,
        ];
    }
}
