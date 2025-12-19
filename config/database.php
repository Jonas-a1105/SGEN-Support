<?php
// Configuración de Base de Datos Unificada
// Soporta: XAMPP local (puerto 3306), Desktop App (puerto 3307), y producción vía .env

// DEBUG - BEFORE ANYTHING ELSE
$debugLog = __DIR__ . '/../public/db_debug.log';
file_put_contents($debugLog, "\n" . date('H:i:s') . " === database.php START ===\n", FILE_APPEND);
file_put_contents($debugLog, "  getenv(DB_PORT): " . (getenv('DB_PORT') ?: 'NOT SET') . "\n", FILE_APPEND);
file_put_contents($debugLog, "  \$_ENV[DB_PORT]: " . ($_ENV['DB_PORT'] ?? 'NOT SET') . "\n", FILE_APPEND);
file_put_contents($debugLog, "  defined(DB_PORT): " . (defined('DB_PORT') ? 'YES=' . DB_PORT : 'NO') . "\n", FILE_APPEND);
file_put_contents($debugLog, "  SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'NOT SET') . "\n", FILE_APPEND);

// Detect if running as Desktop App using MULTIPLE methods
$isDesktopApp = false;

// Method 1: Check SERVER_SOFTWARE for PHP Development Server
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
if (strpos($serverSoftware, 'Development Server') !== false) {
    $isDesktopApp = true;
}

// Method 2: Check if Electron passed DB_PORT=3307 via environment
$envDbPort = getenv('DB_PORT');
if ($envDbPort === '3307') {
    $isDesktopApp = true;
}

// Method 3: Check SERVER_PORT for non-standard ports (Electron uses random high ports)
$serverPort = $_SERVER['SERVER_PORT'] ?? '';
if ($serverPort !== '' && $serverPort !== '80' && $serverPort !== '443' && (int)$serverPort > 1024) {
    // Only apply if SERVER_SOFTWARE looks like PHP's development server
    if (strpos($serverSoftware, 'PHP') !== false) {
        $isDesktopApp = true;
    }
}

// For desktop app, FORCE these values (using different constant names to avoid conflicts)
if ($isDesktopApp) {
    // For desktop app, we MUST use these values regardless of what .env says
    if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
    if (!defined('DB_PORT')) define('DB_PORT', '3307');
    if (!defined('DB_NAME')) define('DB_NAME', 'sgen_db');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
    
    file_put_contents(__DIR__ . '/../public/db_debug.log', "  After define: DB_PORT=" . DB_PORT . "\n", FILE_APPEND);
} else {
    // Normal web server (XAMPP, Apache, production) - use env values
    // Helper function: check getenv first (for Electron), then $_ENV, then default
    if (!function_exists('env_get')) {
        function env_get($key, $default = '') {
            $val = getenv($key);
            if ($val !== false && $val !== '') return $val;
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
            return $default;
        }
    }
    
    if (!defined('DB_HOST')) define('DB_HOST', env_get('DB_HOST', 'localhost'));
    if (!defined('DB_PORT')) define('DB_PORT', env_get('DB_PORT', '3306'));
    if (!defined('DB_NAME')) define('DB_NAME', env_get('DB_NAME', 'sgen_db'));
    if (!defined('DB_USER')) define('DB_USER', env_get('DB_USER', 'root'));
    if (!defined('DB_PASS')) define('DB_PASS', env_get('DB_PASS', ''));
    if (!defined('DB_CHARSET')) define('DB_CHARSET', env_get('DB_CHARSET', 'utf8mb4'));
}