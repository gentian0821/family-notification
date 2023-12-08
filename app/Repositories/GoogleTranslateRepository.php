<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Config;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslateRepository
{
    private $client;

    public function __construct()
    {
        $this->client = new TranslateClient(['key' => Config::get('const.google_api_key')]);
    }

    public function translate(string $message, string $lang)
    {
        return $this->client->translate($message, ['target' => $lang]);
    }
}
