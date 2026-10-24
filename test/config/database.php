<?php
return [

    /**
     * |-----------------------------------------------------------------------
     * | Enable Foreign Key Check
     * |-----------------------------------------------------------------------
     * 
     * This option determines whether foreign key checks are enabled for the database connection.
     * Enabling foreign key checks ensures that the integrity of your database is maintained by enforcing
     * the relationships between tables. If you set this option to false, foreign key checks will be disabled, 
     * which can lead to potential data integrity issues if not used carefully. It is generally recommended
     */
    'strict' => false,

    /**
     * |-----------------------------------------------------------------------
     * | Enable Auto-Casting of Data Types
     * |-----------------------------------------------------------------------
     * 
     * This option determines whether the database connection should automatically cast data types when retrieving data from the database.
     * When enabled, the database connection will automatically convert data types to their appropriate PHP types based on the column definitions in the database schema. 
     * This can help simplify data handling and reduce the need for manual type casting in your application code. However, it may also introduce some performance overhead, 
     * so use this option judiciously based on your application's requirements.
     */
    'autoCast' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Tonka is shown below to make development simple.
    |
    |
    | All database work in Tonka is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'      => env('DB_NAME', ''),
            'username'      => env('DB_USER', ''),
            'password'      => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_520_ci',
            'prefix'    => env('DB_TABLE_PREFIX', ''),
            'engine'    => null
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'default',
            'engine' => null,
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'SQL_Latin1_General_CP1_CI_AS',
            'engine' => null,
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],
    ]
];
