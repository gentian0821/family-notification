<?php

namespace App\Http\Controllers;

use App\UseCase\WeatherUseCase;
use Symfony\Component\HttpFoundation\Response;

class WeatherController extends Controller
{
    public function __invoke(WeatherUseCase $weatherUseCase): Response
    {
        $weatherUseCase();

        return response()->json(['ok']);
    }
}
