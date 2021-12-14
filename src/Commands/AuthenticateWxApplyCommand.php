<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;
use Shion\YsePay\Utils\Signature;

/**
 * 微信实名认证申请
 */
class AuthenticateWxApplyCommand extends Command
{
    public $api_url = 'https://openapi.ysepay.com/gateway.do';

    private $param_keys = [
        'usercode',
        'cust_name',
        'contact_cert_type',
        'contact_cert_no',
        'legal_cert_initial',
        'legal_cert_expire',
        'bus_license_initial',
        'bus_license_expire',
        'store_type',
        'store_name',
        'token',
    ];

    public function build($params)
    {
        $data = $this->getParams($params, $this->param_keys);
        $data['contact_cert_no'] = Signature::DesEncrypt($data['contact_cert_no'], str_pad(substr($this->partner_id, 0, 8), 8, ' ', STR_PAD_LEFT));

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
            'apply_no' => $data->$method->apply_no,
            'send_channel_apply_no' => $data->$method->send_channel_apply_no,
        ];
    }
}
