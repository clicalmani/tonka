<?php

namespace App\Http;

use Clicalmani\Foundation\Maker\HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected array $middleware = [

        /**
         * |-------------------------------------------------------------------
         * |                          Web Gateway
         * |-------------------------------------------------------------------
         * 
         * Web gateway middleware stack
         * 
         * Register here your custom middlewares for web gateway.
         */
        'web' => [
            // Add here your custom middlewares
        ],

        /**
         * |-------------------------------------------------------------------
         * |                          API Gateway
         * |-------------------------------------------------------------------
         * 
         * API gateway middleware stack
         * 
         * Register here your custom middlewares for api gateway.
         */
        'api' => [
            // Add here your custom middlewares
        ]
    ];

    /**
     * The application's global HTTP validator stack.
     *
     * These validators can be invoked anywhere in your application.
     *
     * @var array
     */
    protected array $validator = [
        // Add here your custom validators
    ];
}