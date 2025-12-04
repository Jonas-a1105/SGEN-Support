# Guía Rápida: Configurar Email por Proveedor

## 📧 Gmail

1. **Configurar `config/email.php`:**
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_user' => 'tu-email@gmail.com',
'smtp_pass' => 'xxxx xxxx xxxx xxxx',  // Contraseña de aplicación
'enabled' => true
```

2. **Generar contraseña de aplicación:**
   - Ve a https://myaccount.google.com/apppasswords
   - Selecciona "Correo" y "Windows Computer"
   - Copia la contraseña de 16 dígitos generada
   - Pégala en `smtp_pass` (con o sin espacios)

---

## 📧 Outlook / Hotmail

```php
'smtp_host' => 'smtp-mail.outlook.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_user' => 'tu-email@outlook.com',
'smtp_pass' => 'tu-contraseña-normal',
'enabled' => true
```

---

## 📧 Office 365 (Email Corporativo)

```php
'smtp_host' => 'smtp.office365.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_user' => 'tu-email@empresa.com',
'smtp_pass' => 'tu-contraseña-corporativa',
'enabled' => true
```

---

## 📧 Yahoo Mail

1. **Generar contraseña de aplicación:**
   - Ve a https://login.yahoo.com/account/security
   - "Generar contraseña de aplicación"

2. **Configurar:**
```php
'smtp_host' => 'smtp.mail.yahoo.com',
'smtp_port' => 465,
'smtp_secure' => 'ssl',
'smtp_user' => 'tu-email@yahoo.com',
'smtp_pass' => 'contraseña-app',
'enabled' => true
```

---

## ✅ Probar Configuración

Ejecuta el script de prueba:
```bash
cd c:\xampp\htdocs\sgen-support
php src/Cron/verificar_sla.php
```

Si está bien configurado, deberías ver:
```
✓ No hay tickets vencidos pendientes de notificación.
```

Si hay error, verás el mensaje específico del proveedor.

