<?php

namespace Kayue\Secoo;

use GuzzleHttp;
use GuzzleHttp\Psr7\Request;

class SecooClient
{
    const API_ENDPOINT = 'http://api.pop.secoo.com/rest';
    const TEST_API_ENDPOINT = 'http://testapi.pop.secoo.com:8888/rest';

    private $isTest = false;
    private $secretKey = '';
    private $vendorCode = null;

    /**
     * SecooClient constructor.
     *
     * @param string $vendorCode
     * @param string $secretKey
     * @param bool $isTest
     */
    public function __construct($vendorCode, $secretKey, $isTest = false)
    {
        $this->vendorCode = $vendorCode;
        $this->secretKey = $secretKey;
        $this->isTest = $isTest;
    }

    public function getEndpoint()
    {
        if ($this->isTest) {
            return self::TEST_API_ENDPOINT;
        }

        return self::API_ENDPOINT;
    }

    public function request($method, $params = [])
    {
        $basicParams = [
            'method' => $method,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            'v' => '1.0',
            'format' => 'json',
            'signMethod' => 'md5',
            'vendorCode' => $this->vendorCode
        ];

        $params = array_merge($params, $basicParams);
        $query = http_build_query($params + ['sign' => $this->getSign($params)]);

        $request = new Request('POST', $this->getEndpoint() . '?' . $query);
        $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        return (new GuzzleHttp\Client())->send($request);
    }

    private function getSign($params = [])
    {
        ksort($params);
        $pairs = [];

        foreach ($params as $key => $value) {
            $pairs[] = $key . $value;
        }

        $sign = $this->secretKey . join('', $pairs) . $this->secretKey;

        return strtoupper(md5($sign));
    }
}