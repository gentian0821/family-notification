<?php

namespace App\Domains;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class MakeWeatherMessage
{
    public function __invoke(array $weatherInfo): array
    {
        if (!$weatherInfo) {
            return [];
        }

        $carouselContents = [];
        $boxContents = [];
        $cnt = 0;
        foreach($weatherInfo['list'] as $weather) {
            $carbon = Carbon::createFromTimestamp($weather['dt']);
            $imageUrl = Config::get('const.base_url') . '/img/' . $this->get_icon($weather['weather'][0]['id']);

            if ($cnt <= 5) {
                $carouselContents[] = $this->makeOneDayFlexMessages($weather, $carbon, $imageUrl);
            }

            if ($carbon->isoFormat('HH') == 12) {
                $boxContents[] = $this->makeFiveDaysFlexMessages($weather, $carbon, $imageUrl);
            }

            $cnt++;
        }

        $message = [
            [
                'type' => 'flex',
                'altText' => '今日の天気だよー',
                'contents' => [
                    'type' => 'carousel',
                    'contents' => $carouselContents
                ]
            ],
            [
                'type' => 'flex',
                'altText' => '直近5日間の天気だよー',
                'contents' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => '明日以降の天気'
                            ],
                        ]
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => $boxContents,
                    ]
                ]
            ],
        ];

        return $message;
    }

    private function makeOneDayFlexMessages(array $weather, Carbon $carbon, string $imageUrl): array
    {
        $body = '天気　　： ' . $weather['weather'][0]['description'];
        $body .= "\n気温　　： " . floor($weather['main']['temp']) . '℃';
        $body .= "\n体感温度： " . floor($weather['main']['feels_like']) . '℃';
        $body .= "\n湿度　　： " . floor($weather['main']['humidity']) . '%';
        $body .= "\n気圧　　： " . floor($weather['main']['grnd_level']) . 'hpa';
        $body .= "\n風速　　： " . floor($weather['wind']['speed']) . 'm';

        return [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $carbon->isoFormat('YYYY年MM月DD日(ddd) HH:mm') . 'の天気'
                    ],
                    [
                        'type' => 'image',
                        'url' => $imageUrl,
                        'size' => 'full',
                        'aspectRatio' => '2:1'
                    ],
                    [
                        'type' => 'text',
                        'text' => $body,
                        'wrap' => true
                    ],
                ]
            ],
        ];
    }

    private function makeFiveDaysFlexMessages(array $weather, Carbon $carbon, string $imageUrl): array
    {
        $body = "気温：" . floor($weather['main']['temp']) . '℃';
        $body .= " 湿度：" . floor($weather['main']['humidity']) . '%';

        return [
            'type' => 'box',
            'layout' => 'horizontal',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => $carbon->isoFormat('MM/DD(ddd)') . ' ',
                    'size' => 'xs',
                    'gravity' => 'center',
                    'flex' => 0
                ],
                [
                    'type' => 'image',
                    'url' => $imageUrl,
                    'size' => 'xxs',
                    'aspectMode' => 'fit',
                    'gravity' => 'center',
                    'flex' => 1
                ],
                [
                    'type' => 'text',
                    'text' => $body,
                    'size' => 'xs',
                    'gravity' => 'center',
                    'flex' => 7
                ],
                [
                    'type' => 'text',
                    'text' => $weather['weather'][0]['description'],
                    'size' => 'xs',
                    'gravity' => 'center',
                    'flex' => 0
                ]
            ],
        ];
    }

    public function get_icon(int $id): string
    {
        if ($id >= 200 && $id < 300) {
            return 'thunder.png';
        }

        if ($id >= 300 && $id < 400) {
            return 'drizzle.png';
        }

        if ($id >= 500 && $id <= 504) {
            return 'light_rain.png';
        }

        if ($id >= 511 && $id <= 531) {
            return 'rain.png';
        }

        if ($id >= 600 && $id < 700) {
            return 'snow.png';
        }

        if ($id == 800 || $id == 801) {
            return 'sunny.png';
        }

        if ($id == 802 || $id == 803) {
            return 'sunny_cloud.png';
        }

        if ($id == 804) {
            return 'cloud.png';
        }

        return '';
    }
}
