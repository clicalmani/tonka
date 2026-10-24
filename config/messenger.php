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
            'scheme' => env('MESSENGER_TRANSPORT', 'elegant://default')
        ],
        'sync'  => 'sync://',
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Routing
    |--------------------------------------------------------------------------
    | Here you may specify the message routing for your application. This is used
    | to determine which transport should be used to send a given message based
    | on the message class. You may specify a single transport or an array of
    | transports for each message class. 
    |
    */
    'routing' => [
        // Example: \App\Messages\MyMessage::class => 'elegant',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Strategy
    |--------------------------------------------------------------------------
    | Here you may specify the retry strategy for your application. This is used
    | to determine how many times a message should be retried before it is sent
    | to the failure transport. You may specify a single retry strategy or an array
    | of retry strategies for each message class.
    |
    */
    'retry_strategy' => [
        'default' => 'elegant',
        'strategies' => [
            'elegant' => [
                'max_retries' => env('MESSENGER_RETRIES', 3),
                'delay' => 1000, // in milliseconds
                'max_delay' => 0, // in milliseconds
                'multiplier' => 2,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Transports
    |--------------------------------------------------------------------------
    | Here you may specify the retry transports for your application. This is used
    | to determine which transport should be used to send a given message when it
    | fails to be processed. You may specify a single transport or an array of
    | transports for each message class.
    */
    'retry_transports' => [
        'elegant' => 'messenger.transport.elegant',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failure Transports
    |--------------------------------------------------------------------------
    | Here you may specify the failure transports for your application. This is used
    | to determine which transport should be used to send a given message when it
    | fails to be processed. You may specify a single transport or an array of
    | transports for each message class.
    */
    'failure_transports' => [
        'elegant' => 'messenger.transport.failed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Messenger Options
    |--------------------------------------------------------------------------
    | Here you may specify additional options for the messenger component. These
    | options can be used to configure various aspects of the messenger, such as
    | the queue name, retry attempts, and whether to keep messages after processing.
    |
    */
    'message_table' => env('MESSENGER_MESSAGE_TABLE', 'messenger_messages'),
    'failed_table' => env('MESSENGER_FAILED_TABLE', 'messenger_failed'),
    'queue_name' => env('MESSENGER_QUEUE_NAME', 'default'),
    'retries' => env('MESSENGER_RETRIES', 3),
    'keep' => false
];
