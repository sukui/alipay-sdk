<?php

namespace Alipay\Lib;

use CURLFile;
use ZanPHP\HttpClient\HttpClient;

class Tools
{

    static public function httpGet($url,$timeout=5000)
    {
        $httpClient = new HttpClient();
        $response = yield $httpClient->getByURL($url,[],$timeout);
        yield (intval($response->getStatusCode()) === 200) ? $response->getBody() : false;
    }


    static public function httpPost($url, $data, $timeout=5000)
    {

        $httpClient = new HttpClient();
        $response = yield $httpClient->postByURL($url,self::_buildPost($data),$timeout);
        yield (intval($response->getStatusCode()) === 200) ? $response->getBody() : false;
    }


    static public function httpsPost($url, $data, $ssl_cert = null, $ssl_key = null, $timeout = 30000)
    {
        $httpClient = new HttpClient();
        $options = [];
        if (!is_null($ssl_cert) && file_exists($ssl_cert) && is_file($ssl_cert)) {
            $options['ssl_cert_file']    = $ssl_cert;
        }
        if (!is_null($ssl_key) && file_exists($ssl_key) && is_file($ssl_key)) {
            $options['ssl_key_file'] = $ssl_key;
        }
        $httpClient->set($options);
        $response = yield $httpClient->postByURL($url,self::_buildPost($data),$timeout);
        yield (intval($response->getStatusCode()) === 200) ? $response->getBody() : false;
    }

    static private function _buildPost(&$data)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_string($value) && $value[0] === '@' && class_exists('CURLFile', false)) {
                    $filename = realpath(trim($value, '@'));
                    file_exists($filename) && $value = new CURLFile($filename);
                }
            }
        }
        return $data;
    }

    static public function getAddress()
    {
        $ip = yield getClientIp();
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            yield $ip;
        } else {
            yield '0.0.0.0';
        }
    }

    /**
     * 从证书文件获取公共key
     * @param $certPath
     * @return mixed|string
     */
    static public function getPublicKey($certPath){
        $cert = file_get_contents($certPath);
        $key = openssl_pkey_get_public($cert);
        $keyData = openssl_pkey_get_details($key);
        return $keyData['key'];
    }

    /**
     * 获取证书序列号
     * @param $certPath
     * @return string
     */
    static public function getCertSn($certPath){
        $sign = file_get_contents($certPath);
        $ssl = openssl_x509_parse($sign);
        return md5(self::_arr2str(array_reverse($ssl['issuer'])) . $ssl['serialNumber']);
    }

    /**
     * 获取根证书序列号
     * @param $certPath
     * @return bool|null|string
     */
    static  public function getRootCertSN($certPath)
    {
        $sn = null;
        if (!file_exists($certPath)){
            return false;
        }
        $sign = file_get_contents($certPath);
        $array = explode("-----END CERTIFICATE-----", $sign);
        for ($i = 0; $i < count($array) - 1; $i++) {
            $ssl[$i] = openssl_x509_parse($array[$i] . "-----END CERTIFICATE-----");
            if (strpos($ssl[$i]['serialNumber'], '0x') === 0) {
                $ssl[$i]['serialNumber'] = self::_hex2dec($ssl[$i]['serialNumber']);
            }
            if ($ssl[$i]['signatureTypeLN'] == "sha1WithRSAEncryption" || $ssl[$i]['signatureTypeLN'] == "sha256WithRSAEncryption") {
                if ($sn == null) {
                    $sn = md5(self::_arr2str(array_reverse($ssl[$i]['issuer'])) . $ssl[$i]['serialNumber']);
                } else {
                    $sn = $sn . "_" . md5(self::_arr2str(array_reverse($ssl[$i]['issuer'])) . $ssl[$i]['serialNumber']);
                }
            }
        }
        return $sn;
    }

    /**
     * 新版 数组转字符串
     * @param array $array
     * @return string
     */
    static private function _arr2str($array)
    {
        $string = [];
        if ($array && is_array($array)) {
            foreach ($array as $key => $value) {
                $string[] = $key . '=' . $value;
            }
        }
        return implode(',', $string);
    }


    /**
     * 新版 0x转高精度数字
     * @param string $hex
     * @return int|string
     */
    static  private function _hex2dec($hex)
    {
        list($dec, $len) = [0, strlen($hex)];
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }

    /**
     * 去除证书前后内容及空白
     * @param string $sign
     * @return string
     */
    static  public function trimCert($sign)
    {
        return preg_replace(['/\s+/', '/\-{5}.*?\-{5}/'], '', $sign);
    }

}