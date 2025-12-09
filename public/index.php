<?php
// 1. Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Cargar variables de entorno (.env)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// 3. Configuración de Depuración (basado en .env)
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// 4. Configurar zona horaria
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Caracas');

// 5. Iniciar Sesión (CRÍTICO: Debe ir antes de cualquier salida)
// Configurar duración de sesión (8 horas = 28800 segundos)
ini_set('session.gc_maxlifetime', 28800);
ini_set('session.cookie_lifetime', 28800);

session_start();

// Archivo de configuración principal (ahora puede usar $_ENV)
require_once __DIR__ . '/../config/config.php';

use App\Core\Router;

// Obtener la URL solicitada (usamos REQUEST_URI para obtener la ruta completa)
$uri = $_SERVER['REQUEST_URI'];
// Obtener el método (GET, POST)
$method = $_SERVER['REQUEST_METHOD'];

// Cargar las rutas y dirigir la solicitud
try {
    Router::load(__DIR__ . '/../config/routes.php')
          ->direct($uri, $method);
} catch (Exception $e) {
    // En producción, esto debería ir a un log
    echo "Error: " . $e->getMessage();
}