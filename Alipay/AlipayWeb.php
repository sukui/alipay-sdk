<?php

namespace Alipay;

use Alipay\Lib\AlipayCommon;

/**
 * web付款
 * Class AlipayUser
 * @package Alipay
 */
class AlipayWeb extends AlipayCommon{

    /**
     * APP收款下单
     * @param $open_id
     * @param $subject
     * @param $out_trade_no
     * @param $total_amount
     * @param $notify_url
     * @param $return_url
     * @return string
     */
    public function create($open_id, $subject, $out_trade_no, $total_amount, $notify_url,$return_url){
        $biz = [
            'out_trade_no' => $out_trade_no,
            'subject' => $subject,
            'total_amount' => $total_amount,
            'buyer_id' => $open_id,
            'product_code'  => 'FAST_INSTANT_TRADE_PAY',
        ];
        $this->setOption('notify_url',$notify_url);
        $this->setOption('return_url',$return_url);
        $this->setMethod('alipay.trade.page.pay');
        $options = $this->getOption($biz);
        $options['sign'] = $this->getSign($options);
        return $this->gateway."?".http_build_query($options);
    }
}
