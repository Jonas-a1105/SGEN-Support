<?php
// Test AuthController directly
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== TESTING AUTH CONTROLLER ===\n\n";

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
session_start();
require_once __DIR__ . '/../config/config.php';

echo "DB_PORT: " . DB_PORT . "\n";
echo "DB_HOST: " . DB_HOST . "\n\n";

echo "Testing AuthController constructor...\n";
try {
    $auth = new \App\Controllers\AuthController();
    echo "AuthController created successfully!\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
