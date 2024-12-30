<?php 
return [

    /**
     * |-------------------------------------------------------------------
     * |                          Web Middlewares
     * |-------------------------------------------------------------------
     * 
     */
    'web' => [
        // ...
    ],

    /**
     * |-------------------------------------------------------------------
     * |                        API Middlewares
     * |-------------------------------------------------------------------
     * 
     */
    'api' => [
        'api' => App\Http\Middlewares\Authenticate::class,
    ],

    /**
     * |-------------------------------------------------------------------
     * |                          Validators
     * |-------------------------------------------------------------------
     * 
     * Custom validators
     */
    'validators' => [
        // ...
    ]
];