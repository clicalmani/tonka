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
    'svg' => 'image/svg+xml', 
    'xhtml' => 'application/xhtml+xml',
];

 $url = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

 $file = __DIR__ . '/public' . $url;

if ($url !== '/' && is_file($file)) {
    
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mime = $mime_types[$ext] ?? 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    
    readfile($file);
    
    exit;
}

require_once __DIR__ . '/public/index.php';