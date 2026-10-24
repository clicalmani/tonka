<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by your
    | application to broadcast events. You may set this to any of the
    | connections defined in the "connections" array below.
    |
    */

    'default' => env('BROADCAST_DRIVER', 'mercure'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the broadcast connections / drivers used
    | by your application to emit events to your frontend or real-time hubs.
    | Several examples are provided, and you are free to add your own.
    |
    | Supported: "mercure", "log", "null"
    |
    */

    'connections' => [

        'mercure' => [
            'driver' => 'mercure',
            'url'    => env('MERCURE_HUB_URL', 'http://localhost:3000/.well-known/mercure'),
            'token'  => env('MERCURE_JWT_TOKEN', ''),
            'secret' => env('MERCURE_JWT_SECRET'),
            'ttl'    => (int) env('MERCURE_JWT_TTL', 900),
        ],
        
        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    'auth_prefix' => env('BROADCAST_AUTH_PREFIX', 'broadcasting'),
];