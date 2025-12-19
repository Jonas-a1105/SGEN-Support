<?php
// Exact replica of index.php with debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "=== DEBUG INDEX.PHP FLOW ===\n\n";

try {
    // 1. Load autoloader
    echo "1. Loading autoloader...\n";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "   OK\n";
    
    // 2. Load Dotenv
    echo "2. Loading Dotenv...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
    echo "   OK\n";
    
    // 3. Start session
    echo "3. Starting session...\n";
    ini_set('session.gc_maxlifetime', 28800);
    ini_set('session.cookie_lifetime', 28800);
    session_start();
    echo "   OK (session_id: " . session_id() . ")\n";
    
    // 4. Load config
    echo "4. Loading config.php...\n";
    require_once __DIR__ . '/../config/config.php';
    echo "   OK (DB_HOST=" . DB_HOST . ", DB_PORT=" . DB_PORT . ")\n";
    
    // 5. Test DB directly
    echo "5. Testing DB connection directly...\n";
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "   OK - Direct PDO connection works\n";
    
    // 6. Test Database class
    echo "6. Testing Database::getInstance()...\n";
    $db = \App\Core\Database::getInstance();
    echo "   OK - Database class works\n";
    
    // 7. Test Model
    echo "7. Testing Model (Usuario)...\n";
    $usuario = new \App\Models\Usuario();
    echo "   OK - Usuario model created\n";
    
    // 8. Test Router
    echo "8. Testing Router load...\n";
    $router = \App\Core\Router::load(__DIR__ . '/../config/routes.php');
    echo "   OK - Router loaded\n";
    
    echo "\n=== ALL TESTS PASSED ===\n";
    
} catch (Exception $e) {
    echo "\n!!! EXCEPTION !!!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}
