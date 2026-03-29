<?php

use App\Services\Api\IPmart\DataRequest;

test('generateAPILink sends the expected route and payload', function () {
    $testRequest = new class extends DataRequest
    {
        public static ?array $capturedRequest = null;

        public function __construct() {}

        public function sendRequest($route, $data, $method = 'POST')
        {
            self::$capturedRequest = [
                'route' => $route,
                'data' => $data,
                'method' => $method,
            ];

            return [
                'link' => 'http://proxy.ipmart.io:8080',
                'ips' => ['proxy.ipmart.io:8080:user:pass'],
            ];
        }
    };

    $className = $testRequest::class;

    $result = $className::generateAPILink('CA', '68e77560d247fca264c188ab', 'US', 5, 1, 1, 'California', 'Pittsburg');

    expect($result)->toBe([
        'link' => 'http://proxy.ipmart.io:8080',
        'ips' => ['proxy.ipmart.io:8080:user:pass'],
    ]);

    expect($className::$capturedRequest)->toBe([
        'route' => 'custom/api/getIps',
        'data' => [
            'apiCntryCode' => 'CA',
            'subUserId' => '68e77560d247fca264c188ab',
            'cntryCode' => 'US',
            'time' => 5,
            'num' => 1,
            'format' => 1,
            'stateName' => 'California',
            'cityName' => 'Pittsburg',
        ],
        'method' => 'POST',
    ]);
});
