-- Script para limpiar notificaciones con URLs incorrectas
-- Ejecuta este script en tu base de datos para eliminar las notificaciones con enlaces rotos

-- Opción 1: Eliminar todas las notificaciones con URLs que contengan {id} literal
DELETE FROM notificaciones 
WHERE enlace LIKE '%{id}%';

-- Opción 2: Si prefieres solo marcarlas como leídas en vez de eliminarlas
-- UPDATE notificaciones 
-- SET leido = 1 
-- WHERE enlace LIKE '%{id}%';

-- Verificar cuántas notificaciones quedan
SELECT COUNT(*) as total_notificaciones FROM notificaciones;
