<?php
// Debug exactly what index.php sees
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== DEBUG FROM ROUTER ===\n\n";

echo "SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'NOT SET') . "\n";
echo "Contains 'Development Server': ";
echo (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Development Server') !== false) ? "YES" : "NO";
echo "\n\n";

// Load config exactly like index.php does
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../config/config.php';

echo "After config load:\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_PORT: " . DB_PORT . "\n";

// Now test connection
try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "\nCONNECTION: SUCCESS\n";
} catch (Exception $e) {
    echo "\nCONNECTION: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
