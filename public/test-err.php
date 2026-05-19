<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
header('Content-Type: text/plain; charset=utf-8');
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    // Get last 4000 characters
    if (strlen($content) > 8000) {
        $content = substr($content, -8000);
    }
    echo "--- LATEST 100 LINES OF LARAVEL LOG ---\n\n";
    echo $content;
} else {
    echo "Laravel log file not found at: " . realpath($logFile);
}
