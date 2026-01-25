<?php

/**
 * |----------------------------------------------------------------
 * | Static File Server
 * |----------------------------------------------------------------
 * This script serves static files directly when they exist, bypassing
 * the main application logic for improved performance. 
 * It checks if the requested URI corresponds to a file in the 'public'
 * directory and serves it with the correct MIME type.
 */

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
     * |----------------------------------------------------------------
     * | Serve Static File
     * |----------------------------------------------------------------
     * If the requested URI matches a file in the 'public' directory,
     * serve that file with the appropriate MIME type.
     */
    $file = __DIR__.'/public' . $uri;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    header('Content-Type: ' . @$mime_types[$ext] ?? mime_content_type($file)); // Only content type is needed the remaining headers will be guest by the browser
    include $file;

    // Terminate script execution after serving the file
    exit;
}

require_once __DIR__.'/public/index.php';
