<?php
// Script para vincular empleado al usuario
require_once __DIR__ . '/../config/database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: debug_session.php');
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $usuario_id = $_POST['usuario_id'];
    $empleado_id = $_POST['empleado_id'];
    
    // Actualizar el usuario con el empleado_id
    $stmt = $pdo->prepare("UPDATE usuarios SET empleado_id = ? WHERE id = ?");
    $stmt->execute([$empleado_id, $usuario_id]);
    
    echo "<h2>✓ Vinculación Exitosa</h2>";
    echo "<p>El usuario ha sido vinculado al empleado correctamente.</p>";
    echo "<p><strong>Ahora debes:</strong></p>";
    echo "<ol>";
    echo "<li>Cerrar sesión</li>";
    echo "<li>Volver a iniciar sesión</li>";
    echo "<li>El departamento debería aparecer correctamente</li>";
    echo "</ol>";
    echo "<p><a href='../index.php?url=auth/logout'>Cerrar Sesión Ahora</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
