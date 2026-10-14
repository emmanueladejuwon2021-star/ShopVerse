<?php
/**
 * ShopVerse — Local development router for PHP built-in web server:
 * php -S localhost:8000 router.php
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets directly if they exist in public/
if ($path !== '/' && is_file(__DIR__ . '/public' . $path)) {
    return false;
}

// Route all dynamic requests to the front controller
require __DIR__ . '/api/index.php';
