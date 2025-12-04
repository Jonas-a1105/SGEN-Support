<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General de Soportes</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            margin: 20px;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: middle; padding: 0; border: none; }
        .logo { width: 80px; height: auto; }
        .title-cell { text-align: right; padding-right: 10px; }
        h2 { margin: 0; font-size: 18px; color: #333; }
        h3 { text-align: center; margin-bottom: 20px; }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .report-table th, .report-table td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .estado-resuelto { color: green; }
        .estado-pendiente { color: #dd8c00; }
        .estado-en_proceso { color: #007bff; }
        .footer { margin-top: 20px; font-size: 10px; text-align: center; color: #888; }
    </style>
</head>
<body>

    <?php
    // Cargar el logo
    $logoPath = __DIR__ . '/../../../public/img/p_id.png';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $imageData = file_get_contents($logoPath);
        $imageMime = mime_content_type($logoPath);
        $logoBase64 = 'data:' . $imageMime . ';base64,' . base64_encode($imageData);
    }
    ?>

    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                <?php if ($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" class="logo" alt="Logo">
                <?php endif; ?>
            </td>
            <td class="title-cell">
                <h2>Reporte General de Soportes</h2>
                <strong>Fecha:</strong> <?= date('d/m/Y') ?>
            </td>
        </tr>
    </table>

    <?php if (!empty($filters)): ?>
    <div style="margin-bottom: 15px; font-size: 9px; background-color: #f9f9f9; padding: 5px; border: 1px solid #ddd;">
        <strong>Filtros aplicados:</strong>
        <?php
        $filtrosTexto = [];
        if (!empty($filters['fecha_inicio'])) $filtrosTexto[] = "Desde: " . date('d/m/Y', strtotime($filters['fecha_inicio']));
        if (!empty($filters['fecha_fin'])) $filtrosTexto[] = "Hasta: " . date('d/m/Y', strtotime($filters['fecha_fin']));
        if (!empty($filters['estado'])) $filtrosTexto[] = "Estado: " . ucfirst($filters['estado']);
        if (!empty($filters['prioridad'])) $filtrosTexto[] = "Prioridad: " . ucfirst($filters['prioridad']);
        if (!empty($filters['categoria_id'])) $filtrosTexto[] = "Categoría ID: " . $filters['categoria_id'];
        
        echo implode(' | ', $filtrosTexto);
        ?>
    </div>
    <?php endif; ?>

    <h3>Listado de Tickets</h3>

    <table class="report-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Categoría</th>
                <th>Departamento</th>
                <th>Equipo (Serial)</th>
                <th>Técnico</th>
                <th>Cierre</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($soportes)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay soportes registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($soportes as $soporte): ?>
                    <?php
                        $estado_class = 'estado-' . strtolower($soporte->estado);
                        $estado_texto = ucfirst(str_replace('_', ' ', $soporte->estado));
                    ?>
                    <tr>
                        <td><?= $soporte->id ?></td>
                        <td><?= date('d/m/y', strtotime($soporte->fecha)) ?></td>
                        <td><span class="<?= $estado_class ?>"><?= $estado_texto ?></span></td>
                        <td><?= ucfirst($soporte->prioridad) ?></td>
                        <td>
                            <?php if (!empty($soporte->categoria_nombre)): ?>
                                <span style="color: <?= $soporte->categoria_color ?>"><?= htmlspecialchars($soporte->categoria_nombre) ?></span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($soporte->departamento_nombre) ?></td>
                        <td><?= htmlspecialchars($soporte->equipo_serial) ?></td>
                        <td><?= htmlspecialchars($soporte->tecnico_asignado ?? 'N/A') ?></td>
                        <td><?= $soporte->fecha_cierre ? date('d/m/y', strtotime($soporte->fecha_cierre)) : 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>SGEN-Support - Reporte generado automáticamente.</p>
    </div>

</body>
</html>