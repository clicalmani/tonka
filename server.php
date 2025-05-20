<?php
$mime_types = [
    'css' => 'text/css',
    'gif' => 'image/gif',
    'htm' => 'text/html',
    'html' => 'text/html',
    'ico' => 'image/vnd.microsoft.icon',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'js' => 'text/javascript',
    'json' => 'application/json',
    'mjs' => 'text/javascript',
    'png' => 'image/png',
    'pdf' => 'application/pdf',
    'xhtml' => 'application/xhtml+xml',
];

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {

    /**
     * This line has been tested and work on development
     * Not tested on production. May be it should be commented on production
     * because there is a try and catch which did the same thing in public/index.php. 
     * If there is no conflict we can keep it.
     */
    $file = __DIR__.'/public' . $uri;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    header('Content-Type: ' . @$mime_types[$ext] ?? mime_content_type($file)); // Only content type is needed the remaining headers will be guest by the browser
    include $file;

    // We should exit to end the file transfer process
    exit;
}

require_once __DIR__.'/public/index.php';
