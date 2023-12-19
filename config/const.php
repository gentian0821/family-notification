<?php

return [
    // app settings
    'base_url' => 'https://family-notification.onrender.com',
    'app_api_key' => env('APP_API_KEY'),

    // Line Settings
    'line_access_token' => env('LINE_ACCESS_TOKEN'),
    'line_base_uri' => 'https://api.line.me',
    'line_data_base_uri' => 'https://api-data.line.me',
    'line_push_api' => '/v2/bot/message/push',
    'line_reply_api' => '/v2/bot/message/reply',
    'line_content_api' => '/v2/bot/message/%s/content',
    'fayc4_send_to' => env('FAYC4_SEND_TO'),

    // Google Settings
    'google_application_credential' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    'google_api_credential' => env('GOOGLE_API_CREDENTIAL'),
    'google_api_key' => env('GOOGLE_API_KEY'),
    'cloud_vision_base_url' => 'https://vision.googleapis.com',
    'cloud_vision_annotate_api' => '/v1/images:annotate',
    'calendar_id' => env("GOOGLE_CALENDAR_ID"),

    // Open Weather Settings
    'open_weather_base_url' => 'https://api.openweathermap.org',
    'open_weather_api_weather' => '/data/2.5/forecast',
    'open_weather_api_key' => env('OPEN_WEATHER_API_KEY'),
];
