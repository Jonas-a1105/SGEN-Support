<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha del Equipo - <?= htmlspecialchars($equipo->codigo_inventario) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: white;
            color: #0f172a;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .header p {
            color: #64748b;
            font-size: 0.875rem;
        }
        .print-date {
            text-align: right;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }
        .section {
            margin-bottom: 1.5rem;
        }
        .section-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-title i {
            color: #4f46e5;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
        }
        .status-badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            background: #d1fae5;
            color: #059669;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .qr-section {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e2e8f0;
        }
        .qr-placeholder {
            width: 120px;
            height: 120px;
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 0.5rem;
            margin: 0 auto 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-placeholder i {
            font-size: 3rem;
            color: #cbd5e1;
        }
        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        @media print {
            body { padding: 1rem; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print" style="position: fixed; top: 1rem; right: 1rem; padding: 0.5rem 1rem; background: #4f46e5; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <i class="bi bi-printer"></i> Imprimir
    </button>

    <div class="header">
        <h1><?= htmlspecialchars($equipo->marca . ' ' . $equipo->modelo) ?></h1>
        <p><?= ucfirst($equipo->tipo ?? 'Equipo') ?></p>
    </div>

    <div class="print-date">
        Generado: <?= date('d/m/Y H:i') ?>
    </div>

    <div class="section">
        <div class="section-title">
            <i class="bi bi-tag"></i>
            Identificación
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Código de Inventario</span>
                <span class="info-value"><?= htmlspecialchars($equipo->codigo_inventario ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Número de Serie</span>
                <span class="info-value"><?= htmlspecialchars($equipo->numero_serie ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado</span>
                <span class="status-badge"><?= ucfirst(str_replace('_', ' ', $equipo->estado ?? 'N/A')) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tipo</span>
                <span class="info-value"><?= ucfirst($equipo->tipo ?? 'N/A') ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <i class="bi bi-cpu"></i>
            Especificaciones Técnicas
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Procesador</span>
                <span class="info-value"><?= htmlspecialchars($equipo->procesador ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Memoria RAM</span>
                <span class="info-value"><?= htmlspecialchars($equipo->memoria_ram ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Almacenamiento</span>
                <span class="info-value"><?= htmlspecialchars($equipo->almacenamiento ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Sistema Operativo</span>
                <span class="info-value"><?= htmlspecialchars($equipo->sistema_operativo ?? 'N/A') ?></span>
            </div>
            <?php if (!empty($equipo->direccion_ip)): ?>
            <div class="info-item">
                <span class="info-label">Dirección IP</span>
                <span class="info-value"><?= htmlspecialchars($equipo->direccion_ip) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($equipo->mac_address)): ?>
            <div class="info-item">
                <span class="info-label">MAC Address</span>
                <span class="info-value"><?= htmlspecialchars($equipo->mac_address) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <i class="bi bi-geo-alt"></i>
            Ubicación y Asignación
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Departamento</span>
                <span class="info-value"><?= htmlspecialchars($departamento->nombre ?? 'No asignado') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Ubicación Física</span>
                <span class="info-value"><?= htmlspecialchars($equipo->ubicacion_fisica ?? 'No especificada') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Asignado a</span>
                <span class="info-value"><?= $empleado ? htmlspecialchars($empleado->nombre . ' ' . $empleado->apellido) : 'Sin asignar' ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($equipo->proveedor) || !empty($equipo->fecha_compra)): ?>
    <div class="section">
        <div class="section-title">
            <i class="bi bi-receipt"></i>
            Información de Adquisición
        </div>
        <div class="info-grid">
            <?php if (!empty($equipo->proveedor)): ?>
            <div class="info-item">
                <span class="info-label">Proveedor</span>
                <span class="info-value"><?= htmlspecialchars($equipo->proveedor) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($equipo->fecha_compra)): ?>
            <div class="info-item">
                <span class="info-label">Fecha de Compra</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($equipo->fecha_compra)) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($equipo->valor_compra)): ?>
            <div class="info-item">
                <span class="info-label">Valor de Compra</span>
                <span class="info-value">$<?= number_format($equipo->valor_compra, 2) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($equipo->garantia)): ?>
            <div class="info-item">
                <span class="info-label">Garantía hasta</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($equipo->garantia)) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="qr-section">
        <div class="qr-placeholder">
            <i class="bi bi-qr-code"></i>
        </div>
        <p style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($equipo->codigo_inventario) ?></p>
    </div>

    <div class="footer">
        <p>SGEN-Support • Sistema de Gestión de Equipos</p>
        <p>Documento generado automáticamente</p>
    </div>
</body>
</html>
