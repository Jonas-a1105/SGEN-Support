<?php
/**
 * Script de limpieza de notificaciones con enlaces rotos
 * Ejecuta este script una sola vez para eliminar notificaciones con URLs que contienen {id} literal
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use App\Models\Notificacion;

$notificacionModel = new Notificacion();

echo "<h2>Limpieza de Notificaciones con Enlaces Rotos</h2>";
echo "<p>Eliminando notificaciones con URLs que contienen {id} literal...</p>";

$deletedCount = $notificacionModel->deleteBrokenLinks();

echo "<p style='color: green;'><strong>✓ Éxito:</strong> Se eliminaron {$deletedCount} notificaciones con enlaces rotos.</p>";
echo "<p>Ya puedes <a href='" . BASE_URL . "'>volver al dashboard</a>.</p>";
