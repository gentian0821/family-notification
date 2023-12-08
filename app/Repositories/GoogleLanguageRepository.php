<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Config;
use Google\Cloud\Language\V1beta2\Document;
use Google\Cloud\Language\V1beta2\Document\Type;
use Google\Cloud\Language\V1beta2\LanguageServiceClient;

class GoogleLanguageRepository
{
    private $client;

    public function __construct()
    {
        if (!file_exists(Config::get('const.google_application_credential'))) {
            file_put_contents(
                Config::get('const.google_application_credential'),
                Config::get('const.google_api_credential')
            );
        }

        $this->client = new LanguageServiceClient(['projectId' => 'fayc4-249803']);
    }

    /**
     * @param $text
     * @return mixed
     * @throws \Google\ApiCore\ApiException
     */
    public function sentiment($text)
    {
        $document = new Document();
        $document->setContent($text)->setType(Type::PLAIN_TEXT);
        $response = $this->client->analyzeSentiment($document);
        $documentSentiment = $response->getDocumentSentiment();

        return $documentSentiment;
    }
}
