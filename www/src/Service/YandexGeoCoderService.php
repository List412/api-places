<?php


namespace App\Service;


use Symfony\Component\HttpClient\HttpClient;

class YandexGeoCoderService
{
    private $http;
    private $apiKey;
    private $url;

    public function __construct()
    {
        $this->http = HttpClient::create();
        $this->apiKey = $_ENV['YA_GEO_API_KEY'];
        $this->url = "https://geocode-maps.yandex.ru/1.x";
    }

    public function request($geocode)
    {
        $params = http_build_query([
            'apikey' => $this->apiKey,
            'geocode' => $geocode,
            'format' => 'json'
        ]);
        return $this->http->request('GET', $this->url . '?' . $params);
    }

    public function getCity($lat, $lng)
    {
        $response = $this->request("$lng, $lat")->toArray();
        $geoObject = $response['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
        $addressFields = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['Components'];
        $city = array_filter($addressFields, function ($item) { return $item['kind'] == 'locality';});
        return array_values($city)[0]['name'];
    }
}