<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'allowed_origin' => '*',

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => @ $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '*',

    'max_age' => 86400, // Cache preflight response for one (1) day

    'allow_credentials' => false,

];
