<?php

/*
|--------------------------------------------------------------------------
| Application Authentication Configuration
|--------------------------------------------------------------------------
|
| Here you may specify the configuration for the authentication services
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration for the JWT tokens
    |
    */
    'tokens' => [

        /*
        |--------------------------------------------------------------------------
        | JWT Header Configuration
        |--------------------------------------------------------------------------
        |
        | Here you may specify the configuration for the JWT header
        |
        */
        'header' => [
            'algo' => 'HS256',
            'type' => 'JWT'
        ],

        /*
        |--------------------------------------------------------------------------
        | JWT Payload Configuration
        |--------------------------------------------------------------------------
        |
        | Here you may specify the configuration for the JWT payload
        |
        */
        'expire' => 86400,
        'algo' => 'sha256'
    ]
];
