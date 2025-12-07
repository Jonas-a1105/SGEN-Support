<?php
/**
 * Tab: Información del Ticket - V2 Ultra Moderna
 * Grid layout con tarjetas de dispositivo y materiales
 */

use App\Helpers\{ViewHelper, BadgeHelper};

// Preparar datos del técnico
$techName = $soporte->tecnico_asignado ?? null;
$techInitials = 'NA';
if ($techName) {
    $parts = explode(' ', $techName);
    $techInitials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
    if (strlen($techInitials) < 2) $techInitials = strtoupper(substr($techName, 0, 2));
}

// Status indicator
$statusDotColor = 'amber';
$statusText = 'Pendiente de revisión';
if ($soporte->estado === 'en_proceso') {
    $statusDotColor = 'blue';
    $statusText = 'En proceso de revisión';
} elseif ($soporte->estado === 'resuelto') {
    $statusDotColor = 'emerald';
    $statusText = 'Resuelto';
} elseif ($soporte->estado === 'cerrado') {
    $statusDotColor = 'emerald';
    $statusText = 'Cerrado';
} elseif ($soporte->estado === 'en_espera') {
    $statusDotColor = 'amber';
    $statusText = 'En espera';
}

// Icono del dispositivo
$deviceIcon = 'bi-pc-display';
$deviceType = strtolower($soporte->equipo_tipo ?? '');
if (strpos($deviceType, 'laptop') !== false || strpos($deviceType, 'portatil') !== false) {
    $deviceIcon = 'bi-laptop';
} elseif (strpos($deviceType, 'impresora') !== false || strpos($deviceType, 'printer') !== false) {
    $deviceIcon = 'bi-printer';
} elseif (strpos($deviceType, 'servidor') !== false || strpos($deviceType, 'server') !== false) {
    $deviceIcon = 'bi-hdd-rack';
}
?>

