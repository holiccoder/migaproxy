<?php

namespace App\Services\Api\IPmart;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DataRequest
{
    private $request_url;

    private $api_key;

    private $api_secret;

    public function __construct()
    {
        $this->request_url = env('IPMART_API_URL');
        $this->api_key = env('IPMART_API_KEY');
        $this->api_secret = env('IPMART_API_SECRET');
    }

    public function sendRequest($route, $data, $method = 'POST')
    {
        $client = new \GuzzleHttp\Client;
        $payload = $data;
        try {
            $response = $client->request($method, $this->request_url.$route, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accessKeyId' => $this->api_key,
                    'accessKeySecret' => $this->api_secret,
                ],
                'body' => json_encode($payload),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] == 200) {
                return $data['data'];
            } else {
                Log::critical('IPmart request failed.', [
                    'route' => $route,
                    'payload' => $payload,
                    'response' => $data,
                ]);
            }

        } catch (GuzzleException $e) {
            Log::error('IPmart request exception.', [
                'route' => $route,
                'payload' => $payload,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * register user and get id, proxyName and proxy password
     */
    public static function registerUser($email, $password, $remark = ''): array|bool
    {
        $instance = new static;

        $data = [
            'email' => $email,
            'passwd' => $password,
            'remark' => $remark,
        ];
        $response = $instance->sendRequest('custom/add-user', $data, 'POST');

        if (! is_array($response) || ! isset($response['id'])) {
            return false;
        }

        $ipmartId = $response['id'];
        $getUserInfo = $instance->sendRequest('custom/getSubUserById', ['subUserId' => $ipmartId], 'POST');
        $ipmartUserInfo = is_array($getUserInfo) ? $getUserInfo : $response;

        if (
            ! isset($ipmartUserInfo['proxyName']) ||
            ! isset($ipmartUserInfo['proxyPwd']) ||
            ! isset($ipmartUserInfo['login_name']) ||
            ! isset($ipmartUserInfo['passwd'])
        ) {
            return false;
        }

        return [
            'ipmart_id' => (string) ($ipmartUserInfo['id'] ?? $ipmartId),
            'ipmart_email' => (string) ($ipmartUserInfo['email'] ?? $email),
            'plan_balance' => (string) ($ipmartUserInfo['plan_balance'] ?? 0),
            'proxyName' => (string) $ipmartUserInfo['proxyName'],
            'proxyPwd' => (string) $ipmartUserInfo['proxyPwd'],
            'login_name' => (string) $ipmartUserInfo['login_name'],
            'passwd' => (string) $ipmartUserInfo['passwd'],
        ];

    }

    /**
     * @param  $opr  --0-查询所有国家标识，1-配合code查询州，2-配合code查询城市
     * @param  $state
     *                发送示例：{
     *                "opr": "2",
     *                "code": "AE",
     *                "state": "Abudhabi"这里对应选择国家，州等
     *                }
     */
    public static function chooseArea($opr = 0, $code = null, $state = null)
    {
        $instance = new static;

        $params = [
            'opr' => $opr,
            'code' => $code,
            'state' => $state,
        ];

        $response = $instance->sendRequest('custom/areas', $params);

        return $response;
    }

    /**
     * 选择代理协议http, socks5
     */
    public function chooseProtocol()
    {
        $response = $this->sendRequest('custom/protocols', []);

        return $response;
    }

    public function proxyPattern()
    {
        $response = $this->sendRequest('custom/patterns', []);

        return $response;
    }

    public static function proxyRules()
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/rules', []);

        return $response;
    }

    public function getProxyRules()
    {
        return self::proxyRules();
    }

    public function getCountries()
    {
        return self::chooseArea(0);
    }

    /**
     * @param  $id
     *             returns traffic balance,proxy name and proxy password
     */
    public function getAvailableTraffic($id)
    {
        $response = $this->sendRequest('custom/proxy_info', [
            'id' => $id,
        ]);

        return $response;
    }

    /**
     * @param  $protocol  0 http 2 socks5
     * @param  $rule  0 rotating, 1 socks5 5-30 minutes,
     * @param  int  $count
     *                      returns test link and links
     */
    public static function generateProxyLinks($id, $protocol = 0, $pattern = 1, int $rule = 1, int $count = 0, $country = null, $state = null, $city = null)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/links', [
            'id' => $id,
            'cntry' => $country,
            'state' => $state,
            'city' => $city,
            'pattern' => $pattern,
            'protocol' => $protocol,
            'ipCount' => $count,
            'rule' => $rule,
        ]);

        return $response;
    }

    public static function changeProxyPassword($id, $password)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/resetSubUserProxyPwd', [
            'subUserId' => $id,
            'proxyPwd' => $password,
        ]);

        return $response;
    }

    public static function getStaticResidentialProductList()
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/static/products', []);

        return $response;
    }

    /**
     * @param  $code  购买地区，如US
     * @param  $num  购买IP数量
     * @param  $totalPrice  订单总价格
     * @return void
     */
    public static function buyStaticResidentialProduct($product_id, $code, $num, $totalPrice)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/static/pay', [
            'productId' => $product_id,
            'code' => $code,
            'num' => $num,
            'totalPrice' => $totalPrice,
        ]);

        return $response;
    }

    public static function getUserPurchasedIpList($user_id, $pageSize, $cntryCode, $staticProxyIp, $orderNo, $pageNum = 0)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/static/getIpsByPage', [
            'subUserId' => $user_id,
            'pageNum' => $pageNum,
            'pageSize' => $pageSize,
            'cntryCode' => $cntryCode,
            'staticProxyIp' => $staticProxyIp,
            'orderNo' => $orderNo,
        ]);

        return $response;
    }

    public static function getStaticIpCountByCode($code)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/static/getInventoryByCode', [
            'code' => $code,
        ]);

        return $response;
    }

    public static function changeStaticIpUsernamePassword($id, $username, $password, $staticIps)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/static/setIpAccounts', [
            'subUserId' => $id,
            'staticIps' => $staticIps,
            'userName' => $username,
            'passwd' => $password,
        ]);
    }

    public static function getRotatingApiList($id, $ApiCountryCode, $cntryCode, $num, $format, $time, $stateName, $cityName)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/api/getIps', [
            'apiCntryCode' => $ApiCountryCode,
            'subUserId' => $id,
            'cntryCode' => $cntryCode,
            'time' => $time,
            'num' => $num,
            'format' => $format,
            'stateName' => $stateName,
            'cityName' => $cityName,
        ]);

        return $response;
    }

    public static function payForCustomerUsingBalance($id, $capacity, $orderToken)
    {
        $instance = new static;
        $orderToken = Str::random(12);
        $response = $instance->sendRequest('custom/pay', [
            'id' => $id,
            'capacity' => $capacity,
            'orderToken' => $orderToken,
        ]);

        return $response;
    }

    public static function getTrafficUsageHistory($id, $start_date, $end_date)
    {
        $instance = new static;

        return $instance->sendRequest('custom/traffic', [
            'id' => $id,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public static function generateAPILink($apiCntryCode, $id, $cntryCode, $time, $num, $format, $stateName, $cityName)
    {
        $instance = new static;
        $response = $instance->sendRequest('custom/api/getIps', [
            'apiCntryCode' => $apiCntryCode,
            'subUserId' => $id,
            'cntryCode' => $cntryCode,
            'time' => $time,
            'num' => $num,
            'format' => $format,
            'stateName' => $stateName,
            'cityName' => $cityName,
        ]);

        return $response;
    }
}
