<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../config/config.php';

echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_PORT: " . DB_PORT . "\n";
echo "SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "CONNECTION: SUCCESS\n";
} catch (Exception $e) {
    echo "CONNECTION: FAILED - " . $e->getMessage() . "\n";
}
