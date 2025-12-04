<?php
// migrations/m2025_11_22_add_role_refs.php
// Ejecutar con migrate_db.php

$pdo = new PDO('mysql:host=localhost;dbname=sgen_db;charset=utf8mb4', 'root', '');

$sql = "
ALTER TABLE usuarios
  ADD COLUMN empleado_id INT NULL,
  ADD COLUMN departamento_id INT NULL,
  ADD CONSTRAINT fk_usuario_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_usuario_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL;
";

$pdo->exec($sql);

echo "Migración completada: columnas añadidas a usuarios.\n";
?>
