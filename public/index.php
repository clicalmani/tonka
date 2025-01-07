<?php
/**
 * Load composer autoloader
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

return ( require_once dirname(__DIR__) . '/bootstrap/app.php')
            ->handleRequest();
