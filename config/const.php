<?php

return [
    // app settings
    'base_url' => 'https://fayc4.onrender.com',
    'app_api_key' => env('APP_API_KEY'),

    // Line Settings
    'line_access_token' => env('LINE_ACCESS_TOKEN'),
    'line_base_uri' => 'https://api.line.me',
    'line_push_api' => '/v2/bot/message/push',
    'fayc4_send_to' => env('FAYC4_SEND_TO'),

    // Google Settings
    'google_api_credential' => env("GOOGLE_API_CREDENTIAL"),
    'calendar_id' => env("GOOGLE_CALENDAR_ID"),
];
