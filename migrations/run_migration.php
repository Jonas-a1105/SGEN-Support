<?php
// Usar __DIR__ para asegurar que las rutas sean correctas
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ruta ajustada para buscar el archivo en la misma carpeta que este script
    // TODO: Idealmente esto debería iterar sobre todos los archivos de migración
    $migrationFile = __DIR__ . '/m2025_12_03_add_notificacion_vencimiento.php';
    
    if (file_exists($migrationFile)) {
        $migration = require $migrationFile;
        $migration['up']($pdo);
        echo "Migración ejecutada con éxito.\n";
    } else {
        echo "Error: No se encontró el archivo de migración: $migrationFile\n";
    }
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
