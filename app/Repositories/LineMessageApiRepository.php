<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class LineMessageApiRepository
{
    private Client $guzzleClient;

    private array $headers;

    public function __construct()
    {
        $this->guzzleClient = new Client(
            [
                'base_uri' => Config::get('const.line_base_uri')
            ]
        );

        $this->headers = [
            'Content-Type' => 'application/json; charser=UTF-8',
            'Authorization' => 'Bearer ' . Config::get('const.line_access_token'),
        ];
    }

    public function push(array $messages, string $send_to): ResponseInterface
    {
        $options = [
            'json' => [
                "to" => $send_to,
                "messages" => $messages,
            ],
            'headers' => $this->headers,
        ];

        return $this->guzzleClient->request('POST', Config::get('const.line_push_api'), $options);
    }

    public function reply(array $messages, string $replyToken): ResponseInterface
    {
        $options = [
            'json' => [
                "replyToken" => $replyToken,
                "messages" => $messages
            ],
            'headers' => $this->headers,
        ];

        return $this->guzzleClient->request('POST', Config::get('const.line_reply_api'), $options);
    }
}
