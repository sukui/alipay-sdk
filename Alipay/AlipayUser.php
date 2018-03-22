<?php

namespace Alipay;

use Alipay\Lib\AlipayCommon;
use Alipay\Lib\Singleton;

/**
 * 用户信息
 * Class AlipayUser
 * @package Alipay
 */
class AlipayUser extends AlipayCommon{

    use Singleton;

    /**
     * 通过用户授权token获取用户信息
     * @param $access_token
     * @return \Generator
     */
    public function getUserInfoByToken($access_token ){
        $this->setOption('auth_token',$access_token);
        $this->setMethod("alipay.user.info.share");
        yield $this->getResult();
    }
}
