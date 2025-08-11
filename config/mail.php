<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'scheme' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD')
        ],

        'postmark' => [
            'schema' => 'postmark+smtp',
            'host' => 'default',
            'port' => null,
            'username' => 'ID',
            'password' => null
        ],

        'resend' => [
            'schema' => 'resend+smtp',
            'host' => 'default',
            'port' => null,
            'username' => 'resend',
            'password' => 'API_KEY'
        ],

        'sendmail' => [
            'schema' => 'sendmail',
            'host' => 'default',
            'port' => null,
            'username' => null,
            'password' => null
        ],

        'failover' => [
            'mailers' => [
                'smtp',
                'postmark',
            ],
        ],

        'roundrobin' => [
            'mailers' => [
                'smtp',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    'to' => [
        'address' => env('MAIL_TO_ADDRESS', ''),
        'name' => env('MAIL_TO_NAME', ''),
    ],

    'cc' => [
        'address' => env('MAIL_CC_ADDRESS', ''),
        'name' => env('MAIL_CC_NAME', ''),
    ],

    'headers' => [
        'dates' => [
            'X-Mailer-Date' => \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new \DateTimeZone('UTC')),
            'X-Mailer-Received' => \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new \DateTimeZone('UTC')),
        ],
        'mailbox' => [
            'X-Mailer-From' => 'Clicalmani Mailer <' . env('MAIL_FROM_ADDRESS') . '>',
            'X-Mailer-To' => 'Clicalmani Mailer <' . env('MAIL_TO_ADDRESS', '') . '>',
        ],
        'tags' => ['password-reset'],
        'metadata' => [
            'X-Mailer' => 'Clicalmani Mailer',
            'X-Mailer-Version' => '2.3.4',
        ],
        'unstructured' => [
            'X-Mailer-Header' => 'Clicalmani Mailer',
        ],
        'parametized' => [
            'X-Mailer-Param' => 'Clicalmani Mailer',
        ]
    ],

];
