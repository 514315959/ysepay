<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 线上分账
 */
class SingleDivisionOnlineAcceptCommand extends Command
{
    public $api_url = 'https://commonapi.ysepay.com/gateway.do';

    private $param_keys = [
        'out_batch_no',
        'out_trade_no',
        'payee_usercode',
        'total_amount',
        'sys_flag',
        'is_divistion',
        'is_again_division',
        'division_mode',
        'div_list',
    ];

    public function build($params)
    {
        $data = $this->getParams($params, $this->param_keys);

        $this->setVersion('3.0')
            ->setApiUrl($this->api_url)
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
            'returnCode' => $data->$method->returnCode,
            'retrunInfo' => $data->$method->retrunInfo,
            'msg' => $data->$method->msg,
        ];
    }
}
