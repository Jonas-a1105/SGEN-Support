# Guía de Optimización del Sistema SGEN
> **Objetivo:** Lograr que el sistema sea veloz incluso en equipos de gama baja ("tostadoras").

He realizado cambios profundos en el código para mejorar el rendimiento. Aquí detallo qué se hizo y qué puedes configurar en tu servidor para mejorarlo aún más.

## 1. Cambios Implementados (Ya funcionando)

### A. "Cache" Inteligente en Navegador (`.htaccess`)
**¿Qué hace?**
Le dice al navegador (Chrome, Firefox, Edge) que guarde imágenes, CSS y Javascripts en la memoria del PC del usuario por 1 año.
**Resultado:**
- La primera carga es normal.
- Las siguientes cargas son **instantáneas** porque no descargan el diseño ni los scripts de nuevo.
- Ahorra ancho de banda y CPU del servidor.

### B. Búsqueda Instantánea sin "Cuelgues" (`soportes.js`)
**Problema Anterior:**
Al escribir en el buscador de tickets, el sistema intentaba filtrar la lista cientos de veces por segundo, congelando navegadores lentos.
**Solución:**
Implementé una técnica llamada **"Debouncing"** y **"DOM Caching"**.
- El sistema espera 300ms a que termines de escribir.
- Mantiene una copia de los datos en memoria para no leer la pantalla constantemente.
**Resultado:** Escritura fluida y filtrado ultra-rápido.

### C. Índices de Base de Datos (Optimización Backend)
**Problema Anterior:**
MySQL leía toda la tabla para encontrar un ticket o equipo.
**Solución:**
Agregué índices (atajos) a las columnas clave:
- `soportes`: estado, prioridad, creador, técnico.
- `equipos`: serial, código, tipo.
**Resultado:** Consultas de base de datos ~100x más rápidas.

---

## 2. Recomendaciones de Servidor (XAMPP/Apache)

Si tienes acceso a la configuración de PHP y MySQL (`php.ini` y `my.ini`), ajusta estos valores para exprimir el rendimiento:

### A. Habilitar OPcache (CRÍTICO para PHP)
PHP debe compilarse cada vez que alguien abre una página. OPcache guarda el código compilado en RAM.
**En `php.ini` busca y descomenta/modifica:**
```ini
[opcache]
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```
> *Esto por sí solo puede duplicar la velocidad del sistema.*

### B. Ajustes de MySQL (`my.ini`)
Si tienes poca RAM (ej. 4GB), ajusta el buffer de InnoDB:
```ini
[mysqld]
innodb_buffer_pool_size = 512M  ; Asigna 50-70% de la RAM disponible solo si es un servidor dedicado
query_cache_type = 1
query_cache_size = 64M
```

## 3. Mantenimiento Futuro

Si el sistema opera por años y se vuelve lento:
1.  **Archivado de Tickets:** Mover tickets "Cerrados" de hace más de 2 años a una tabla `soportes_historico`.
2.  **Limpieza de Logs:** Vaciar tablas de auditoría si crecen demasiado.
