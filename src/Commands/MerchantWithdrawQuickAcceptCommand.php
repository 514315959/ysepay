<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 实时提现交易(一般户到银行卡)
 */
class MerchantWithdrawQuickAcceptCommand extends Command
{
    public $api_url = 'https://commonapi.ysepay.com/gateway.do';

    private $param_keys = [
        'out_trade_no',
        'merchant_usercode',
        'currency',
        'total_amount',
        'subject',
        'shopdate',
        'bank_account_no',
        'card_type',
    ];

    public function build($params)
    {
        $data = $this->getParams($params, $this->param_keys);

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
            'out_trade_no' => $data->$method->out_trade_no,
            'trade_status' => $data->$method->trade_status,
            'trade_status_description' => $data->$method->trade_status_description ?? '',
            'total_amount' => $data->$method->total_amount ?? 0,
            'account_date' => $data->$method->account_date ?? '',
            'trade_no' => $data->$method->trade_no ?? '',
            'fee' => $data->$method->fee ?? 0,
            'partner_fee' => $data->$method->partner_fee ?? 0,
            'payee_fee' => $data->$method->payee_fee ?? 0,
            'payer_fee' => $data->$method->payer_fee ?? 0,
        ];
    }
}
