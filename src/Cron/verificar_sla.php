<?php
/**
 * Script CRON: Verificar tickets vencidos y enviar notificaciones
 * 
 * INSTALACIÓN:
 * 1. En Windows (Task Scheduler):
 *    - Programa: C:\xampp\php\php.exe
 *    - Argumentos: C:\xampp\htdocs\sgen-support\src\Cron\verificar_sla.php
 *    - Frecuencia: Cada 1 hora
 * 
 * 2. En Linux (crontab -e):
 *    0 * * * * /usr/bin/php /path/to/sgen-support/src/Cron/verificar_sla.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Services/EmailService.php';

use App\Services\EmailService;

try {
    // Conectar a la base de datos
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando verificación de SLA...\n";
    
    // Buscar tickets vencidos que no han sido notificados
    $sql = "SELECT s.*, 
                   e.serial as equipo_serial,
                   u.username as tecnico_asignado,
                   u.email as tecnico_email
            FROM soportes s
            LEFT JOIN equipos e ON s.equipo_id = e.id
            LEFT JOIN usuarios u ON s.tecnico_asignado_id = u.id
            WHERE s.estado != 'resuelto'
              AND s.fecha_vencimiento IS NOT NULL
              AND s.fecha_vencimiento < NOW()
              AND (s.notificacion_vencimiento_enviada = 0 OR s.notificacion_vencimiento_enviada IS NULL)
            ORDER BY s.prioridad DESC, s.fecha_vencimiento ASC";
    
    $stmt = $pdo->query($sql);
    $ticketsVencidos = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($ticketsVencidos)) {
        echo "✓ No hay tickets vencidos pendientes de notificación.\n";
        exit(0);
    }
    
    echo "⚠️  Encontrados " . count($ticketsVencidos) . " tickets vencidos.\n\n";
    
    // Inicializar servicio de email
    $emailService = new EmailService($pdo);
    
    $enviados = 0;
    $fallidos = 0;
    
    foreach ($ticketsVencidos as $ticket) {
        echo "Procesando Ticket #{$ticket->id} (Prioridad: {$ticket->prioridad})...\n";
        
        // Enviar notificación
        $resultados = $emailService->enviarNotificacionVencimiento($ticket);
        
        $exitoso = false;
        foreach ($resultados as $email => $resultado) {
            if ($resultado['success']) {
                echo "  ✓ Email enviado a: {$email}\n";
                $exitoso = true;
            } else {
                echo "  ✗ Error al enviar a {$email}: " . ($resultado['error'] ?? 'Error desconocido') . "\n";
            }
        }
        
        if ($exitoso) {
            // Marcar como notificado
            $updateSql = "UPDATE soportes 
                          SET notificacion_vencimiento_enviada = 1,
                              notificacion_vencimiento_fecha = NOW()
                          WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$ticket->id]);
            
            $enviados++;
        } else {
            $fallidos++;
        }
        
        echo "\n";
    }
    
    echo "========================================\n";
    echo "Resumen:\n";
    echo "  Total procesados: " . count($ticketsVencidos) . "\n";
    echo "  Enviados exitosamente: {$enviados}\n";
    echo "  Fallidos: {$fallidos}\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "ERROR DE BASE DE DATOS: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Verificación completada.\n";
