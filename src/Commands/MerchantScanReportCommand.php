<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 商户报备
 */
class MerchantScanReportCommand extends Command
{
    public $api_url = 'https://multiapi.ysepay.com:2443/busi-gate-api/mercScanReport';

    private $param_keys = [
        'mercId',
        'reportChannel',
        'mercProv',
        'mercCity',
        'mercArea',
        'contactsName',
        'contactsTel',
        'mchType',
        'orgNo',
        'appletAppid',
    ];

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
        } else if ($data->$method->respCode != '00') {
            return [
                'status' => false,
                'message' => $data->$method->respMsg,
                'code' => $data->$method->respCode,
            ];
        }

        return [
            'status' => true,
            'message' => $data->$method->respMsg,
            'respCode' => $data->$method->respCode,
            'busiType' => $data->$method->busiType,
            'channelId' => $data->$method->channelId,
            'thirdMercId' => $data->$method->thirdMercId,
            'apprSts' => $data->$method->apprSts,
            'remark' => $data->$method->remark ?? ''
        ];
    }
}
