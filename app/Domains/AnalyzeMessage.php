<?php

namespace App\Domains;

use App\Repositories\LineMessageApiDataRepository;
use App\Repositories\GoogleVisionRepository;
use App\Repositories\GoogleLanguageRepository;
use App\Repositories\GoogleTranslateRepository;

class AnalyzeMessage
{
    public function __construct(
        private LineMessageApiDataRepository $lineMessageApiDataRepository,
        private GoogleVisionRepository $googleVisionRepository,
        private GoogleLanguageRepository $googleLanguageRepository,
        private GoogleTranslateRepository $googleTranslateRepository
    ) {
    }

    public function __invoke(array $events): array
    {
        if (isset($events['message']['type']) && $events['message']['type'] === 'sticker') {
            return [];
        }

        if (isset($events['message']['type']) && $events['message']['type'] === 'image') {
            return [$this->image($events['message'])];
        }

        $result = $this->translate($events['message']['text']);
        if ($result) {
            return [$result];
        }

        return $this->defaultMessages($events);
    }

    private function image(array $message): array
    {
        $response = $this->lineMessageApiDataRepository->getContents($message['id']);
        $visionResponse = $this->googleVisionRepository->annotate($response->getBody());

        return [
            'type' => 'text',
            'text' => $visionResponse['responses'][0]['textAnnotations'][0]['description']
        ];
    }

    private function translate(string $pushText): array
    {
        if (!preg_match('/^翻訳 (.*)/u',$pushText, $matches)) {
            return [];
        }

        $source_text = $matches[1];
        $asciiCount = 0;
        $multibyteCount = 0;
        $length = mb_strlen($source_text, 'UTF-8');
        for ($i = 0; $i < $length; $i += 1) {
            $char = mb_substr($source_text, $i, 1, 'UTF-8');
            if (mb_check_encoding($char, 'ASCII')) {
                $asciiCount++;
                continue;
            }
            $multibyteCount++;
        }

        $lang = $asciiCount > $multibyteCount ? 'ja' : 'en';
        $result = $this->googleTranslateRepository->translate($matches[1], $lang);

        return [
            'type' => 'text',
            'text' => $result['text'],
        ];
    }

    private function defaultMessages(array $events): array
    {
        $key = rand(1,10);

        if ($key >= 3) {
            return [$this->emotion($events['message']['text'])];
        }

        $default_messages = [
            1 => [
                [
                    'type' => 'text',
                    'text' => 'ふぁいしーふぉーだよー！'
                ],
                [
                    'type' => 'text',
                    'text' => $this->pictureLetter('1F98B')
                ]
            ],
            2 => [[
                'type' => 'text',
                'text' => $events['message']['text']
            ]],
        ];

        return $default_messages[$key];
    }

    private function emotion(string $text): array
    {
        $result = $this->googleLanguageRepository->sentiment($text);
        $score = $result->getScore();

        if ($score > 0.5) {
            return [
                'type' => 'text',
                'text' => 'やったね！' . $this->pictureLetter('1F604')
            ];
        }

        if ($score > 0.0) {
            return [
                'type' => 'text',
                'text' => 'うんうん！' . $this->pictureLetter('1F642')
            ];
        }

        if ($score > -0.5) {
            return [
                'type' => 'text',
                'text' => 'ざんねん！' . $this->pictureLetter('1F62B')
            ];
        }

        if ($score > -1.0) {
            return [
                'type' => 'text',
                'text' => 'えーーーん...' . $this->pictureLetter('1F62D')
            ];
        }
    }

    private function pictureLetter(string $code): string
    {
        $bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
        return mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');
    }
}
