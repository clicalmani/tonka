<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection
    |--------------------------------------------------------------------------
    |
    | Supported: "sync", "database", "redis"
    |
    */

    'connection' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        /*
        |----------------------------------------------------------------------
        | Sync Driver
        |----------------------------------------------------------------------
        |
        | Executes jobs inline. Useful for local development and testing.
        | No persistence — jobs are executed the moment they are pushed.
        |
        */

        'sync' => [
            'driver' => 'sync',
        ],

        /*
        |----------------------------------------------------------------------
        | Database Driver
        |----------------------------------------------------------------------
        |
        | Stores jobs in a database table. Simple and reliable — no external
        | infrastructure required. Requires the `jobs` table.
        |
        */

        'database' => [
            'driver' => 'database',
            'table'  => env('QUEUE_TABLE', 'jobs'),
            'queue'  => env('QUEUE_DEFAULT', 'default'),
        ],

        /*
        |----------------------------------------------------------------------
        | Redis Driver
        |----------------------------------------------------------------------
        |
        | Uses Redis lists and sorted sets. Lowest latency, highest
        | throughput. Recommended for production at scale.
        |
        */

        'redis' => [
            'driver' => 'redis',
            'prefix' => env('QUEUE_PREFIX', 'queue:'),
            'queue'  => env('QUEUE_DEFAULT', 'default'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Defaults
    |--------------------------------------------------------------------------
    */

    'worker' => [

        /*
        | Queues to process, in order of priority.
        */

        'queues' => explode(',', env('QUEUE_WORKER_QUEUES', 'default')),

        /*
        | Seconds to sleep when no job is available.
        */

        'sleep' => env('QUEUE_WORKER_SLEEP', 1),

        /*
        | Maximum memory (MB) before the worker exits gracefully.
        */

        'memory' => env('QUEUE_WORKER_MEMORY', 128),

        /*
        | Maximum job timeout (seconds) enforced by the worker.
        */

        'timeout' => env('QUEUE_WORKER_TIMEOUT', 60),

    ],

];