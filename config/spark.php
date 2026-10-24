<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Filtering
    |--------------------------------------------------------------------------
    | By default, Spark will not expose any routes for security reasons.
    | You must explicitly allow route groups or specific names.
    */
    'only' => [
        [
            'name' => 'login',
            'policy' => null
        ],
        [
            'name' => 'logout',
            'policy' => null
        ],
        [
            'name' => 'home',
            'policy' => null
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OR: Exclusion (Blacklist)
    |--------------------------------------------------------------------------
    | If you prefer to expose everything EXCEPT specific routes.
    */
    // 'except' => [
    //     'admin.*',
    //     'horizon.*',
    // ],

    /*
    |--------------------------------------------------------------------------
    | Groups
    |--------------------------------------------------------------------------
    | Define route groups for selective exposure.
    */
    'groups' => [
        'public' => ['home', 'about', 'contact'],
        'auth' => ['dashboard', 'profile', 'settings'],
    ],
];
