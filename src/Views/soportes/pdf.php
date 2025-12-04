<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $soporte->id ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 5px; width: 50%; vertical-align: top; }
        .label { font-weight: bold; color: #666; }
        .section-title { background: #f8f9fa; padding: 8px; font-weight: bold; margin: 15px 0 10px 0; border-left: 4px solid #0d6efd; }
        .badge { padding: 3px 8px; border-radius: 4px; color: white; font-size: 10px; }
        .bg-success { background-color: #198754; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-danger { background-color: #dc3545; }
        .bg-info { background-color: #0dcaf0; }
        .bg-secondary { background-color: #6c757d; }
        .signature-box { margin-top: 50px; text-align: center; page-break-inside: avoid; }
        .signature-img { max-width: 300px; border-bottom: 1px solid #ccc; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Servicio Técnico</h2>
        <p>Ticket #<?= $soporte->id ?> - <?= date('d/m/Y H:i', strtotime($soporte->fecha)) ?></p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <span class="label">Solicitante:</span><br>
                <?= htmlspecialchars($soporte->nombre_empleado ?? 'N/A') ?><br>
                <small><?= htmlspecialchars($soporte->departamento_nombre ?? '') ?></small>
            </div>
            <div class="info-cell">
                <span class="label">Estado:</span><br>
                <span class="badge bg-<?= $soporte->estado === 'resuelto' ? 'success' : ($soporte->estado === 'en_proceso' ? 'info' : 'warning') ?>">
                    <?= ucfirst(str_replace('_', ' ', $soporte->estado)) ?>
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <span class="label">Equipo:</span><br>
                <?= htmlspecialchars($soporte->equipo_tipo ?? '') ?> - <?= htmlspecialchars($soporte->equipo_marca ?? '') ?><br>
                <small>S/N: <?= htmlspecialchars($soporte->equipo_serial ?? 'N/A') ?></small>
            </div>
            <div class="info-cell">
                <span class="label">Técnico Asignado:</span><br>
                <?= htmlspecialchars($soporte->tecnico_asignado ?? 'Sin asignar') ?>
            </div>
        </div>
    </div>

    <div class="section-title">Descripción del Problema</div>
    <p><?= nl2br(htmlspecialchars($soporte->descripcion)) ?></p>

    <?php if (!empty($soporte->observaciones)): ?>
        <div class="section-title">Observaciones / Solución</div>
        <p><?= nl2br(htmlspecialchars($soporte->observaciones)) ?></p>
    <?php endif; ?>

    <?php if (!empty($comentarios)): ?>
        <div class="section-title">Historial de Comentarios</div>
        <?php foreach ($comentarios as $comentario): ?>
            <div style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                <strong><?= htmlspecialchars($comentario->nombre_usuario) ?></strong> 
                <small class="text-muted">(<?= date('d/m/Y H:i', strtotime($comentario->fecha)) ?>)</small><br>
                <?= nl2br(htmlspecialchars($comentario->comentario)) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($soporte->firma_usuario)): ?>
        <div class="signature-box">
            <img src="<?= $soporte->firma_usuario ?>" class="signature-img" alt="Firma del Usuario">
            <p>Firma de Conformidad</p>
            <p><?= htmlspecialchars($soporte->nombre_empleado ?? 'Usuario') ?></p>
        </div>
    <?php else: ?>
        <div class="signature-box">
            <div style="height: 100px; border-bottom: 1px solid #ccc; width: 300px; margin: 0 auto;"></div>
            <p>Firma de Conformidad</p>
            <p><?= htmlspecialchars($soporte->nombre_empleado ?? 'Usuario') ?></p>
        </div>
    <?php endif; ?>

    <div class="footer">
        Generado el <?= date('d/m/Y H:i:s') ?> por SGEN Support
    </div>
</body>
</html>