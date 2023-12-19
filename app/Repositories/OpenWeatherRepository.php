<?php

namespace APP\Repositories;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class OpenWeatherRepository
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => Config::get('const.open_weather_base_url')]);
    }

    public function forecasts(): array
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json; charser=UTF-8',
            ],
        ];

        $weatherUrl = Config::get('const.open_weather_api_weather') . '?zip=112-0011,JP&units=metric&lang=ja&APPID=';

        $apiKey = Config::get('const.open_weather_api_key');

        $response = $this->client->request('GET', $weatherUrl . $apiKey, $options);

        return json_decode($response->getBody(), true);
    }
}
