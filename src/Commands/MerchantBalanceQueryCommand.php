<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 实时提现交易(一般户到银行卡)
 */
class MerchantBalanceQueryCommand extends Command
{
    public $api_url = 'https://commonapi.ysepay.com/gateway.do';

    private $param_keys = [
        'merchant_usercode',
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

        // account_detail => [
        //     'account_id', 账户号
        //     'account_type', 账户类型
        //     'account_amount', 账户余额
        //     'account_withdrawbalance', 预可提现余额(只针对一般消费类账户与待结算账户)
        // ]
        return [
            'status' => true,
            'message' => $data->$method->msg,
            'account_total_amount' => $data->$method->account_total_amount,
            'account_settled_unpaid_amount' => $data->$method->account_settled_unpaid_amount,
            'account_detail' => $data->$method->account_detail
        ];
    }
}
