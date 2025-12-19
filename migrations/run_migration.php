<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

require_once __DIR__ . '/../config/database.php';

try {
    // FIRST: Connect without database to create it if needed
    $dsnNoDB = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $pdoInit = new PDO($dsnNoDB, DB_USER, DB_PASS);
    $pdoInit->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "🔌 Base de datos verificada/creada.\n";
    $pdoInit = null; // Close initial connection
    
    // NOW connect to the database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔌 Conectado a la base de datos.\n";
    
    // Obtener todos los archivos PHP en la carpeta migrations
    $files = glob(__DIR__ . '/m*.php');
    sort($files); // Asegurar orden cronológico
    
    $executed = 0;
    $skipped = 0;

    echo "🔍 Buscando migraciones...\n\n";
    
    foreach ($files as $file) {
        $filename = basename($file);
        
        // Aislar el require para evitar contaminación de variables global/local si es posible
        // Pero para "legacy" scripts que ejecutan al incluirse, no podemos incluirlos aquí sin ejecutarlos.
        // Solución: Leer el contenido del archivo para detectar si devuelve array
        
        $content = file_get_contents($file);
        
        // Detección simple: busca "return [" o "return array("
        if (preg_match('/return\s*\[|return\s*array\(/i', $content)) {
            // Es estilo Moderno (array)
            try {
                $migration = require $file;
                
                if (is_array($migration) && isset($migration['up']) && is_callable($migration['up'])) {
                    echo "🚀 Ejecutando: $filename\n";
                    // Ejecutar la migración
                    // Nota: Idealmente deberíamos chequear si ya se corrió en una tabla 'migrations_log'
                    // Pero por ahora confiamos en que los scripts modernos tienen chequeos internos (IF NOT EXISTS)
                    $migration['up']($pdo);
                    $executed++;
                }
            } catch (Exception $e) {
                echo "❌ Error en $filename: " . $e->getMessage() . "\n";
            }
        } else {
            // Es estilo Legacy (Ejecución directa o texto plano)
            // NO lo ejecutamos automáticamente para evitar duplicaciones o errores fatales en scripts viejos sin guardas.
            echo "⚠️  Saltando legacy/manual: $filename\n";
            $skipped++;
        }
    }
    
    echo "\n🏁 Resumen:\n";
    echo "   - Ejecutados: $executed\n";
    echo "   - Saltados (Legacy): $skipped\n";
    
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}
