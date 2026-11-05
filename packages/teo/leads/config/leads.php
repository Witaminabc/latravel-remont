<?php

return [
    'notify_email' => env('CHAT_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS')),

    'statuses' => [
        'new'      => 'Новая',
        'processed' => 'Обработана',
    ],
];
