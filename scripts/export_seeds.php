<?php
/**
 * SGEN-Support: Export Database to Clean Seeds File
 * 
 * This script exports the current database to a clean PHP seeds file
 * that can be included in updates for new installations.
 * 
 * Usage: php scripts/export_seeds.php
 */

require_once __DIR__ . '/../config/database.php';

echo "====================================================\n";
echo "   SGEN-Support - Database Export to Seeds\n";
echo "====================================================\n\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "✅ Conectado a la base de datos.\n\n";
    
    // Tables to export (in order of dependencies)
    $tables = [
        'departamentos',
        'usuarios', 
        'empleados',
        'equipos',
        'categorias',
        'soportes',
        'ticket_comentarios',
        'ticket_archivos',
        'mantenimientos',
        'inventario_items',
        'inventario_movimientos',
        'inventario_ubicaciones',
        'inventario_consumos',
        'bajas_inventario',
        'notificaciones',
        'sesiones_log',
        'bitacora_acciones'
    ];
    
    $outputPath = __DIR__ . '/../database/seeds.php';
    $output = "<?php\n";
    $output .= "/**\n";
    $output .= " * SGEN-Support Database Seeds\n";
    $output .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= " * \n";
    $output .= " * This file contains seed data for new installations.\n";
    $output .= " */\n\n";
    $output .= "return [\n";
    
    foreach ($tables as $table) {
        echo "📦 Exporting table: $table...\n";
        
        try {
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $output .= "    '$table' => [\n";
                foreach ($rows as $row) {
                    $output .= "        " . var_export($row, true) . ",\n";
                }
                $output .= "    ],\n\n";
                echo "   ✅ " . count($rows) . " registros exportados.\n";
            } else {
                echo "   ⚪ Tabla vacía, omitiendo.\n";
            }
        } catch (Exception $e) {
            echo "   ⚠️ Error: " . $e->getMessage() . "\n";
        }
    }
    
    $output .= "];\n";
    
    file_put_contents($outputPath, $output);
    
    echo "\n====================================================\n";
    echo "✅ Seeds exportados a: $outputPath\n";
    echo "====================================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
