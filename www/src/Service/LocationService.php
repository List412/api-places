<?php


namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class LocationService
{
    public function getCoordinatesByIp(string $ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception('invalid ip');
        }

        $client = HttpClient::create();
        try {
            $response = $client
                ->request('GET', 'https://api.ipgeolocationapi.com/geolocate/' . $ip)
                ->toArray();
        } catch (\Exception $e) {
            throw new \Exception('failed to get location for ip:' . $ip);
        }

        return [
            'lat' => $response['geo']['latitude'],
            'lng' => $response['geo']['longitude'],
        ];
    }
}