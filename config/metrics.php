<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default metrics driver used to collect and
    | dispatch metrics. Each driver determines how metrics are stored and
    | where they are ultimately sent.
    |
    | Supported: "memory", "log", "statsd"
    |
    */

    'driver' => env('METRICS_DRIVER', 'memory'),

    /*
    |--------------------------------------------------------------------------
    | Default Tags
    |--------------------------------------------------------------------------
    |
    | Tags applied to every metric emitted by the application. Use them to
    | segment metrics by environment, host, region, or release. Keep the
    | cardinality low — avoid user IDs, request IDs, or timestamps.
    |
    */

    'tags' => [
        'env'     => env('APP_ENV', 'production'),
        'host'    => gethostname() ?: 'unknown',
        'region'  => env('APP_REGION', 'local'),
        'version' => env('APP_VERSION', '1.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure each metrics driver. Drivers are resolved
    | lazily — only the active driver is instantiated at boot time.
    |
    */

    'drivers' => [

        /*
        |----------------------------------------------------------------------
        | In-Memory Driver
        |----------------------------------------------------------------------
        |
        | Stores metrics in process memory. Ideal for development and tests.
        | Metrics are lost when the process exits. Use `metrics()->all()` to
        | inspect them.
        |
        */

        'memory' => [
            'driver' => 'memory',
        ],

        /*
        |----------------------------------------------------------------------
        | Log Driver
        |----------------------------------------------------------------------
        |
        | Writes each metric to the application log. A pragmatic choice for
        | production environments without dedicated metrics infrastructure.
        | Metrics can be parsed downstream by a log shipper.
        |
        | Note: this driver is fire-and-forget. It does not support reading
        | metrics back — `metrics()->all()` returns an empty array.
        |
        */

        'log' => [
            'driver'  => 'log',
            'channel' => env('METRICS_LOG_CHANNEL', null),
            'level'   => env('METRICS_LOG_LEVEL', 'info'),
        ],

        /*
        |----------------------------------------------------------------------
        | StatsD Driver
        |----------------------------------------------------------------------
        |
        | Sends metrics over UDP to a StatsD-compatible agent (Datadog,
        | Etsy StatsD, Telegraf, etc.). Best for production environments
        | with a metrics backend already in place.
        |
        */

        'statsd' => [
            'driver' => 'statsd',
            'host'   => env('STATSD_HOST', '127.0.0.1'),
            'port'   => env('STATSD_PORT', 8125),
            'prefix' => env('STATSD_PREFIX', 'app.'),
            'tags'   => env('STATSD_TAGS_ENABLED', true),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Sampling
    |--------------------------------------------------------------------------
    |
    | Control the sampling rate for high-volume metrics. A value of 1.0
    | captures 100% of events. Lower values reduce overhead at the cost
    | of statistical precision.
    |
    | Format: [ 'metric.pattern' => rate ]
    |
    | Patterns support the wildcard `*` and are evaluated in order.
    |
    */

    'sampling' => [
        // 'http.request'     => 0.1,
        // 'db.query.*'       => 0.05,
        // 'notifications.*'  => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Instrumentation
    |--------------------------------------------------------------------------
    |
    | Automatically emit metrics for common framework events. Enable only
    | what you actually use — each hook adds a small overhead to every
    | request.
    |
    */

    'auto' => [

        /*
        | HTTP requests — emits:
        |   http.request.count      (increment, tagged by route, method, status)
        |   http.request.duration   (timing,    tagged by route, method)
        */

        'http' => env('METRICS_AUTO_HTTP', false),

        /*
        | Database queries — emits:
        |   db.query.count          (increment, tagged by connection, type)
        |   db.query.duration       (timing,    tagged by connection, type)
        */

        'database' => env('METRICS_AUTO_DB', false),

        /*
        | Queue jobs — emits:
        |   queue.job.pushed        (increment, tagged by queue, notification)
        |   queue.job.processed     (increment, tagged by queue)
        |   queue.job.failed        (increment, tagged by queue, error)
        |   queue.job.duration      (timing,    tagged by queue)
        */

        'queue' => env('METRICS_AUTO_QUEUE', false),

        /*
        | Notifications — emits:
        |   notifications.sending   (increment, tagged by channel)
        |   notifications.sent      (increment, tagged by channel, event)
        |   notifications.failed    (increment, tagged by channel, error)
        |   notifications.skipped   (increment, tagged by channel)
        |   notifications.duration  (timing,    tagged by channel)
        */

        'notifications' => env('METRICS_AUTO_NOTIFICATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Flush Interval
    |--------------------------------------------------------------------------
    |
    | For buffered drivers, the number of seconds between automatic flushes.
    | Set to 0 to disable periodic flushing (flush happens on shutdown).
    | Has no effect on the memory and log drivers.
    |
    */

    'flush_interval' => env('METRICS_FLUSH_INTERVAL', 10),

    /*
    |--------------------------------------------------------------------------
    | Cardinality Guard
    |--------------------------------------------------------------------------
    |
    | Protect against runaway tag cardinality. When the number of unique
    | tag combinations for a single metric key exceeds this threshold,
    | subsequent combinations are dropped and a warning is logged.
    |
    | Set to 0 to disable the guard (not recommended in production).
    |
    */

    'cardinality_limit' => env('METRICS_CARDINALITY_LIMIT', 1000),

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | When enabled, the metrics subsystem uses an in-memory driver regardless
    | of the default driver. Useful to isolate tests from real metrics
    | backends.
    |
    */

    'fake_when_testing' => env('METRICS_FAKE_WHEN_TESTING', false),

];