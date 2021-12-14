<?php

namespace Shion\YsePay\Commands;

use GuzzleHttp\Psr7\Response;

/**
 * 微信实名认证申请
 */
class AuthenticateWxAuthorizedQueryCommand extends Command
{
    public $api_url = 'https://openapi.ysepay.com/gateway.do';

    private $param_keys = [
        'usercode',
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

        return [
            'status' => true,
            'message' => $data->$method->msg,
            'thirdparty_mercid_x' => $data->$method->thirdparty_mercid_x ?? '',
            'thirdparty_authstate_x' => $data->$method->thirdparty_authstate_x ?? '', // AUTHORIZE_STATE_AUTHORIZED || AUTHORIZE_STATE_UNAUTHORIZED
            'note_x' => $data->$method->note_x ?? '',
        ];
    }
}
