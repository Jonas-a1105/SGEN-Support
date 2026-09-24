<?php
/**
 * SGEN-Support: Import Seeds into Database
 * 
 * This script imports seed data from seeds.php into a fresh database.
 * It's designed to be called during initial setup.
 * 
 * Usage: php scripts/import_seeds.php
 */

require_once __DIR__ . '/../config/database.php';

echo "====================================================\n";
echo "   SGEN-Support - Import Seeds to Database\n";
echo "====================================================\n\n";

function importSeeds(PDO $pdo): bool {
    // Find seeds file
    $seedsPaths = [
        __DIR__ . '/../database/seeds.php',
        dirname(__DIR__) . '/database/seeds.php',
    ];
    
    $resourcesPath = getenv('RESOURCES_PATH') ?: ($_ENV['RESOURCES_PATH'] ?? null);
    if ($resourcesPath) {
        array_unshift($seedsPaths, $resourcesPath . '/database/seeds.php');
    }
    
    $seedsFile = null;
    foreach ($seedsPaths as $path) {
        if (file_exists($path)) {
            $seedsFile = $path;
            break;
        }
    }
    
    if (!$seedsFile) {
        echo "   ⚠️ No se encontró archivo seeds.php\n";
        return false;
    }
    
    echo "📄 Usando: $seedsFile\n\n";
    
    $seeds = require $seedsFile;
    
    if (!is_array($seeds)) {
        echo "   ❌ El archivo seeds.php no retorna un array válido.\n";
        return false;
    }
    
    foreach ($seeds as $table => $rows) {
        if (empty($rows)) continue;
        
        echo "📦 Importing table: $table...\n";
        
        // Check if table already has data
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            if ($count > 0) {
                echo "   ⚪ Tabla ya tiene $count registros, omitiendo.\n";
                continue;
            }
        } catch (Exception $e) {
            echo "   ⚠️ Tabla no existe, omitiendo.\n";
            continue;
        }
        
        $imported = 0;
        foreach ($rows as $row) {
            try {
                $columns = array_keys($row);
                $placeholders = array_fill(0, count($columns), '?');
                
                $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($row));
                $imported++;
            } catch (Exception $e) {
                // Ignore duplicates and constraint errors
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    // Log but continue
                }
            }
        }
        
        echo "   ✅ $imported registros importados.\n";
    }
    
    return true;
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "✅ Conectado a la base de datos.\n\n";
    
    importSeeds($pdo);
    
    echo "\n====================================================\n";
    echo "✅ Importación completada.\n";
    echo "====================================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
