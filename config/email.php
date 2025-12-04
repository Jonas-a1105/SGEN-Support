<?php
/**
 * Configuración de Email para notificaciones del sistema
 * 
 * ================================
 * CONFIGURACIONES PREDEFINIDAS
 * ================================
 * 
 * 📧 GMAIL:
 *    smtp_host: 'smtp.gmail.com'
 *    smtp_port: 587
 *    smtp_secure: 'tls'
 *    smtp_user: 'tu-email@gmail.com'
 *    smtp_pass: 'contraseña de aplicación' (generar en: https://myaccount.google.com/apppasswords)
 * 
 * 📧 OUTLOOK / HOTMAIL:
 *    smtp_host: 'smtp-mail.outlook.com'
 *    smtp_port: 587
 *    smtp_secure: 'tls'
 *    smtp_user: 'tu-email@outlook.com'
 *    smtp_pass: 'tu-contraseña'
 * 
 * 📧 OFFICE 365:
 *    smtp_host: 'smtp.office365.com'
 *    smtp_port: 587
 *    smtp_secure: 'tls'
 *    smtp_user: 'tu-email@empresa.com'
 *    smtp_pass: 'tu-contraseña'
 * 
 * 📧 YAHOO:
 *    smtp_host: 'smtp.mail.yahoo.com'
 *    smtp_port: 465
 *    smtp_secure: 'ssl'
 *    smtp_user: 'tu-email@yahoo.com'
 *    smtp_pass: 'contraseña de aplicación'
 */

return [
    // =============================
    // CONFIGURACIÓN SMTP
    // =============================
    
    'smtp_host' => 'smtp.gmail.com',        // Servidor SMTP (ver opciones arriba)
    'smtp_port' => 587,                      // Puerto (587 para TLS, 465 para SSL)
    'smtp_user' => 'tu-email@gmail.com',    // Tu email completo
    'smtp_pass' => 'tu-password-aqui',      // Contraseña o contraseña de aplicación
    'smtp_secure' => 'tls',                  // 'tls' o 'ssl'
    
    // =============================
    // REMITENTE
    // =============================
    
    'from_email' => 'soporte@empresa.com',
    'from_name' => 'Sistema de Soporte SGEN',
    
    // =============================
    // DESTINATARIOS ADMINISTRATIVOS
    // =============================
    
    'admin_emails' => [
        'admin@empresa.com'  // Emails que recibirán notificaciones críticas
    ],
    
    // =============================
    // ACTIVACIÓN
    // =============================
    
    'enabled' => false,  // ⚠️ CAMBIAR A true CUANDO HAYAS CONFIGURADO LOS DATOS SMTP
];

