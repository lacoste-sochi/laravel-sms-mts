<?php

return [
    'api_key' => env('MTS_SMS_API_KEY'),
    'api_url' => env('MTS_SMS_API_URL', 'https://api.mts.ru/sms/v1'),
    'sender_name' => env('MTS_SMS_SENDER_NAME'),
    'default_channel' => env('MTS_SMS_DEFAULT_CHANNEL', 'default'),
];