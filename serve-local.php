<?php
/**
 * Local dev router for `php -S`.
 *
 * This app builds asset URLs as `public/frontEnd/...`, so it must be served with the
 * PROJECT ROOT as the web root (the way it runs in production), NOT with public/ as the
 * web root the way `php artisan serve` does. Run from the project root:
 *
 *   php -S 127.0.0.1:8000 serve-local.php
 *
 * Real files (css/js/images under public/) are served directly; everything else is
 * handed to Laravel via public/index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the static asset as-is
}

require __DIR__ . '/public/index.php';
