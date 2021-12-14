<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;
use Shion\YsePay\Utils\Signature;

/**
 * 商户进件注册接口
 */
class MerchantRegisterAcceptCommand extends Command
{
    public $api_url = 'https://register.ysepay.com:2443/register_gateway/gateway.do';

    private $param_keys = [
        'merchant_no',
        'remark',
        'cust_type',
        'another_name',
        'cust_name',
        'mer_flag',
        'industry',
        'province',
        'city',
        'company_addr',
        'legal_name',
        'legal_tel',
        'mail',
        'contact_man',
        'contact_phone',
        'legal_cert_type',
        'legal_cert_no',
        'legal_cert_expire',
        'bus_license',
        'bus_license_expire',
        'notify_type',
        'settle_type',
        'bank_account_no',
        'bank_account_name',
        'bank_account_type',
        'bank_card_type',
        'bank_name',
        'bank_type',
        'bank_province',
        'bank_city',
        'cert_type',
        'cert_no',
        'bank_telephone_no',
        'service_tel',
        'org_no',
        'sub_account_flag',
        'token'
    ];

    public function build($params)
    {
        $data = $this->getParams($params, $this->param_keys);

        $data['legal_cert_no'] = Signature::DesEncrypt($data['legal_cert_no'], str_pad(substr($this->partner_id, 0, 8), 8, ' ', STR_PAD_LEFT));
        $data['cert_no'] = Signature::DesEncrypt($data['cert_no'], str_pad(substr($this->partner_id, 0, 8), 8, ' ', STR_PAD_LEFT));

        $this->setApiUrl($this->api_url)
            ->setBizContent($data)
            ->setNotifyUrl($params['notify_url']);
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
            'usercode' => $data->$method->usercode,
            'custname' => $data->$method->custname,
            'custid' => $data->$method->custid,
            'user_status' => $data->$method->user_status,
            'createtime' => $data->$method->createtime,
        ];
    }
}
