<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the application name which will be used in the 
    | application. The name is used to identify the application.
    |
    */

    'name' => env('APP_NAME', 'Tonka'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the application version which will be used in the 
    | application. The version is for versioning the application.
    |
    */

    'version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the console command line tool. You should set this to the root of
    | the application so that it's available within console commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Tonka's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |---------------------------------------------------------------------------
    | JSON 
    |---------------------------------------------------------------------------
    | 
    | This settings instruct how to encode and decode json string and convert it 
    | into a PHP value.
    */

    'json' => [
        'encode' => [
            'flags' => JSON_UNESCAPED_UNICODE,
            'depth' => 512
        ],
        'decode' => [
            'associative' => true,
            'depth' => 512,
            'flags' => JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Tonka Framework Service Providers...
         */
        Clicalmani\Foundation\Auth\AuthServiceProvider::class,
        Clicalmani\Foundation\Providers\HelpersServiceProvider::class,
        Clicalmani\Foundation\Auth\EncryptionServiceProvider::class,
        Clicalmani\Foundation\Providers\RouteServiceProvider::class,
        Clicalmani\Foundation\Providers\LogServiceProvider::class,
        Clicalmani\Foundation\Providers\ContainerServiceProvider::class,
        Clicalmani\Foundation\Providers\ValidationServiceProvider::class,
        
        /*
         * Application Service Providers...
         */
        App\Providers\SessionServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
    ]
];
