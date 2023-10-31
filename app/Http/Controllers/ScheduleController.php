<?php

namespace App\Http\Controllers;

use App\UseCase\ScheduleUseCase;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    public function __invoke(ScheduleUseCase $scheduleUseCase): Response
    {
        $scheduleUseCase();

        return response()->json(['ok']);
    }
}
