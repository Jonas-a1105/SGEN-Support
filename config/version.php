<?php
/**
 * App Version Configuration
 * Centralized version management - reads from environment or defaults
 * 
 * In Electron: Reads from APP_VERSION env set by main.js (from package.json)
 * In XAMPP: Reads from package.json directly or uses fallback
 */

// Try to get version from environment (set by Electron's main.js)
$envVersion = getenv('APP_VERSION');
if ($envVersion !== false && $envVersion !== '') {
    define('APP_VERSION', $envVersion);
} else {
    // Fallback: Try to read from package.json
    $packageJsonPaths = [
        __DIR__ . '/../desktop/package.json',  // Development
        __DIR__ . '/../package.json',           // Alternative location
    ];
    
    $version = '1.0.0'; // Default fallback
    
    foreach ($packageJsonPaths as $path) {
        if (file_exists($path)) {
            $packageData = json_decode(file_get_contents($path), true);
            if (isset($packageData['version'])) {
                $version = $packageData['version'];
                break;
            }
        }
    }
    
    define('APP_VERSION', $version);
}
