<?php
// Configuración Principal del Sistema

// 1. Configuración de la URL BASE
// Prioridad: 1. Variable de entorno APP_URL, 2. Detección automática
if (isset($_ENV['APP_URL']) && !empty($_ENV['APP_URL'])) {
    define('BASE_URL', rtrim($_ENV['APP_URL'], '/') . '/');
} else {
    // Detección automática (Fallback)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . '://' . $host . $path . '/');
}

// 2. Otras configuraciones globales pueden ir aquí...
// La configuración de BD se ha movido a config/database.php para ser reutilizable por scripts CLI.
require_once __DIR__ . '/database.php';