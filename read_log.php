<?php
// Read the last 50 lines of the Laravel log file
$logFile = __DIR__ . '/storage/logs/laravel.log';

if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo "Last 50 lines of laravel.log:\n\n";
    echo implode("", $lastLines);
} else {
    echo "Log file not found at: " . $logFile;
}
