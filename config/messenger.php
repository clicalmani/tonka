<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Transport
    |--------------------------------------------------------------------------
    | This option controls the default transport that is used to send all
    | messages unless another transport is explicitly specified when sending
    | the message. All additional transports can be configured within the
    | "transports" array. Examples of each type of transport are provided.
    |
    */

    'default' => env('MESSENGER_TRANSPORT', 'elegant'),

    /*
    |--------------------------------------------------------------------------
    | Transports Configurations
    |--------------------------------------------------------------------------
    | Here you may configure all of the transports used by your application to
    | send messages. Several examples have been configured for you and you are 
    | free to add your own as your application requires.
    |
    | Tonka supports a variety of messenger "transport" drivers that can be used
    | when delivering a message. You may specify which one you're using for
    | your messages below. You may also add additional transports if needed.
    |
    | Supported: "elegant", "amqp", "redis"
    |
    */

    'transports' => [

        'elegant' => [
            'scheme' => env('MESSENGER_TRANSPORT', 'elegant'),
            'host' => env('MESSENGER_HOST', '127.0.0.1'),
            'port' => env('MESSENGER_PORT', 2525),
            'username' => env('MESSENGER_USERNAME', ''),
            'password' => env('MESSENGER_PASSWORD', '')
        ],
    ]
];
