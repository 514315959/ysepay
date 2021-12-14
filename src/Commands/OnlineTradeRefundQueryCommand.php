<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 交易退款查询接口
 */
class OnlineTradeRefundQueryCommand extends Command
{
    public $api_url = 'https://openapi.ysepay.com/gateway.do';

    private $param_keys = [
        'trade_no',
        'out_trade_no',
        'out_request_no',
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
            'trade_no' => $data->$method->trade_no,
            'out_trade_no' => $data->$method->out_trade_no,
            'out_request_no' => $data->$method->out_request_no,
            'refund_state' => $data->$method->refund_state,
            'funds_state' => $data->$method->funds_state,
            'refund_reason' => $data->$method->refund_reason,
            'total_amount' => $data->$method->total_amount,
            'refund_amount' => $data->$method->refund_amount,
            'account_date' => $data->$method->account_date,
            'markting_refund_detail' => $data->$method->markting_refund_detail,
            'refund_channelfunds_dynamics' => $data->$method->refund_channelfunds_dynamics,
            'real_discount_fee_rate' => $data->$method->real_discount_fee_rate,
        ];
    }
}
