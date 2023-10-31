<?php

namespace App\Domains;

class MakeScheduleMessage
{
    public function __invoke(array $events): string
    {
        $eventsText = collect($events)->map(function ($event) {
            return $event->getSummary();
        })->filter()->join("\n・");

        return "今日の予定だよー！\n・" . $eventsText;
    }
}
