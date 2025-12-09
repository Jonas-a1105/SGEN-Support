<?php
/**
 * Configuración de Email para notificaciones del sistema
 * Lee las credenciales desde el archivo .env para mayor seguridad.
 */

return [
    // =============================
    // CONFIGURACIÓN SMTP
    // =============================
    
    'smtp_host'   => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'smtp_port'   => $_ENV['MAIL_PORT'] ?? 587,
    'smtp_user'   => $_ENV['MAIL_USER'] ?? 'tu-email@gmail.com',
    'smtp_pass'   => $_ENV['MAIL_PASS'] ?? '', // Contraseña de aplicación
    'smtp_secure' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    
    // =============================
    // REMITENTE
    // =============================
    
    'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_USER'] ?? 'soporte@sgen.com',
    'from_name'  => $_ENV['MAIL_FROM_NAME'] ?? 'Sistema de Soporte SGEN',
    
    // =============================
    // DESTINATARIOS ADMINISTRATIVOS
    // =============================
    
    'admin_emails' => [
        // Puedes agregar emails fijos aquí, o cargarlos desde .env separados por coma si quisieras
        'admin@empresa.com' 
    ],
    
    // =============================
    // ACTIVACIÓN
    // =============================
    
    // Solo habilitar si tenemos usuario y contraseña configurados
    'enabled' => !empty($_ENV['MAIL_USER']) && !empty($_ENV['MAIL_PASS']),
];
