<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mantenimientos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; color: white; }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-danger { background-color: #dc3545; }
        .bg-info { background-color: #17a2b8; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Mantenimientos</h2>
        <p>Generado el: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Equipo</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Próxima Fecha</th>
                <th>Costo</th>
                <th>Realizado Por</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mantenimientos as $m): 
                $estadoClass = match($m->estado) {
                    'pendiente' => 'bg-warning',
                    'en_proceso' => 'bg-info',
                    'completado' => 'bg-success',
                    'pospuesto' => 'bg-secondary',
                    'cancelado' => 'bg-danger',
                    default => 'bg-secondary'
                };
            ?>
            <tr>
                <td><?= htmlspecialchars($m->equipo_codigo ?? 'N/A') ?></td>
                <td><?= ucfirst($m->tipo_mantenimiento) ?></td>
                <td><span class="badge <?= $estadoClass ?>"><?= ucfirst($m->estado) ?></span></td>
                <td><?= date('d/m/Y', strtotime($m->fecha)) ?></td>
                <td><?= $m->proxima_fecha ? date('d/m/Y', strtotime($m->proxima_fecha)) : '-' ?></td>
                <td>$<?= number_format($m->costo, 2) ?></td>
                <td><?= htmlspecialchars($m->realizado_por) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        SGEN-Support - Sistema de Gestión de Soporte
    </div>
</body>
</html>
