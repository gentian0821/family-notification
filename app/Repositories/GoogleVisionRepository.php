<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GoogleVisionRepository
{
    private $client;

    private $headers;

    private $api_key;

    public function __construct()
    {
        // $this->client = new Client(['base_uri' => Config::get('const.cloud_vision_base_uri')]);
        // $this->headers = [
        //     'Content-Type' => 'application/json; charser=UTF-8',
        // ];
        // $this->api_key = Config::get('const.cloud_vision_api_key');
    }

    public function annotate(string $content): string
    {
        $request_json = [
            "requests" => [
                [
                    "image" => [
                        "content" => base64_encode($content)
                    ],
                    "features" => [
                        [
                            "type" => "TEXT_DETECTION" ,
                            "maxResults" => 3 ,
                        ] ,
                    ],
                ],
            ],
        ];

        $curl = curl_init() ;
        curl_setopt( $curl, CURLOPT_URL, Config::get('const.cloud_vision_base_uri') . Config::get('const.cloud_vision_annotate_api') . "?key=" . Config::get('const.google_api_key') ) ;
        curl_setopt( $curl, CURLOPT_HEADER, true ) ;
        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST" ) ;
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ) ) ;
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ) ;
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ) ;
        curl_setopt( $curl, CURLOPT_TIMEOUT, 15 ) ;
        curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($request_json) ) ;
        $res1 = curl_exec( $curl ) ;
        $res2 = curl_getinfo( $curl ) ;
        curl_close( $curl ) ;

        $response = json_decode(substr( $res1, $res2["header_size"] ), true);
        Log::info($response);
        return $response;

//        $options = [
//            'json' => $request_json,
//            'headers' => $this->headers,
//        ];
//Log::info(Config::get('const.cloud_vision_annotate_api') . '?key=' . $this->api_key);
//        $response = $this->client->request(
//            'POST',
//            Config::get('const.cloud_vision_annotate_api') . '?key=' . $this->api_key,
//            $options
//        );
//
//        return json_decode($rsponse->getBody(), true);
    }
}
