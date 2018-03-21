<?php

namespace Alipay;

use Alipay\Lib\AlipayCommon;

/**
 * 授权校验
 * Class AlipayOauth
 * @package Alipay
 */
class AlipayOauth extends AlipayCommon{

    protected $authGateway = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

    public $method = 'alipay.system.oauth.token';

    /**
     * 授权跳转
     * @param $redirectUri
     * @param $scope
     * @return string
     */
    public function getOauthRedirect($redirectUri,$scope){
        $query = array(
            'app_id'         => $this->getAppId(),
            'redirect_uri'  => $redirectUri,
            'scope'         => $scope
        );
        return $this->authGateway.'?'.http_build_query($query);
    }

    /**
     * 获取用于抓取用户信息token
     * @param $auth_code
     * @return \Generator
     */
    public function getToken($auth_code){
        $this->setOption('grant_type','authorization_code');
        $this->setOption('code',$auth_code);
        yield $this->getResult();
    }
}
