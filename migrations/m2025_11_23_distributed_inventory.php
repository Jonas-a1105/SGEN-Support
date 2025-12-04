<?php
// migrations/m2025_11_23_distributed_inventory.php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Iniciando migración de Inventario Distribuido...\n";

    // 1. Crear tabla inventario_ubicaciones
    $sqlUbicaciones = "
        CREATE TABLE IF NOT EXISTS inventario_ubicaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_id INT NOT NULL,
            departamento_id INT NULL, -- NULL representa Almacén Central
            cantidad INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES inventario_items(id) ON DELETE CASCADE,
            FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
            UNIQUE KEY unique_stock (item_id, departamento_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $pdo->exec($sqlUbicaciones);
    echo "Tabla 'inventario_ubicaciones' creada.\n";

    // 2. Crear tabla inventario_consumos
    $sqlConsumos = "
        CREATE TABLE IF NOT EXISTS inventario_consumos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            soporte_id INT NOT NULL,
            item_id INT NOT NULL,
            cantidad INT NOT NULL,
            usuario_id INT NOT NULL,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (soporte_id) REFERENCES soportes(id) ON DELETE CASCADE,
            FOREIGN KEY (item_id) REFERENCES inventario_items(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $pdo->exec($sqlConsumos);
    echo "Tabla 'inventario_consumos' creada.\n";

    // 3. Actualizar tabla inventario_movimientos
    // Añadir columnas si no existen
    $columns = $pdo->query("SHOW COLUMNS FROM inventario_movimientos")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('origen_departamento_id', $columns)) {
        $pdo->exec("ALTER TABLE inventario_movimientos ADD COLUMN origen_departamento_id INT NULL AFTER usuario_id");
        $pdo->exec("ALTER TABLE inventario_movimientos ADD CONSTRAINT fk_mov_origen FOREIGN KEY (origen_departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL");
        echo "Columna 'origen_departamento_id' agregada.\n";
    }

    if (!in_array('destino_departamento_id', $columns)) {
        $pdo->exec("ALTER TABLE inventario_movimientos ADD COLUMN destino_departamento_id INT NULL AFTER origen_departamento_id");
        $pdo->exec("ALTER TABLE inventario_movimientos ADD CONSTRAINT fk_mov_destino FOREIGN KEY (destino_departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL");
        echo "Columna 'destino_departamento_id' agregada.\n";
    }

    if (!in_array('referencia_id', $columns)) {
        $pdo->exec("ALTER TABLE inventario_movimientos ADD COLUMN referencia_id INT NULL COMMENT 'ID de Ticket o Mantenimiento' AFTER motivo");
        echo "Columna 'referencia_id' agregada.\n";
    }

    // 4. Migrar datos existentes: Mover stock_actual a inventario_ubicaciones (Almacén Central)
    // Solo si la tabla ubicaciones está vacía para evitar duplicados
    $count = $pdo->query("SELECT COUNT(*) FROM inventario_ubicaciones")->fetchColumn();
    if ($count == 0) {
        $sqlMigrate = "
            INSERT INTO inventario_ubicaciones (item_id, departamento_id, cantidad)
            SELECT id, NULL, stock_actual FROM inventario_items WHERE stock_actual > 0
        ";
        $pdo->exec($sqlMigrate);
        echo "Datos migrados: Stock actual movido a Almacén Central (departamento_id = NULL).\n";
    } else {
        echo "Migración de datos omitida: La tabla 'inventario_ubicaciones' ya tiene datos.\n";
    }

    echo "Migración de Inventario Distribuido completada exitosamente.\n";

} catch (PDOException $e) {
    die("Error en migración: " . $e->getMessage() . "\n");
}
