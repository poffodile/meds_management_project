<?php
/**
 * Local dev router for `php -S`.
 *
 * This app builds (legacy) asset URLs as `public/frontEnd/...`, so it must be served
 * with the PROJECT ROOT as the web root (the way it runs in production), NOT with
 * public/ as the web root the way `php artisan serve` does. Run from the project root:
 *
 *   php -S 127.0.0.1:8000 serve-local.php
 *
 * Real files (css/js/images under public/) are served directly; everything else is
 * handed to Laravel via public/index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1) Real file at the project root (legacy assets referenced as `public/frontEnd/...`).
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false; // let the built-in server serve the static asset as-is
}

// 2) Real file under public/ — the Vite build emits asset URLs as `/build/...` (it
//    assumes public/ is the web root). Since we serve from the project root, map those
//    to `public/build/...` and stream them with a sensible content-type. Without this,
//    `@vite` script/style tags 404 and Inertia pages render blank.
$publicFile = __DIR__ . '/public' . $path;
if ($path !== '/' && strpos($path, '..') === false && is_file($publicFile)) {
    $mimes = [
        'js' => 'application/javascript', 'mjs' => 'application/javascript',
        'css' => 'text/css', 'json' => 'application/json', 'map' => 'application/json',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'webp' => 'image/webp',
        'ico' => 'image/x-icon', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
        'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject',
    ];
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    header('Content-Length: ' . filesize($publicFile));
    header('Cache-Control: public, max-age=31536000');
    readfile($publicFile);

    return true; // handled
}

require __DIR__ . '/public/index.php';
