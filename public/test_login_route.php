<?php
// Test the exact flow that fails - go to / which redirects to /auth/login
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DON'T set Content-Type - let PHP set it naturally to see if HTML works
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Caracas');
ini_set('session.gc_maxlifetime', 28800);
ini_set('session.cookie_lifetime', 28800);
session_start();

require_once __DIR__ . '/../config/config.php';

use App\Core\Router;

// Simulate going to /auth/login directly
$_SERVER['REQUEST_URI'] = '/auth/login';
$_GET['url'] = 'auth/login';
$method = 'GET';

try {
    Router::load(__DIR__ . '/../config/routes.php')
          ->direct('/auth/login', $method);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
