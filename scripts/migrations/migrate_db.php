<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database.\n";

    // --- MIGRACIÓN 1: Agregar proveedor_rif a equipos ---
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM equipos LIKE 'proveedor_rif'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE equipos ADD COLUMN proveedor_rif VARCHAR(20) DEFAULT NULL AFTER proveedor");
            echo "Migración completada: Campo 'proveedor_rif' agregado a 'equipos'.\n";
        } else {
            echo "El campo 'proveedor_rif' ya existe en 'equipos'.\n";
        }
    } catch (PDOException $e) {
        echo "Error al agregar 'proveedor_rif': " . $e->getMessage() . "\n";
    }

    // --- MIGRACIÓN 2: Crear tabla mantenimientos ---
    $createMantenimientos = "
        CREATE TABLE IF NOT EXISTS mantenimientos (
          id int(11) NOT NULL AUTO_INCREMENT,
          equipo_id int(11) NOT NULL,
          fecha datetime NOT NULL DEFAULT current_timestamp(),
          descripcion text NOT NULL,
          tipo_mantenimiento enum('preventivo','correctivo','actualizacion','otro') NOT NULL,
          costo decimal(10,2) DEFAULT 0.00,
          realizado_por varchar(150) DEFAULT NULL,
          created_at timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (id),
          KEY equipo_id (equipo_id),
          CONSTRAINT mantenimientos_ibfk_1 FOREIGN KEY (equipo_id) REFERENCES equipos (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    try {
        $pdo->exec($createMantenimientos);
        echo "Tabla 'mantenimientos' creada/verificada exitosamente.\n";
    } catch (PDOException $e) {
        echo "Error al crear 'mantenimientos': " . $e->getMessage() . "\n";
    }

    // --- MIGRACIÓN 3: Agregar departamento_id a empleados ---
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM empleados LIKE 'departamento_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE empleados ADD COLUMN departamento_id INT NULL AFTER cedula");
            $pdo->exec("ALTER TABLE empleados ADD FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL");
            echo "Migración completada: Campo 'departamento_id' agregado a 'empleados'.\n";
        } else {
            echo "El campo 'departamento_id' ya existe en 'empleados'.\n";
        }
    } catch (PDOException $e) {
        echo "Error al agregar 'departamento_id' a empleados: " . $e->getMessage() . "\n";
    }

    // --- MIGRACIÓN 4: Agregar estado 'en_espera' a soportes ---
    try {
        // Verificar el ENUM actual
        $stmt = $pdo->query("SHOW COLUMNS FROM soportes LIKE 'estado'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($column && strpos($column['Type'], 'en_espera') === false) {
            $pdo->exec("ALTER TABLE soportes MODIFY COLUMN estado ENUM('pendiente','en_proceso','en_espera','resuelto') NOT NULL DEFAULT 'pendiente'");
            echo "Migración completada: Estado 'en_espera' agregado a 'soportes'.\n";
        } else {
            echo "El estado 'en_espera' ya existe en 'soportes'.\n";
        }
    } catch (PDOException $e) {
        echo "Error al agregar estado 'en_espera': " . $e->getMessage() . "\n";
    }

    echo "\n=== Migraciones finalizadas ===\n";

    // --- MIGRACIÓN 5: Agregar empleado_id y departamento_id a usuarios ---
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'empleado_id'");
        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE usuarios
                    ADD COLUMN empleado_id INT NULL,
                    ADD COLUMN departamento_id INT NULL,
                    ADD CONSTRAINT fk_usuario_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE SET NULL,
                    ADD CONSTRAINT fk_usuario_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL";
            $pdo->exec($sql);
            echo "Migración completada: columnas 'empleado_id' y 'departamento_id' añadidas a 'usuarios'.\n";
        } else {
            echo "Las columnas 'empleado_id' y 'departamento_id' ya existen en 'usuarios'.\n";
        }
    } catch (PDOException $e) {
        echo "Error al agregar columnas a usuarios: " . $e->getMessage() . "\n";
    }

    // --- MIGRACIÓN 6: Agregar usuario_creacion_id a soportes ---
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM soportes LIKE 'usuario_creacion_id'");
        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE soportes ADD COLUMN usuario_creacion_id INT NULL AFTER empleado_id";
            $pdo->exec($sql);

            $sql = "ALTER TABLE soportes ADD CONSTRAINT fk_soportes_usuario_creacion 
                    FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id) ON DELETE SET NULL";
            $pdo->exec($sql);
            echo "Migración completada: 'usuario_creacion_id' agregado a 'soportes'.\n";
        } else {
            echo "El campo 'usuario_creacion_id' ya existe en 'soportes'.\n";
        }
    } catch (PDOException $e) {
        echo "Error al agregar 'usuario_creacion_id' a soportes: " . $e->getMessage() . "\n";
    }

    // --- MIGRACIÓN 7: Inventario Distribuido ---
    require_once __DIR__ . '/migrations/m2025_11_23_distributed_inventory.php';


} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
