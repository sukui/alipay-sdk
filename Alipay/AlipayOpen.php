<?php

namespace Alipay;
use Alipay\Lib\AlipayCommon;

/**
 * 阿里开放平台(生活号)
 * Class AlipayOpen
 * @package Alipay
 */
class AlipayOpen extends AlipayCommon{

    /**
     * 是否关注生活号
     * @param $user_id
     * @return \Generator
     */
    public function isFollow($user_id){
        $biz = [
            'user_id' => $user_id,
        ];
        $this->setMethod('alipay.open.public.user.follow.query');
        yield $this->getResult($biz);
    }

    /**
     * 发送模板消息
     * @param $open_id
     * @param $template_id
     * @param $context
     * @return \Generator
     */
    public function sendMessage($open_id,$template_id,$context){
        $biz = [
            'to_user_id' => $open_id,
            'template' => [
                'template_id'   => $template_id,
                'context'       => $context
            ],
        ];
        $this->setMethod('alipay.open.public.user.follow.query');
        yield $this->getResult($biz);
    }
}
