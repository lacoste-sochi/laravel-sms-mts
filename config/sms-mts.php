<?php

return [
    'auth_method' => env('SMS_MTS_AUTH_METHOD', 'token'), // 'token' или 'basic'
    'token' => env('SMS_MTS_TOKEN'),
    'login' => env('SMS_MTS_LOGIN'),
    'password' => env('SMS_MTS_PASSWORD'),
    'sender' => env('SMS_MTS_SENDER_NAME', 'YourSenderName'),
    'timeout' => env('SMS_MTS_TIMEOUT', 10),
    'connect_timeout' => env('SMS_MTS_CONNECT_TIMEOUT', 5),
    'api_urls' => [
        'token' => env('SMS_MTS_API_URL_TOKEN', 'https://api.mts.ru/client-omni-adapter_production/1.0.2/mcom/messageManagement/messages'),
        'basic' => env('SMS_MTS_API_URL_BASIC', 'https://omnichannel.mts.ru/http-api/v1/messages'),
    ],
];
