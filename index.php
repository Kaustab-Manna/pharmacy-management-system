<?php
/**
 * Root Router / Proxy for environments pointing document root to project base directory
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Serve static assets directly if they exist in public/
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false; // let PHP built-in server or web server serve it
}

require_once __DIR__ . '/public/index.php';
