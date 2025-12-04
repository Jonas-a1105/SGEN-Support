<?php
// ---- INICIO DE CÓDIGO DE DEPURACIÓN ----
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---- FIN DE CÓDIGO DE DEPURACIÓN ----

// Configurar zona horaria (Venezuela/Caracas = UTC-4)
date_default_timezone_set('America/Caracas');

// Iniciar sesión
session_start();

// Archivo de configuración principal (para BASE_URL, etc.)
require_once __DIR__ . '/../config/config.php';

// Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

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