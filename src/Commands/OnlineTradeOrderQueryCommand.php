<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 订单及明细查询接口
 */
class OnlineTradeOrderQueryCommand extends Command
{
    public $api_url = 'https://search.ysepay.com/gateway.do';

    private $param_keys = [
        'out_trade_no',
        'shopdate',
        'trade_no',
    ];

    public function build($params)
    {
        $this->setApiUrl($this->api_url)->setBizContent($this->getParams($params, $this->param_keys));
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
            'trade_status' => $data->$method->trade_status,
            'out_trade_no' => $data->$method->out_trade_no,
            'trade_no' => $data->$method->trade_no,
            'total_amount' => $data->$method->total_amount,
            'receipt_amount' => $data->$method->receipt_amount,
            'account_date' => $data->$method->account_date,
            'result_note' => $data->$method->result_note,
            'pay_detail_list' => $data->$method->pay_detail_list,
            'quick_pay_detail_list' => $data->$method->quick_pay_detail_list ?? null,
            'settlement_amount' => $data->$method->settlement_amount ?? null,
            'is_discount' => $data->$method->is_discount ?? null,
            'total_discount_fee' => $data->$method->total_discount_fee ?? null,
            'total_discount' => $data->$method->total_discount ?? null,
            'wxpay_coupon_info' => $data->$method->wxpay_coupon_info ?? null,
            'wxpay_coupon_good_detail' => $data->$method->wxpay_coupon_good_detail ?? null,
            'trade_status_ext' => $data->$method->trade_status_ext ?? null,
            'alipay_coupon_info' => $data->$method->alipay_coupon_info ?? null,
            'alipay_coupon_good_detail' => $data->$method->alipay_coupon_good_detail ?? null,
            'coupon_info' => $data->$method->coupon_info ?? null,
            'installment_resp' => $data->$method->installment_resp ?? null,
            'mer_discount_fee' => $data->$method->mer_discount_fee ?? null,
            'has_refund_payee_fee' => $data->$method->has_refund_payee_fee ?? null,
            'has_refund_payer_fee' => $data->$method->has_refund_payer_fee ?? null,
            'has_refund_src_fee' => $data->$method->has_refund_src_fee ?? null,
            'refund_amount' => $data->$method->refund_amount ?? null,
        ];
    }
}
