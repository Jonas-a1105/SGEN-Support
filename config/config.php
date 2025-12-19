<?php
// Configuración Principal del Sistema

// 1. Configuración de la URL BASE
// Detect if running as Desktop App first
$isDesktopServer = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Development Server') !== false);

if ($isDesktopServer) {
    // For Electron/PHP Dev Server: use simple URL with dynamic port
    $protocol = "http";
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
    define('BASE_URL', $protocol . '://' . $host . '/');
} elseif (isset($_ENV['APP_URL']) && !empty($_ENV['APP_URL'])) {
    // Production: use APP_URL from .env
    define('BASE_URL', rtrim($_ENV['APP_URL'], '/') . '/');
} else {
    // XAMPP/Apache: auto-detect with path
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . '://' . $host . $path . '/');
}

// 2. Otras configuraciones globales pueden ir aquí...
// La configuración de BD se ha movido a config/database.php para ser reutilizable por scripts CLI.
require_once __DIR__ . '/database.php';

// 3. App Version - Centralized from package.json
require_once __DIR__ . '/version.php';