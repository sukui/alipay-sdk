<?php

namespace Alipay;

use Alipay\Lib\AlipayCommon;
use Alipay\Lib\Singleton;

/**
 * 线下交易(面对面交易)
 * Class AlipayTrade
 * @package Alipay
 */
class AlipayTrade extends AlipayCommon{

    use Singleton;

    /**
     * 统一收单
     * @param string $open_id
     * @param string $subject
     * @param string $out_trade_no
     * @param float $total_amount
     * @param string $notify_url
     * @param int $discountable_amount
     * @return \Generator
     */
    public function create($open_id, $subject, $out_trade_no, $total_amount, $notify_url,$discountable_amount=0){
        $biz = [
            'out_trade_no' => $out_trade_no,
            'subject' => $subject,
            'total_amount' => $total_amount,
            'buyer_id' => $open_id,
        ];
        $this->setOption('notify_url',$notify_url);
        if(intval($discountable_amount) > 0){
            $biz['discountable_amount'] = intval($discountable_amount);
        }
        $this->setMethod('alipay.trade.create');
        yield $this->getResult($biz);
    }

    /**
     * 统一收单线下交易预创建，生成二维码供用户支付
     * @param $out_trade_no
     * @param $subject
     * @param $total_amount
     * @param $notify_url
     * @param int $discountable_amount
     * @return \Generator
     */
    public function preCreate($out_trade_no,$subject,$total_amount,$notify_url,$discountable_amount=0){
        $biz = [
            'out_trade_no' => $out_trade_no,
            'subject' => $subject,
            'total_amount' => $total_amount,
            'product_code'  => 'FACE_TO_FACE_PAYMENT'
        ];
        $this->setOption('notify_url',$notify_url);
        if(intval($discountable_amount) > 0){
            $biz['discountable_amount'] = intval($discountable_amount);
        }
        $this->setMethod('alipay.trade.precreate');
        yield $this->getResult($biz);
    }

    /**
     * 条形码、二维码、声波收款
     * @param string $out_trade_no
     * @param string $subject
     * @param float $total_amount
     * @param int $auth_code
     * @param string $scene bar_code：条码 wave_code：声波
     * @param int $discountable_amount
     * @return \Generator
     */
    public function qrPay($out_trade_no,$subject,$total_amount,$auth_code,$scene='bar_code',$discountable_amount=0){
        $biz = [
            'out_trade_no' => $out_trade_no,
            'subject' => $subject,
            'total_amount' => $total_amount,
            'auth_code' => $auth_code,
            'scene'     => $scene,
        ];
        if(intval($discountable_amount > 0)){
            $biz['discountable_amount'] = intval($discountable_amount);
        }
        $this->setMethod('alipay.trade.pay');
        yield $this->getResult($biz);
    }

    /**
     * 交易查询
     * @param $out_trade_no
     * @return \Generator
     */
    public function query($out_trade_no){
        $biz = [
            'out_trade_no' => $out_trade_no,
        ];
        $this->setMethod('alipay.trade.query');
        yield $this->getResult($biz);
    }

    /**
     * 取消交易
     * @param $out_trade_no
     * @return \Generator
     */
    public function cancel($out_trade_no){
        $biz = [
            'out_trade_no' => $out_trade_no,
        ];
        $this->setMethod('alipay.trade.cancel');
        yield $this->getResult($biz);
    }


    /**
     * 取消交易
     * @param $out_trade_no
     * @param string $notify_url
     * @return \Generator
     */
    public function close($out_trade_no,$notify_url=null){
        $biz = [
            'out_trade_no' => $out_trade_no,
        ];
        if($notify_url == null){
            $this->setOption('notify_url',$notify_url);
        }
        $this->setMethod('alipay.trade.close');
        yield $this->getResult($biz);
    }

    /**
     * 退款
     * @param $out_trade_no
     * @param $refund_id
     * @param $refund_amount
     * @param null $refund_reason
     * @return \Generator
     */
    public function refund($out_trade_no,$refund_id,$refund_amount,$refund_reason=null){
        $biz = [
            'out_trade_no' => $out_trade_no,
            'refund_amount' => $refund_amount,
            'out_request_no' => $refund_id
        ];
        if($refund_reason != null){
            $biz['refund_reason'] = $refund_reason;
        }
        $this->setMethod('alipay.trade.refund');
        yield $this->getResult($biz);
    }

    /**
     * 企业付款
     * @param $account_id
     * @param int $amount 金额
     * @param string $out_trade_no 商户订单号
     * @param $user_name
     * @param string $remark 备注信息
     * @return array|bool
     * @link https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer
     */
    public function transfers($account_id, $amount, $out_trade_no, $user_name, $remark=null)
    {
        $biz = [
            'out_biz_no' => $out_trade_no,
            'payee_account' => $account_id,
            'amount' => $amount,
            'payee_real_name' => $user_name,
            'remark' => $remark
        ];

        if(is_numeric($account_id)){
            $biz['payee_type'] = 'ALIPAY_USERID';
        }else{
            $biz['payee_type'] = 'ALIPAY_LOGONID';
        }

        if($remark != null){
            $biz['remark'] = $remark;
        }
        $this->setMethod('alipay.fund.trans.toaccount.transfer');
        yield $this->getResult($biz);
    }
}
