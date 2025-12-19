<?php
// Trace EXACTLY what happens in the real index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== TRACING INDEX.PHP EXECUTION ===\n\n";

// Step 1
echo "1. Loading autoloader... ";
require_once __DIR__ . '/../vendor/autoload.php';
echo "OK\n";

// Step 2
echo "2. Loading Dotenv... ";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
echo "OK\n";

// Step 3
echo "3. Configuring debug mode... ";
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
echo "OK\n";

// Step 4  
echo "4. Setting timezone... ";
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Caracas');
echo "OK\n";

// Step 5
echo "5. Starting session... ";
session_start();
echo "OK (session_id: " . session_id() . ")\n";

// Step 6
echo "6. Loading config.php... ";
require_once __DIR__ . '/../config/config.php';
echo "OK (DB_PORT=" . DB_PORT . ")\n";

// Step 7 - Test DB before Router
echo "7. Testing DB before Router... ";
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT, DB_USER, DB_PASS);
    echo "OK\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit;
}

// Step 8 - Load routes
echo "8. Loading routes.php... ";
use App\Core\Router;
$router = Router::load(__DIR__ . '/../config/routes.php');
echo "OK\n";

// Step 9 - Test HomeController directly
echo "9. Testing HomeController directly... ";
try {
    $controller = new \App\Controllers\HomeController();
    echo "OK\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n=== TRACE COMPLETE ===\n";
