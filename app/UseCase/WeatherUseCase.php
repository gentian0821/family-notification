<?php

namespace App\UseCase;

use App\Domains\MakeWeatherMessage;
use App\Repositories\OpenWeatherRepository;
use App\Repositories\LineMessageApiRepository;
use Illuminate\Support\Facades\Config;

class WeatherUseCase
{
    public function __construct(
        private LineMessageApiRepository $lineMessageApiRepository,
        private OpenWeatherRepository $openWeatherRepository
    ) {
    }

    public function __invoke(): void
    {
        $forecasts = $this->openWeatherRepository->forecasts();

        $makeWeatherMessage = app()->make(MakeWeatherMessage::class);
        $messages = $makeWeatherMessage($forecasts);

        $this->lineMessageApiRepository->push($messages, Config::get('const.fayc4_send_to'));
    }
}
