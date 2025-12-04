<?php
/**
 * Tab: Información del Ticket
 * Incluye: Datos del ticket, equipo afectado, repuestos
 */
?>

<div class="row">
    <!-- Columna Principal: Información del Ticket -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-ticket-detailed me-2"></i>Información del Ticket</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong><i class="bi bi-calendar-event me-2"></i>Fecha de Reporte:</strong></p>
                        <p><?= ViewHelper::formatDate($soporte->fecha, 'd/m/Y h:i A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong><i class="bi bi-tags me-2"></i>Categoría:</strong></p>
                        <?= BadgeHelper::renderCategoria($soporte) ?>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="mb-2"><strong><i class="bi bi-person-check me-2"></i>Técnico Asignado:</strong></p>
                        <p><?= ViewHelper::escape($soporte->tecnico_asignado, 'Sin asignar') ?></p>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="mb-2"><strong><i class="bi bi-calendar-check me-2"></i>Fecha de Cierre:</strong></p>
                        <p>
                            <?php if ($soporte->fecha_cierre): ?>
                                <span id="fechaCierreDisplay"><?= ViewHelper::formatDate($soporte->fecha_cierre, 'd/m/Y h:i A') ?></span>
                            <?php else: ?>
                                <span class="text-muted" id="fechaCierreDisplay">Pendiente</span>
                            <?php endif; ?>
                            
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="abrirModalFechaCierre()">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6 mt-3">
                        <p class="mb-2"><strong><i class="bi bi-clock-history me-2"></i>Tiempo de Atención:</strong></p>
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
                            <span class="badge <?= $badgeClass ?> fs-6"><?= $tiempoTexto ?></span>
                        <?php else: ?>
                            <span class="text-muted">--</span>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3"><i class="bi bi-chat-left-text me-2"></i>Descripción del Problema</h6>
                <div class="bg-light p-3 rounded border">
                    <p class="mb-0" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($soporte->descripcion)) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Lateral: Equipo y Repuestos -->
    <div class="col-lg-4">
        <!-- Información del Equipo -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-pc-display me-2"></i>Equipo Afectado</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Serial</small>
                    <p class="fw-bold mb-0"><?= ViewHelper::escape($soporte->equipo_serial, 'N/A') ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Tipo</small>
                    <p class="mb-0"><?= ucfirst(ViewHelper::escape($soporte->equipo_tipo, 'N/A')) ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Modelo</small>
                    <p class="mb-0"><?= ViewHelper::escape($soporte->equipo_modelo, 'N/A') ?></p>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted">Departamento</small>
                    <p class="mb-0"><?= ViewHelper::escape($soporte->departamento_nombre, 'Sin asignar') ?></p>
                </div>
            </div>
        </div>

        <!-- Repuestos y Materiales -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-tools me-2"></i>Repuestos y Materiales</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($consumos)): ?>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consumos as $consumo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($consumo->item_nombre) ?></td>
                                    <td class="text-center"><?= $consumo->cantidad ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3 small"><em>No se han registrado consumos.</em></p>
                <?php endif; ?>

                <!-- Formulario para agregar consumo -->
                <?php if ($soporte->estado == 'en_proceso' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                    <hr>
                    <h6 class="mb-3">Agregar Repuesto</h6>
                    <form action="<?= BASE_URL ?>soportes/agregar_consumo" method="POST">
                        <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                        
                        <div class="mb-2">
                            <label class="form-label small">Repuesto</label>
                            <select name="item_id" class="form-select form-select-sm" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item->id ?>">
                                        <?= htmlspecialchars($item->nombre) ?> (<?= $item->stock_departamento ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Cantidad</label>
                            <input type="number" name="cantidad" class="form-control form-control-sm" min="1" value="1" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-plus-circle me-1"></i> Agregar
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
