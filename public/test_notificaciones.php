<!DOCTYPE html>
<html>
<head>
    <title>Test de Notificaciones</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 5px; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔔 Test de Notificaciones</h1>
        
        <?php
        session_start();
        
        // Conexión a la base de datos
        $host = 'localhost';
        $db   = 'sgen_db';
        $user = 'root';
        $pass = '';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear notificación de prueba
            if (isset($_POST['crear_notif']) && isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $mensaje = "Esta es una notificación de prueba";
                $enlace = "/";
                
                $sql = "INSERT INTO notificaciones (usuario_id, mensaje, enlace) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $mensaje, $enlace]);
                
                echo "<p class='success'>✓ Notificación de prueba creada correctamente</p>";
            }
            
            // Mostrar notificaciones actuales
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                echo "<p class='info'>Usuario actual: {$_SESSION['username']} (ID: $userId)</p>";
                
                // Contar no leídas
                $sql = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leido = 0";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId]);
                $unread = $stmt->fetchColumn();
                
                echo "<p><strong>Notificaciones no leídas:</strong> $unread</p>";
                
                // Mostrar todas las notificaciones
                $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 10";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId]);
                $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($notifs) > 0) {
                    echo "<h3>Notificaciones recientes:</h3>";
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Mensaje</th><th>Enlace</th><th>Leída</th><th>Fecha</th></tr>";
                    foreach ($notifs as $n) {
                        $leida = $n['leido'] ? '✓ Sí' : '✗ No';
                        echo "<tr>";
                        echo "<td>{$n['id']}</td>";
                        echo "<td>" . htmlspecialchars($n['mensaje']) . "</td>";
                        echo "<td>" . htmlspecialchars($n['enlace']) . "</td>";
                        echo "<td>$leida</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($n['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No tienes notificaciones.</p>";
                }
                
                echo '<form method="POST">
                        <button type="submit" name="crear_notif">Crear notificación de prueba</button>
                      </form>';
                
            } else {
                echo "<p class='error'>No has iniciado sesión. <a href='/sgen-support/auth/login'>Ir a login</a></p>";
            }
            
            echo '<p><a href="/sgen-support/">← Volver al dashboard</a></p>';
            
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
</body>
</html>
