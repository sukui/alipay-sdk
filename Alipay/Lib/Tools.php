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

}
