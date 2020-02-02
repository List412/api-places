<?php


namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class LocationService
{
    public function getNearbyPlaces($lat, $lng, $range)
    {
        $apiKey = $_ENV['GOOGLE_APi_KEY'];

        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$lat,$lng&radius=$range&key=$apiKey";
        $client = HttpClient::create();
        try {
            $response = $client
                ->request('GET', $url)
                ->toArray();
        } catch (\Exception $e) {

        }
        return $response;
    }

    public function getPlaceInfo($lat, $lng, $query)
    {
        $apiKey = $_ENV['GOOGLE_APi_KEY'];
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?location=$lat,$lng&query=$query&key=$apiKey";
        $client = HttpClient::create();
        try {
            $response = $client
                ->request('GET', $url)
                ->toArray();
        } catch (\Exception $e) {

        }
        return $response;
    }

    public function getCoordinatesByIp(string $ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception('invalid ip');
        }

        $apiKey = $_ENV['IP_GEO_API_KEY'];

        $client = HttpClient::create();
        try {
            $response = $client
                ->request('GET', "https://api.ipgeolocation.io/ipgeo?apiKey=$apiKey&ip=$ip")
                ->toArray();
        } catch (\Exception $e) {
            throw new \Exception('failed to get location for ip:' . $ip);
        }

        return [
            'lat' => $response['latitude'],
            'lng' => $response['longitude'],
            'city' => $response['city']
        ];
    }

    public function distance($lat1, $lng1, $lat2, $lng2)
    {
        if ($lat1 == $lat2 && $lng1 == $lng2) {
            return 0;
        }

        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1.609344;
    }

    public function getCityGoogle($lat, $long) {
        $get_API = "http://maps.googleapis.com/maps/api/geocode/json?latlng=";
        $get_API .= round($lat,6).",";
        $get_API .= round($long,6);

        $client = HttpClient::create();
        try {
            $response = $client
                ->request('GET', $get_API.'&sensor=false')
                ->toArray();
        } catch (\Exception $e) {

        }
        return $response;
    }



}