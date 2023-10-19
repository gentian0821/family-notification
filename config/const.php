<?php

return [
    'base_url' => 'https://fayc4.onrender.com',

    'line_access_token' => env('LINE_ACCESS_TOKEN'),

    'line_base_uri' => 'https://api.line.me',

    'line_push_api' => '/v2/bot/message/push',

    'fayc4_send_to' => env('FAYC4_SEND_TO'),
];
