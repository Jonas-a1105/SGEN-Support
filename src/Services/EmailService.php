<?php
namespace App\Services;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Servicio de Email para enviar notificaciones del sistema
 * Soporta Gmail, Outlook, Office 365 y otros proveedores SMTP
 */
class EmailService
{
    private $config;
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->config = require __DIR__ . '/../../config/email.php';
        $this->pdo = $pdo;
    }

    /**
     * Enviar notificación de ticket vencido
     */
    public function enviarNotificacionVencimiento($ticket)
    {
        if (!$this->config['enabled']) {
            return ['success' => false, 'error' => 'Email deshabilitado en configuración'];
        }

        $destinatarios = $this->obtenerDestinatarios($ticket);
        
        $asunto = "🔴 TICKET VENCIDO #{$ticket->id}: " . substr($ticket->descripcion, 0, 50);
        
        $mensaje = $this->generarMensajeVencimiento($ticket);
        
        $resultados = [];
        foreach ($destinatarios as $email) {
            $resultados[$email] = $this->enviar($email, $asunto, $mensaje);
        }
        
        return $resultados;
    }

    /**
     * Obtener lista de destinatarios para notificación
     */
    private function obtenerDestinatarios($ticket)
    {
        $destinatarios = [];
        
        // 1. Técnico asignado
        if (!empty($ticket->tecnico_email)) {
            $destinatarios[] = $ticket->tecnico_email;
        }
        
        // 2. Administradores
        if (!empty($this->config['admin_emails'])) {
            $destinatarios = array_merge($destinatarios, $this->config['admin_emails']);
        }
        
        return array_unique($destinatarios);
    }

    /**
     * Generar contenido HTML del mensaje
     */
    private function generarMensajeVencimiento($ticket)
    {
        $vencimiento = date('d/m/Y H:i', strtotime($ticket->fecha_vencimiento));
        $ahora = date('d/m/Y H:i');
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background-color: white; }
                .header { background-color: #dc3545; color: white; padding: 30px 20px; text-align: center; }
                .header h2 { margin: 0; font-size: 24px; }
                .content { padding: 30px 20px; }
                .info-row { margin: 15px 0; padding: 12px; background-color: #f8f9fa; border-left: 3px solid #007bff; }
                .label { font-weight: bold; color: #495057; display: block; margin-bottom: 5px; }
                .value { color: #212529; }
                .footer { text-align: center; padding: 20px; background-color: #f8f9fa; font-size: 0.9em; color: #6c757d; }
                .alert { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                .btn { display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .priority-high { color: #dc3545; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>⚠️ TICKET VENCIDO</h2>
                    <p style='margin: 10px 0 0 0; font-size: 14px;'>Sistema de Soporte SGEN</p>
                </div>
                <div class='content'>
                    <div class='alert'>
                        <strong>⏰ Atención:</strong> Este ticket ha excedido su tiempo de respuesta SLA y requiere atención inmediata.
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>🎫 Ticket ID:</span> 
                        <span class='value'>#" . str_pad($ticket->id, 4, '0', STR_PAD_LEFT) . "</span>
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>⚡ Prioridad:</span> 
                        <span class='value priority-high'>" . strtoupper($ticket->prioridad) . "</span>
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>📝 Descripción:</span> 
                        <span class='value'>" . htmlspecialchars(substr($ticket->descripcion, 0, 150)) . (strlen($ticket->descripcion) > 150 ? '...' : '') . "</span>
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>💻 Equipo:</span> 
                        <span class='value'>" . htmlspecialchars($ticket->equipo_serial ?? 'N/A') . "</span>
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>👤 Técnico Asignado:</span> 
                        <span class='value'>" . htmlspecialchars($ticket->tecnico_asignado ?? 'Sin asignar') . "</span>
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>⏱️ Venció el:</span> 
                        <span class='value'>{$vencimiento}</span>
                    </div>
                    
                    <div class='info-row'>
                        <span class='label'>📅 Fecha Actual:</span> 
                        <span class='value'>{$ahora}</span>
                    </div>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . BASE_URL . "soportes/ver/{$ticket->id}' class='btn'>
                            Ver Ticket Completo
                        </a>
                    </div>
                </div>
                <div class='footer'>
                    <p><strong>Sistema de Soporte SGEN</strong></p>
                    <p>Este es un mensaje automático. Por favor no responda a este correo.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Enviar email usando PHPMailer con soporte para múltiples proveedores
     */
    private function enviar($destinatario, $asunto, $mensaje)
    {
        try {
            $mail = new PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_user'];
            $mail->Password = $this->config['smtp_pass'];
            $mail->SMTPSecure = $this->config['smtp_secure']; // 'tls' o 'ssl'
            $mail->Port = $this->config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Configuración para Gmail específicamente
            if (strpos($this->config['smtp_host'], 'gmail') !== false) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
            }
            
            // Remitente
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // Destinatario
            $mail->addAddress($destinatario);
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->AltBody = strip_tags($mensaje); // Versión texto plano
            
            $mail->send();
            
            return [
                'success' => true,
                'email' => $destinatario
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'email' => $destinatario,
                'error' => $mail->ErrorInfo
            ];
        }
    }
}

