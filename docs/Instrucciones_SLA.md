# Instrucciones: Configuración del Sistema SLA con Notificaciones

## 📋 Resumen
Se han implementado mejoras al sistema SLA:
1. ✅ **Tiempos configurables** mediante `config/sla.php`
2. ✅ **Notificaciones automáticas** por email cuando los tickets vencen

---

## 🔧 Configuración Requerida

### Paso 1: Configurar Tiempos de SLA

Edita el archivo `config/sla.php` si deseas cambiar los tiempos:

```php
return [
    'alta' => 4,    // Cambiar a X horas
    'media' => 24,  // Cambiar a X horas
    'baja' => 48    // Cambiar a X horas
];
```

### Paso 2: Configurar Email

Edita el archivo `config/email.php`:

```php
return [
    'smtp_host' => 'smtp.gmail.com',  // Tu servidor SMTP
    'smtp_user' => 'tu-email@gmail.com',
    'smtp_pass' => 'tu-password',
    'from_email' => 'soporte@empresa.com',
    'admin_emails' => ['admin@empresa.com'],
    'enabled' => true  // CAMBIAR A true PARA ACTIVAR
];
```

**Para Gmail:**
- Habilita "Verificación en 2 pasos"
- Genera una "Contraseña de aplicación" en https://myaccount.google.com/apppasswords
- Usa esa contraseña en `smtp_pass`

### Paso 3: Configurar Tarea Automática (Cron)

#### En Windows (Task Scheduler):
1. Abre "Programador de Tareas"
2. Crear tarea básica:
   - **Nombre:** Verificar SLA Tickets
   - **Desencadenador:** Diariamente, cada 1 hora
   - **Acción:** Iniciar programa
     - **Programa:** `C:\xampp\php\php.exe`
     - **Argumentos:** `C:\xampp\htdocs\sgen-support\src\Cron\verificar_sla.php`

#### En Linux (Crontab):
```bash
crontab -e
# Agregar línea:
0 * * * * /usr/bin/php /ruta/a/sgen-support/src/Cron/verificar_sla.php
```

---

## 🧪 Prueba Manual

Ejecuta el script manualmente para probar:

```bash
cd c:\xampp\htdocs\sgen-support
php src/Cron/verificar_sla.php
```

Deberías ver:
```
[2025-12-03 16:00:00] Iniciando verificación de SLA...
✓ No hay tickets vencidos pendientes de notificación.
```

---

## 📧 Formato del Email

Los técnicos y administradores recibirán un email HTML con:
- 🔴 Alerta visual de TICKET VENCIDO
- Información del ticket (ID, prioridad, equipo)
- Fecha de vencimiento vs fecha actual
- Botón directo para ver el ticket

---

## 📊 ¿Cómo funciona?

1. **Cada hora** (o según configures), el script `verificar_sla.php` se ejecuta automáticamente
2. Busca tickets que:
   - NO estén resueltos
   - Tengan fecha de vencimiento pasada
   - NO hayan sido notificados aún
3. Envía email al técnico asignado y administradores
4. Marca el ticket como "notificado" para no enviar duplicados

---

## ⚠️ Importante

- Si `enabled = false` en `config/email.php`, NO se enviarán emails
- Solo se envía **un email por ticket vencido** (no se repite)
- Los tickets resueltos NO generan notificaciones

