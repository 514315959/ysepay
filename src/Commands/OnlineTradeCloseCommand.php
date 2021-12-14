<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 关闭订单接口
 */
class OnlineTradeCloseCommand extends Command
{
    public $api_url = 'https://openapi.ysepay.com/gateway.do';

    private $param_keys = [
        'out_trade_no',
        'shopdate',
        'trade_no',
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
            'out_trade_no' => $data->$method->out_trade_no,
            'trade_no' => $data->$method->trade_no,
        ];
    }
}
