<?php

use App\Http\Controllers\NotifyController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WeatherController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware([AuthenticateApiToken::class])-> group(function () {
    Route::post('notify', NotifyController::class);
    Route::get('schedule', ScheduleController::class);
    Route::get('weather', WeatherController::class);
});