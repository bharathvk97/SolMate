<?php
// config/services.php
return [
    'mailgun'  => ['domain'=>env('MAILGUN_DOMAIN'),'secret'=>env('MAILGUN_SECRET'),'endpoint'=>env('MAILGUN_ENDPOINT','api.mailgun.net'),'scheme'=>'https'],
    'postmark' => ['token'=>env('POSTMARK_TOKEN')],
    'ses'      => ['key'=>env('AWS_ACCESS_KEY_ID'),'secret'=>env('AWS_SECRET_ACCESS_KEY'),'region'=>env('AWS_DEFAULT_REGION','us-east-1')],
    'razorpay' => [
        'key_id'     => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
    ],
    'firebase' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'sender_id'  => env('FIREBASE_SENDER_ID'),
    ],
];