<div class="row g-4">
    <!-- Columna Izquierda: Datos del Ticket -->
    <div class="col-lg-8">
        
        <!-- Card: Detalles del Incidente -->
        <div class="td-card">
            <div class="td-card-header">
                <div class="td-card-header-left">
                    <div class="td-card-icon primary">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <h3 class="td-card-title">Detalles del Incidente</h3>
                </div>
            </div>
            
            <div class="td-card-body">
                <div class="td-info-grid">
                    
                    <!-- Fecha de Reporte -->
                    <div class="td-info-item">
                        <label class="td-info-label">
                            <i class="bi bi-calendar-event"></i> Fecha de Reporte
                        </label>
                        <p class="td-info-value">
                            <?= ViewHelper::formatDate($soporte->fecha, 'd/m/Y h:i A') ?>
                        </p>
                    </div>
                    
                    <!-- Categoría -->
                    <div class="td-info-item">
                        <label class="td-info-label">
                            <i class="bi bi-tag"></i> Categoría
                        </label>
                        <p class="td-info-value">
                            <?= BadgeHelper::renderCategoria($soporte) ?>
                        </p>
                    </div>
                    
                    <!-- Técnico Asignado -->
                    <div class="td-info-item">
                        <label class="td-info-label">
                            <i class="bi bi-person"></i> Técnico Asignado
                        </label>
                        <p class="td-info-value">
                            <?php 
                            // Prepare Data for Modal
                            $ticketJson = json_encode([
                                'id' => $soporte->id,
                                'title' => mb_strimwidth($soporte->descripcion ?? '', 0, 60, '...'),
                                'device' => ($soporte->equipo_tipo ?? 'Equipo') . ' ' . ($soporte->equipo_serial ?? ''),
                                'location' => $soporte->departamento_nombre ?? 'General',
                                'category' => $soporte->categoria_nombre ?? 'General',
                                'priority' => strtolower($soporte->prioridad ?? 'media'),
                                'timeElapsed' => ViewHelper::formatDate($soporte->fecha, 'd/m/Y h:i A') // Simplifying for detail view
                            ]);
                            $ticketJsonAttr = htmlspecialchars($ticketJson, ENT_QUOTES, 'UTF-8');
                            ?>

                            <?php if ($techName): ?>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="td-tech-avatar">
                                    <span class="avatar"><?= $techInitials ?></span>
                                    <span><?= htmlspecialchars($techName) ?></span>
                                </span>
                                <?php if (in_array($_SESSION['rol'], ['admin'])): ?>
                                <button class="btn btn-link btn-sm p-0 ms-2" onclick='AssignTechModal.open(<?= $ticketJsonAttr ?>)'>
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fst-italic">Sin asignar</span>
                                <?php if (in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                                <button class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;" 
                                    onclick='AssignTechModal.open(<?= $ticketJsonAttr ?>)'>
                                    <i class="bi bi-person-plus"></i> Asignar
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Estado Actual -->
                    <div class="td-info-item">
                        <label class="td-info-label">
                            <i class="bi bi-check-circle"></i> Estado Actual
                        </label>
                        <p class="td-info-value">
                            <span class="td-status-indicator">
                                <span class="dot <?= $statusDotColor ?>"></span>
                                <?= $statusText ?>
                            </span>
                        </p>
                    </div>
                    
                    <!-- Fecha de Cierre -->
                    <div class="td-info-item">
                        <label class="td-info-label">
                            <i class="bi bi-calendar-check"></i> Fecha de Cierre
                        </label>
                        <p class="td-info-value">
                            <?php if ($soporte->fecha_cierre): ?>
                            <span id="fechaCierreDisplay"><?= ViewHelper::formatDate($soporte->fecha_cierre, 'd/m/Y h:i A') ?></span>
                            <?php else: ?>
                            <span class="text-muted" id="fechaCierreDisplay">Pendiente</span>
                            <?php endif; ?>
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="abrirModalFechaCierre()">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </button>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Tiempo de Atención -->
                    <div class="td-info-item">
                        <label class="td-info-label">
                            <i class="bi bi-clock-history"></i> Tiempo de Atención
                        </label>
                        <p class="td-info-value">
                            <?php if (!empty($soporte->tiempo_atencion_minutos)): 
                                $horas = floor($soporte->tiempo_atencion_minutos / 60);
                                $minutos = $soporte->tiempo_atencion_minutos % 60;
                                $tiempoTexto = "";
                                if ($horas > 0) $tiempoTexto .= "{$horas}h ";
                                $tiempoTexto .= "{$minutos}m";
                                
                                $badgeClass = 'bg-success';
                                if ($soporte->tiempo_atencion_minutos > 240) $badgeClass = 'bg-danger';
                                elseif ($soporte->tiempo_atencion_minutos > 120) $badgeClass = 'bg-warning text-dark';
                            ?>
                            <span class="badge <?= $badgeClass ?>" style="font-size: 0.8rem;"><?= $tiempoTexto ?></span>
                            <?php else: ?>
                            <span class="text-muted">--</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Descripción del Problema -->
                    <div class="td-info-item full-width">
                        <label class="td-info-label">
                            <i class="bi bi-chat-left-text"></i> Descripción del Problema
                        </label>
                        <div class="td-description-box">
                            <?= nl2br(htmlspecialchars($soporte->descripcion)) ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>

    <!-- Columna Derecha: Contexto -->
    <div class="col-lg-4">
        
        <!-- Card: Activo Afectado -->
        <div class="td-card td-device-card">
            <div class="td-card-header">
                <div class="td-card-header-left">
                    <div class="td-card-icon cyan">
                        <i class="bi bi-display"></i>
                    </div>
                    <h3 class="td-card-title">Activo Afectado</h3>
                </div>
                <a href="<?= BASE_URL ?>equipos/ver/<?= $soporte->equipo_id ?>" class="td-action-btn" title="Ver equipo" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
            
            <div class="td-card-body">
                <div class="td-device-decoration">
                    <i class="<?= $deviceIcon ?>"></i>
                </div>
                
                <div class="td-device-content">
                    <!-- Serial -->
                    <div class="mb-3">
                        <label class="td-device-serial-label">Serial</label>
                        <p class="td-device-serial"><?= htmlspecialchars($soporte->equipo_serial ?? 'N/A') ?></p>
                    </div>
                    
                    <!-- Tipo y Modelo -->
                    <div class="td-device-grid">
                        <div class="td-device-item">
                            <label>Tipo</label>
                            <p><?= ucfirst(htmlspecialchars($soporte->equipo_tipo ?? 'N/A')) ?></p>
                        </div>
                        <div class="td-device-item">
                            <label>Modelo</label>
                            <p><?= htmlspecialchars($soporte->equipo_modelo ?? 'N/A') ?></p>
                        </div>
                    </div>
                    
                    <!-- Departamento -->
                    <div class="td-device-dept">
                        <div class="td-device-dept-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="td-device-dept-info">
                            <label>Departamento</label>
                            <p><?= htmlspecialchars($soporte->departamento_nombre ?? 'Sin asignar') ?></p>
                        </div>
                    </div>
                    
                    <!-- Usuario Asignado al Equipo -->
                    <?php if (!empty($soporte->equipo_empleado_nombre)): ?>
                    <div class="td-device-user">
                        <div class="td-device-user-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="td-device-user-info">
                            <label>Asignado a</label>
                            <p><?= htmlspecialchars($soporte->equipo_empleado_nombre) ?></p>
                            <?php if (!empty($soporte->equipo_empleado_cargo)): ?>
                            <small><?= htmlspecialchars($soporte->equipo_empleado_cargo) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="td-device-user">
                        <div class="td-device-user-icon" style="background: var(--td-slate-100); color: var(--td-slate-400);">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="td-device-user-info">
                            <label>Asignado a</label>
                            <p class="text-muted"><em>Sin asignar</em></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card: Materiales/Repuestos -->
        <div class="td-card">
            <div class="td-card-header">
                <div class="td-card-header-left">
                    <div class="td-card-icon slate">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h3 class="td-card-title">Materiales</h3>
                </div>
            </div>
            
            <?php if (!empty($consumos)): ?>
            <div class="td-card-body p-0">
                <table class="td-materials-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: center;">Cant.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consumos as $consumo): ?>
                        <tr>
                            <td><?= htmlspecialchars($consumo->item_nombre) ?></td>
                            <td style="text-align: center;"><?= $consumo->cantidad ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($soporte->estado == 'en_proceso' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                <div class="p-3 border-top">
                    <button type="button" class="td-materials-add-btn w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarConsumo">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Repuesto
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="td-materials-empty">
                <div class="td-materials-empty-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <p>No se han registrado consumos.</p>
                <?php if ($soporte->estado == 'en_proceso' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                <button type="button" class="td-materials-add-btn" data-bs-toggle="modal" data-bs-target="#modalAgregarConsumo">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Repuesto
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal para agregar consumo -->
<div class="modal fade" id="modalAgregarConsumo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-tools text-primary me-2"></i>
                    Agregar Repuesto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>soportes/agregar_consumo" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Repuesto</label>
                        <select name="item_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($items as $item): ?>
                            <option value="<?= $item->id ?>">
                                <?= htmlspecialchars($item->nombre) ?> (Stock: <?= $item->stock_departamento ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f5f9;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
