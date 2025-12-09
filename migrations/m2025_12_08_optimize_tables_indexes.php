<?php
/**
 * Optimization Migration - Final
 * Adds indexes to frequently searched/filtered columns to improve query performance.
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

require_once __DIR__ . '/../config/database.php';

try {
    echo "Iniciando conexión a base de datos...\n";
    $dsn = "mysql:host=" . (defined('DB_HOST') ? DB_HOST : 'localhost') . ";dbname=" . (defined('DB_NAME') ? DB_NAME : 'sgen_db') . ";charset=" . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';
    
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión exitosa. Iniciando optimización...\n";

    $indexes = [
        // Soportes (Tickets)
        ['table' => 'soportes', 'column' => 'estado', 'name' => 'idx_soportes_estado'],
        ['table' => 'soportes', 'column' => 'empleado_id', 'name' => 'idx_soportes_empleado'],
        ['table' => 'soportes', 'column' => 'usuario_creacion_id', 'name' => 'idx_soportes_creador'],
        ['table' => 'soportes', 'column' => 'equipo_id', 'name' => 'idx_soportes_equipo'],
        ['table' => 'soportes', 'column' => 'prioridad', 'name' => 'idx_soportes_prioridad'],
        // created_at might be missing in older schemas, try 'fecha'
        ['table' => 'soportes', 'column' => 'fecha', 'name' => 'idx_soportes_fecha'],
        
        // Equipos (Hardware)
        ['table' => 'equipos', 'column' => 'numero_serie', 'name' => 'idx_equipos_serial'], 
        ['table' => 'equipos', 'column' => 'codigo_inventario', 'name' => 'idx_equipos_codigo'], 
        ['table' => 'equipos', 'column' => 'tipo', 'name' => 'idx_equipos_tipo'], 
        
        // Empleados
        ['table' => 'empleados', 'column' => 'documento', 'name' => 'idx_empleados_doc'], 
        ['table' => 'empleados', 'column' => 'correo', 'name' => 'idx_empleados_correo'], 
    ];

    foreach ($indexes as $idx) {
        $table = $idx['table'];
        $column = $idx['column'];
        $indexName = $idx['name'];

        // Check if index exists
        $stmt = $pdo->prepare("SHOW INDEX FROM `$table` WHERE Key_name = ?");
        $stmt->execute([$indexName]);
        
        if ($stmt->rowCount() == 0) {
            echo "Agregando índice '$indexName' a la tabla '$table'...\n";
            try {
                $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`$column`)";
                $pdo->exec($sql);
                echo "✔ Índice agregado.\n";
            } catch (PDOException $e) {
                echo "⚠ Error agregando índice '$indexName' en tabla '$table': " . $e->getMessage() . "\n";
            }
        } else {
            echo "ℹ Índice '$indexName' ya existe en '$table'.\n";
        }
    }

    echo "Optimización completada con éxito.\n";

} catch (PDOException $e) {
    die("Error durante la optimización: " . $e->getMessage() . "\n");
}
