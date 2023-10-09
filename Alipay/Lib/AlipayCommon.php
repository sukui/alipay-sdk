<?php

namespace Alipay\Lib;

class AlipayCommon
{

    protected $gateway = "https://openapi.alipay.com/gateway.do?charset=utf-8";
    protected $option;
    protected $config;
    public $errCode;
    public $errMsg;

    public $method;

    public function __construct($config)
    {
        if(empty($config['app_id'])){
            throw new \InvalidArgumentException("缺少app_id配置");
        }
        if (is_null($config['private_key'])) {
            throw new \InvalidArgumentException('缺少私钥');
        }
        if (empty($config['alipay_public_cert']) || !file_exists($config['alipay_public_cert'])) {
            throw new \InvalidArgumentException('缺少支付宝公钥');
        }
        if (empty($config['app_public_cert']) || !file_exists($config['app_public_cert'])) {
            throw new \InvalidArgumentException('缺少应用公钥');
        }
        if (empty($config['alipay_root_cert']) || !file_exists($config['alipay_root_cert'])) {
            throw new \InvalidArgumentException('缺少支付宝根证书');
        }
        if(!empty($config['debug'])){
            $this->gateway = "https://openapi.alipaydev.com/gateway.do?charset=utf-8";
        }

        $config['public_key'] = trim(Tools::getPublicKey($config['alipay_public_cert']));
        $config['app_cert_sn'] = Tools::getCertSn($config['app_public_cert']);
        $config['alipay_root_cert_sn'] = Tools::getRootCertSN($config['alipay_root_cert']);

        $content = wordwrap(Tools::trimCert($config['private_key']), 64, "\n", true);
        $config['private_key'] = "-----BEGIN RSA PRIVATE KEY-----\n{$content}\n-----END RSA PRIVATE KEY-----";

        $this->config = $config;
        $this->option = [
            'app_id' => $config['app_id'],
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
        ];
    }

    /**
     * 调用服务
     * @param $method
     */
    public function setMethod($method){
        $this->method = $method;
    }

    /**
     * 设置参数
     * @param $key
     * @param $value
     */
    public function setOption($key,$value){
        if(!empty($key)&&!empty($value)){
            $this->option[$key] = $value;
        }
    }

    /**
     * 获取并组装参数
     * @param array $biz_content
     * @return array
     * @throws \Exception
     */
    public function getOption(array $biz_content=array()){
        if(empty($this->method)){
            throw new \Exception("缺少指定服务method");
        }

        if(!empty($biz_content)){
            $this->setOption('biz_content',json_encode($biz_content,JSON_UNESCAPED_UNICODE));
        }

        $this->setOption('method',$this->method);
        $this->setOption("timestamp", date('Y-m-d H:i:s'));
        $this->setCertOption();
        return $this->option;
    }

    /**
     * 获取需求签名内容
     * @param array $data
     * @param bool $verify
     * @return bool|string
     */
    protected function getSignContent(array $data, $verify = false)
    {
        ksort($data);
        $stringToBeSigned = '';
        foreach ($data as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
        }
        return substr($stringToBeSigned, 0, -1);
    }

    /**
     * 校验
     * @param $data
     * @param null $sign
     * @param bool $sync
     * @return bool
     */
    public function verify($data, $sign = null, $sync = false)
    {
        $sign = is_null($sign) ? $data['sign'] : $sign;
        $toVerify = $sync ? json_encode($data) : $this->getSignContent($data, true);
        $result =  openssl_verify($toVerify, base64_decode($sign), $this->config['public_key'], OPENSSL_ALGO_SHA256);
        if($result === 1){
            return $data;
        }else{
            $this->errMsg = "verify error from {$this->method}, data:".json_encode($data);
            $this->errCode = 20000;
            return false;
        }
    }

    /**
     * 设置证书选项
     */
    public function setCertOption(){
        $this->setOption("app_cert_sn",$this->config['app_cert_sn']);
        $this->setOption("alipay_root_cert_sn",$this->config['alipay_root_cert_sn']);
    }

    /**
     * 签名结果
     * @param $data
     * @return string
     */
    protected function getSign($data)
    {
        $content = $this->getSignContent($data);
        openssl_sign($content, $sign, $this->config['private_key'], OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * 请求并返回结果
     * @param array $biz
     * @return \Generator
     */
    protected function getResult(array $biz=array())
    {
        $options = $this->getOption($biz);
        $options['sign'] = $this->getSign($options);
        $method = str_replace('.', '_', $this->method) . '_response';
        $response = yield Tools::httpPost($this->gateway,$options);
        $data = json_decode($response, true);
        if(isset($data['error_response'])){
            $method = 'error_response';
        }
        if (isset($data[$method]['code']) && $data[$method]['code'] !== '10000') {
            $this->errMsg = (empty($data[$method]['code']) ? '' : "{$data[$method]['msg']}[{$data[$method]['code']}]") . (empty($data[$method]['sub_code']) ? '' : "-{$data[$method]['sub_msg']}[{$data[$method]['sub_code']}]");
            $this->errCode = $data[$method]['code'];
            yield false;
        }else{
            yield $this->verify($data[$method], $data['sign'], true);
        }
    }

    /**
     * 获取错误码
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errCode;
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->errMsg;
    }

    /**
     * 获取appid
     * @return mixed
     */
    public function getAppId()
    {
        return $this->config['app_id'];
    }

    /**
     * 获取配置
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

}