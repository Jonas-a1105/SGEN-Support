<?php
/**
 * Router script for PHP built-in development server
 * This allows clean URLs like /auth/login to work
 */

// DEBUG: Log to file that router was called
file_put_contents(__DIR__ . '/router_debug.log', date('Y-m-d H:i:s') . " - Router called for: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the request is for a real file (css, js, images, etc.), let PHP serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    // Get file extension
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    
    // Set proper content type for common file types
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $uri);
        return true;
    }
    
    // Let PHP serve the file directly
    return false;
}

// Route everything else through index.php
$logFile = __DIR__ . '/router_debug.log';
file_put_contents($logFile, date('H:i:s') . " - About to require index.php\n", FILE_APPEND);
file_put_contents($logFile, "  SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'NOT SET') . "\n", FILE_APPEND);
file_put_contents($logFile, "  Contains Dev Server: " . (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Development Server') !== false ? 'YES' : 'NO') . "\n", FILE_APPEND);

try {
    require_once __DIR__ . '/index.php';
    file_put_contents($logFile, date('H:i:s') . " - index.php completed OK\n", FILE_APPEND);
} catch (Throwable $e) {
    file_put_contents($logFile, date('H:i:s') . " - EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, "  File: " . $e->getFile() . "\n", FILE_APPEND);
    file_put_contents($logFile, "  Line: " . $e->getLine() . "\n", FILE_APPEND);
    throw $e; // Re-throw to show error to user
}
