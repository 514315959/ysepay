<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 商户进件Token获取接口
 */
class MerchantRegisterTokenGetCommand extends Command
{
    public $api_url = 'https://register.ysepay.com:2443/register_gateway/gateway.do';

    private $param_keys = [];

    public function build($params)
    {
        $this->setApiUrl($this->api_url)
            ->setBizContent($this->getParams($params, $this->param_keys));
    }

    public function parser(Response $response)
    {
        $res = $response->getBody()->getContents();
        $data = json_decode($res);

        $method = str_replace('.', '_', $this->method) . '_response';
        
        if (isset($data->unknow_response)) {
            return [
                'status' => false,
                'message' => $data->unknow_response->msg . '(' . $data->unknow_response->sub_msg . ')',
                'code' => $data->unknow_response->code,
                'sub_code' => $data->unknow_response->sub_code
            ];
        } else if ($data->$method->code != 10000 || strtoupper($data->$method->token_status) != 'TOKEN_GET_SUCCESS') {
            return [
                'status' => false,
                'message' => $data->$method->msg . '(' . $data->$method->sub_msg . ')',
                'code' => $data->$method->code,
                'sub_code' => $data->$method->sub_code
            ];
        }

        return [
            'status' => true,
            'message' => $data->$method->msg,
            'token' => $data->$method->token,
            'token_status' => $data->$method->token_status ?? ''
        ];
    }
}
