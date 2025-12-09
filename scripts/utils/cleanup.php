<!DOCTYPE html>
<html>
<head>
    <title>Limpieza de Notificaciones</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Limpieza de Notificaciones con Enlaces Rotos</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Conexión a la base de datos
                $host = 'localhost';
                $db   = 'sgen_db';
                $user = 'root';
                $pass = '';
                
                $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Eliminar notificaciones con enlaces rotos
                $sql = "DELETE FROM notificaciones WHERE enlace LIKE '%{%'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $deletedCount = $stmt->rowCount();
                
                echo "<p class='success'>✓ Éxito: Se eliminaron $deletedCount notificaciones con enlaces rotos.</p>";
                echo "<p>Ya puedes <a href='/sgen-support/'>volver al dashboard</a>.</p>";
                
            } catch (PDOException $e) {
                echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            ?>
            <p>Este script eliminará todas las notificaciones que contengan enlaces rotos (con {id} literal).</p>
            <p><strong>¿Deseas continuar?</strong></p>
            <form method="POST">
                <button type="submit">Sí, eliminar notificaciones rotas</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>
