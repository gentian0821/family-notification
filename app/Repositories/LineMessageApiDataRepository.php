<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class LineMessageApiDataRepository
{
    private $client;

    private $headers;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => Config::get('const.line_data_base_uri')]);
        $this->headers = [
            'Authorization' => 'Bearer ' . Config::get('const.line_access_token'),
        ];
    }

    public function getContents(int $message_id): ResponseInterface
    {
        $options = [
            'headers' => $this->headers,
        ];

        return $this->client->request('GET', sprintf(Config::get('const.line_content_api'), $message_id), $options);
    }
}
