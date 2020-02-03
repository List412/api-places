<?php


namespace App\Tests\Service;


use App\Service\LocationService;
use PHPUnit\Framework\TestCase;

class LocationServiceTest extends TestCase
{
    public function testDistance()
    {
        $locationService = new LocationService();

        $arr = [
            ['lat1' => 1, 'lng1' => 1, 'lat2' => 2, 'lng2' => 2, 'result' => 157225.43],
            ['lat1' => 1, 'lng1' => 1, 'lat2' => 1, 'lng2' => 1, 'result' => 0],
            ['lat1' => -10, 'lng1' => -23, 'lat2' => 21, 'lng2' => 56, 'result' => 9284801.32],
            ['lat1' => 0.0001, 'lng1' => 0, 'lat2' => 0.0002, 'lng2' => 0, 'result' => 11.12],
            ['lat1' => 85, 'lng1' => 180, 'lat2' => -85, 'lng2' => -180, 'result' => 18903137.53]
        ];

        foreach ($arr as $test) {
            $result = $locationService->distance($test['lat1'], $test['lng1'], $test['lat2'], $test['lng2']);
            $this->assertEqualsWithDelta($test['result'], $result, $result * 0.0001);
        }
    }

    public function testGetCoordinatesByIp()
    {
        $locationService = new LocationService();
        $tests = [
            ['ip' => '127.0.0.1', 'result' => 'message'],
            ['ip' => '194.190.5.114', 'result' => 'city'],
            ['ip' => '31.173.120.64', 'result' => 'city'],
        ];

        foreach ($tests as $test) {
            $result = $locationService->getCoordinatesByIp($test['ip']);
            $this->assertArrayHasKey($test['result'], $result);
        }
    }
}