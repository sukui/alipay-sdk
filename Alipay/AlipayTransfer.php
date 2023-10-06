<?php

namespace Alipay;

use Alipay\Lib\AlipayCommon;
use Alipay\Lib\Singleton;

/**
 * 转账
 * Class AlipayTransfer
 * @package Alipay
 */
class AlipayTransfer extends AlipayCommon{
    use Singleton;
    /**
     * 新版企业付款
     * @param $account_id
     * @param int $amount 金额
     * @param string $out_trade_no 商户订单号
     * @param $user_name
     * @param string $remark 备注信息
     * @param null $order_title
     * @return array|bool
     * @link https://opendocs.alipay.com/apis/api_28/alipay.fund.trans.uni.transfer
     */
    public function transfers($account_id, $amount, $out_trade_no, $user_name, $remark=null,$order_title=null)
    {
        $payee_info = [
            'identity' => $account_id,
            'identity_type' => 'ALIPAY_LOGON_ID',
        ];
        if(!empty($user_name)){
            $payee_info['name'] = $user_name;
            $payee_info['identity_type'] = 'ALIPAY_LOGON_ID';
        }else{
            $payee_info['identity_type'] = 'ALIPAY_USER_ID';
        }
        $biz = [
            'out_biz_no' => $out_trade_no,
            'trans_amount' => $amount,
            'remark' => $remark,
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'payee_info' => $payee_info,
            'biz_scene' =>  'DIRECT_TRANSFER'
        ];

        if(!empty($order_title)){
            $biz['order_title'] = $order_title;
        }
        if($remark != null){
            $biz['remark'] = $remark;
        }
        $this->setMethod('alipay.fund.trans.uni.transfer');
        yield $this->getResult($biz);
    }

    /**
     * 查询转账状态
     * @param $order_id
     * @return \Generator
     */
    public function queryResult($order_id)
    {
        $biz = [
            'out_biz_no' => $order_id
        ];
        $this->setMethod('alipay.fund.trans.common.query');
        yield $this->getResult($biz);

    }

    /**
     * 查询账户金额
     * @param $account_id
     * @return \Generator
     */
    public function getAccountMoney($account_id){
        $biz = [
            'alipay_user_id' => $account_id,
            'account_type'  => 'ACCTRANS_ACCOUNT'
        ];
        $this->setMethod('alipay.fund.account.query');
        yield $this->getResult($biz);
    }
}

