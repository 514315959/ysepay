<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 商户进件查询接口
 */
class MerchantRegisterQueryCommand extends Command
{
    public $api_url = 'https://register.ysepay.com:2443/register_gateway/gateway.do';

    private $param_keys = [
        'usercode',
        'merchant_no',
    ];

    public function build($params)
    {
        $data = $this->getParams($params, $this->param_keys);
        $this->setApiUrl($this->api_url)
            ->setBizContent($data);
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
        } else if ($data->$method->code != 10000) {
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
            'merchant_no' => $data->$method->merchant_no,
            'usercode' => $data->$method->usercode,
            'custname' => $data->$method->custname,
            'custid' => $data->$method->custid,
            'user_status' => $data->$method->user_status,
            'cust_status' => $data->$method->cust_status,
            'is_need_contract' => $data->$method->is_need_contract,
            'note' => $data->$method->note,
            'createtime' => $data->$method->createtime,
        ];
    }
}
