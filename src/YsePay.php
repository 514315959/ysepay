<?php

namespace Shion\YsePay;

use Shion\YsePay\Utils\Helper;
use Shion\YsePay\Exception\YsePayException;

class YsePay
{
    private $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 获取商户进件Token，用户上传商户进件图片及提交进件
     */
    public function getMerchantRegisterToken()
    {
        return $this->exec('ysepay.merchant.register.token.get', []);
    }

    /**
     * 上传商户进件所需的图片文件
     */
    public function uploadMerchantRegisterImage($params)
    {
        return $this->exec('ysepay.merchant.register.image.upload', $params);
    }

    /**
     * 商户进件资料提交
     */
    public function merchantRegisterAccept($params)
    {
        return $this->exec('ysepay.merchant.register.accept', $params);
    }

    /**
     * 商户进件
     */
    public function merchantRegister($params)
    {
        // 获取token
        $res = $this->getMerchantRegisterToken();
        if ($res['status'] == false) {
            return $res;
        }

        $params['token'] = $res['token'];

        // 上传图片
        foreach ($params['images'] as $k => $v) {
            $upload_params = [
                'picType' => $k,
                'picFile' => $v,
                'token' => $res['token'],
                'superUsercode' => $this->config['partner_id']
            ];
            $res = $this->uploadMerchantRegisterImage($upload_params);
            if ($res['status'] == false) {
                return $res;
            }
        }

        // 资料提交
        return $this->merchantRegisterAccept($params);
    }

    /**
     * 商户进件状态查询
     */
    public function queryMerchantRegisterStatus($params)
    {
        return $this->exec('ysepay.merchant.register.query', $params);
    }

    /**
     * 微信支付(公众号、小程序)
     */
    public function wechatPay($params)
    {
        return $this->exec('ysepay.online.weixin.pay', $params);
    }

    /**
     * 云闪付APP(手机控件支付)
     */
    public function ysfPay($params)
    {
        return $this->exec('ysepay.online.mobile.controls.pay', $params);
    }

    /**
     * 线上分账
     */
    public function divisionAccept($params)
    {
        return $this->exec('ysepay.single.division.online.accept', $params);
    }

    /**
     * 单笔订单查询
     */
    public function queryTrade($params)
    {
        return $this->exec('ysepay.online.trade.query', $params);
    }

    /**
     * 单笔订单明细查询
     */
    public function queryTradeDetail($params)
    {
        return $this->exec('ysepay.online.trade.order.query', $params);
    }

    /**
     * 交易退款
     */
    public function refund($params)
    {
        return $this->exec('ysepay.online.trade.refund', $params);
    }

    /**
     * 分账退款
     */
    public function divisionRefund($params)
    {
        return $this->exec('ysepay.online.trade.refund.split', $params);
    }

    /**
     * 交易退款查询
     */
    public function queryRefund($params)
    {
        return $this->exec('ysepay.online.trade.refund.query', $params);
    }

    /**
     * 关闭订单
     */
    public function closeTrade($params)
    {
        return $this->exec('ysepay.online.trade.close', $params);
    }

    /**
     * 获取对账单下载地址
     */
    public function getBillDownloadUrl($params)
    {
        return $this->exec('ysepay.online.bill.downloadurl.get', $params);
    }

    /**
     * 一般户到银行卡
     */
    public function withdrawQuick($params)
    {
        return $this->exec('ysepay.merchant.withdraw.quick.accept', $params);
    }

    /**
     * 一般户到银行卡
     */
    public function withdrawD0($params)
    {
        return $this->exec('ysepay.merchant.withdraw.d0.accept', $params);
    }

    /**
     * 商户余额查询
     */
    public function balanceQuery($params)
    {
        return $this->exec('ysepay.merchant.balance.query', $params);
    }

    /**
     * 商户报备
     */
    public function scanReport($params)
    {
        return $this->exec('ysepay.merchant.scan.report', $params);
    }

    /**
     * 微信实名认证申请
     */
    public function wxApply($params)
    {
        // 获取token
        $res = $this->getMerchantRegisterToken();
        if ($res['status'] == false) {
            return $res;
        }

        $params['token'] = $res['token'];

        // 上传图片
        foreach ($params['images'] as $k => $v) {
            $upload_params = [
                'picType' => $k,
                'picFile' => $v,
                'token' => $res['token'],
                'superUsercode' => $this->config['partner_id']
            ];
            $res = $this->uploadMerchantRegisterImage($upload_params);
            if ($res['status'] == false) {
                return $res;
            }
        }

        return $this->exec('ysepay.authenticate.wx.apply', $params);
    }

    /**
     * 微信实名认证状态查询
     */
    public function wxApplyQuery($params)
    {
        return $this->exec('ysepay.authenticate.wx.query', $params);
    }

    /**
     * 微信实名认证状态查询
     */
    public function wxApplyCancel($params)
    {
        return $this->exec('ysepay.authenticate.wx.apply.cancel', $params);
    }

    /**
     * 微信实名认证授权状态查询
     */
    public function wxAuthorizedApplyQuery($params)
    {
        return $this->exec('ysepay.authenticate.wx.authorized.query', $params);
    }

    /**
     * 执行指定的接口
     */
    private function exec($method, $params)
    {
        $method_class = Helper::GetMethodClass($method);

        return $method_class->setPartnerId($this->config['partner_id'])
            ->setPrivateKey($this->config['private_key'])
            ->setPrivateKeyPasswd($this->config['private_key_passwd'])
            ->exec($params);
    }
}
