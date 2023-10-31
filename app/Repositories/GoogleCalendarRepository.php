<?php

namespace APP\Repositories;

use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Config;

class GoogleCalendarRepository
{
    private Google_Service_Calendar $googleServiceCalendar;

    public function __construct(private Google_Client $googleClient)
    {
        $json = json_decode(Config::get('const.google_api_credential'), true);

        $this->googleClient->setApplicationName('calendar');
        $this->googleClient->setAuthConfig($json);
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
        $this->googleServiceCalendar = new Google_Service_Calendar($googleClient);
    }

    public function getEvents(): array
    {
        $params = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMax' => date('c',strtotime(date('Y-m-d 23:59:59'))),
            'timeMin' => date('c',strtotime(date('Y-m-d 00:00:00'))),//2019年1月1日以降の予定を取得対象
        );

        $results = $this->googleServiceCalendar->events->listEvents(
            Config::get('const.calendar_id'),
            $params
        );

        return $results->getItems();
    }
}
