<?php

/**
 * |--------------------------------------------------------------------------
 * | Front Controller
 * |--------------------------------------------------------------------------
 * This file serves as the entry point for all HTTP requests to the application.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

return ( require_once dirname(__DIR__) . '/bootstrap/app.php')
            ->handleRequest();
