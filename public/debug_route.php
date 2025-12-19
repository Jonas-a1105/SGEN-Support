<?php
// Simulate EXACTLY what index.php does with full routing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Load Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// 3. Config (basic, no session)
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// 4. Timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Caracas');

// 5. Start session
session_start();

// 6. Load config
require_once __DIR__ . '/../config/config.php';

// 7. Load and execute router
use App\Core\Router;

$uri = '/auth/login'; // Simulate going to login
$method = 'GET';

try {
    Router::load(__DIR__ . '/../config/routes.php')
          ->direct($uri, $method);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
