<?php

namespace App\UseCase;

use App\Domains\MakeScheduleMessage;
use App\Repositories\GoogleCalendarRepository;
use App\Repositories\LineMessageApiRepository;
use Illuminate\Support\Facades\Config;

class ScheduleUseCase
{
    public function __construct(
        private LineMessageApiRepository $lineMessageApiRepository,
        private GoogleCalendarRepository $googleCalendarRepository
    ) {
    }

    public function __invoke(): void
    {
        $events = $this->googleCalendarRepository->getEvents();

        $makeScheduleMessage = app()->make(MakeScheduleMessage::class);

        $this->lineMessageApiRepository->push(
            [
                [
                    'type' => 'text',
                    'text' => $makeScheduleMessage($events),
                ]
            ],
            Config::get('const.fayc4_send_to')
        );
    }
}
