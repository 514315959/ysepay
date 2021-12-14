<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 云闪付app 手机控件支付
 */
class OnlineMobileControlsPayCommand extends Command
{
    public $api_url = 'https://openapi.ysepay.com/gateway.do';

    private $param_keys = [
        'out_trade_no',
        'shopdate',
        'subject',
        'total_amount',
        'currency',
        'seller_id',
        'seller_name',
        'timeout_express',
        'extend_params',
        'extra_common_param',
        'business_code',
        'bank_type',
        'bank_account_type',
        'support_card_type',
        'sub_merchant',
        'consignee_info',
        'cross_border_info',
        'unionPayGoodsDetails',
    ];

    public function build($params)
    {
        $data = $this->getParams($params, $this->param_keys);

        if (!empty($params['tran_type'])) {
            $this->setTranType($params['tran_type']);
        }

        $this->setVersion('3.4')
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
            'out_trade_no' => $data->$method->out_trade_no,
            'trade_no' => $data->$method->trade_no,
            'trade_status' => $data->$method->trade_status,
            'total_amount' => $data->$method->total_amount,
            'account_date' => $data->$method->account_date ?? '',
            'tn' => $data->$method->tn,
        ];
    }
}
