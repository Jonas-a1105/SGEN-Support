# 🚀 Guía de Despliegue en Producción

> **Última actualización:** 2025-12-10

Esta guía detalla los pasos para desplegar SGEN-Support en un servidor de producción.

---

## 📋 Requisitos del Servidor

### Software Requerido
| Componente | Versión Mínima | Recomendada |
|------------|----------------|-------------|
| PHP | 8.0 | 8.1+ |
| MySQL/MariaDB | 5.7 | 8.0+ |
| Apache/Nginx | 2.4 | 2.4+ |
| Composer | 2.0 | Última |

### Extensiones PHP Requeridas
```
- pdo_mysql
- mbstring
- openssl
- fileinfo
- json
- gd (para imágenes)
- zip (para respaldos)
```

---

## 🔧 Pasos de Instalación

### 1. Subir Archivos al Servidor

```bash
# Clonar repositorio
git clone https://github.com/Jonas-a1105/SGEN-Support.git
cd SGEN-Support

# O subir archivos via FTP/SFTP
```

### 2. Instalar Dependencias

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configurar Variables de Entorno

```bash
# Copiar plantilla
cp .env.example .env

# Editar configuración
nano .env
```

**Configuración mínima en .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://soporte.tuempresa.com/public

DB_HOST=localhost
DB_NAME=sgen_db
DB_USER=sgen_user
DB_PASS=ContraseñaMuySegura123!@#
```

### 4. Crear Base de Datos y Usuario

```sql
-- Conectar a MySQL como root
mysql -u root -p

-- Crear base de datos
CREATE DATABASE sgen_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario específico (NO usar root)
CREATE USER 'sgen_user'@'localhost' IDENTIFIED BY 'ContraseñaMuySegura123!@#';

-- Asignar permisos limitados
GRANT SELECT, INSERT, UPDATE, DELETE ON sgen_db.* TO 'sgen_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Importar estructura
USE sgen_db;
SOURCE /ruta/al/proyecto/database/schema.sql;
```

### 5. Configurar Permisos de Archivos

```bash
# Propietario del servidor web (generalmente www-data)
sudo chown -R www-data:www-data /var/www/sgen-support

# Permisos de directorios
sudo find /var/www/sgen-support -type d -exec chmod 755 {} \;

# Permisos de archivos
sudo find /var/www/sgen-support -type f -exec chmod 644 {} \;

# Permisos de escritura para uploads
sudo chmod -R 775 /var/www/sgen-support/public/uploads
sudo chmod -R 775 /var/www/sgen-support/storage
```

### 6. Configurar Apache (VirtualHost)

Crear archivo `/etc/apache2/sites-available/sgen-support.conf`:

```apache
<VirtualHost *:80>
    ServerName soporte.tuempresa.com
    DocumentRoot /var/www/sgen-support/public
    
    <Directory /var/www/sgen-support/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Redirigir a HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    ErrorLog ${APACHE_LOG_DIR}/sgen-error.log
    CustomLog ${APACHE_LOG_DIR}/sgen-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName soporte.tuempresa.com
    DocumentRoot /var/www/sgen-support/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/tu_certificado.crt
    SSLCertificateKeyFile /etc/ssl/private/tu_llave.key
    
    <Directory /var/www/sgen-support/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Habilitar sitio:
```bash
sudo a2ensite sgen-support.conf
sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

---

## 🔐 Checklist de Seguridad

### Antes del Lanzamiento

- [ ] `APP_DEBUG=false` en .env
- [ ] Usuario de BD específico (no root)
- [ ] Contraseña de BD fuerte (16+ caracteres)
- [ ] HTTPS habilitado con certificado válido
- [ ] Archivo .env NO accesible desde navegador
- [ ] Directorio vendor/ NO accesible desde navegador
- [ ] Permisos de archivos correctos
- [ ] Backups automáticos configurados

### Archivos que NUNCA deben ser accesibles

```
.env
.env.example
composer.json
composer.lock
/vendor/
/config/
/src/
/database/
```

### Verificar en Apache (.htaccess en raíz)

```apache
# Bloquear acceso a archivos sensibles
<FilesMatch "^\.env|composer\.(json|lock)$">
    Require all denied
</FilesMatch>
```

---

## 📊 Monitoreo Post-Lanzamiento

### Logs Importantes

| Log | Ubicación | Propósito |
|-----|-----------|-----------|
| Apache Error | `/var/log/apache2/sgen-error.log` | Errores PHP |
| Apache Access | `/var/log/apache2/sgen-access.log` | Peticiones HTTP |
| MySQL | `/var/log/mysql/error.log` | Errores de BD |

### Comandos Útiles

```bash
# Ver errores en tiempo real
tail -f /var/log/apache2/sgen-error.log

# Verificar espacio en disco
df -h

# Ver procesos MySQL
mysqladmin -u root -p processlist
```

---

## 🔄 Actualizaciones

### Proceso de Actualización

```bash
# 1. Crear backup
mysqldump -u sgen_user -p sgen_db > backup_$(date +%Y%m%d).sql

# 2. Obtener cambios
cd /var/www/sgen-support
git pull origin main

# 3. Actualizar dependencias
composer install --no-dev --optimize-autoloader

# 4. Ejecutar migraciones (si hay)
php migrations/run_migration.php

# 5. Limpiar cache (si aplica)
# php scripts/clear_cache.php
```

---

## 🆘 Solución de Problemas

### Error 500 - Internal Server Error
1. Revisar logs: `tail -f /var/log/apache2/sgen-error.log`
2. Verificar permisos de archivos
3. Verificar configuración de .env

### No conecta a Base de Datos
1. Verificar credenciales en .env
2. Verificar que MySQL está corriendo: `systemctl status mysql`
3. Probar conexión manual: `mysql -u sgen_user -p sgen_db`

### Página en Blanco
1. Habilitar errores temporalmente en .env: `APP_DEBUG=true`
2. Revisar logs de Apache
3. Verificar sintaxis PHP: `php -l public/index.php`

---

## 📞 Soporte

Si encuentras problemas durante el despliegue:
1. Revisa los logs del servidor
2. Consulta la documentación en `/docs/`
3. Abre un issue en el repositorio

---

**Desarrollado por:** Jonás Mendoza
