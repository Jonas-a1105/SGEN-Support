<?php
// Vista de detalle de soporte - Diseño moderno
$estado_class = '';
$estado_texto = ucfirst(str_replace('_', ' ', $soporte->estado));
switch ($soporte->estado) {
    case 'pendiente':
        $estado_class = 'badge bg-warning text-dark';
        break;
    case 'en_proceso':
        $estado_class = 'badge bg-info text-dark';
        break;
    case 'en_espera':
        $estado_class = 'badge bg-secondary';
        break;
    case 'resuelto':
        $estado_class = 'badge bg-success';
        break;
    default:
        $estado_class = 'badge bg-secondary';
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">🎫 Ticket #<?= $soporte->id ?></h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>soportes" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle me-1"></i> Volver
            </a>
            <?php if ($soporte->estado == 'en_proceso' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                <button onclick="confirmarAccion('<?= BASE_URL ?>soportes/marcar_espera/<?= $soporte->id ?>', '¿Poner en espera?', 'El ticket cambiará a estado de espera.', 'warning', 'Sí, poner en espera')" 
                   class="btn btn-warning text-dark">
                    <i class="bi bi-pause-circle me-1"></i> Poner en Espera
                </button>
                <button onclick="confirmarAccion('<?= BASE_URL ?>soportes/resolver/<?= $soporte->id ?>', '¿Marcar como resuelto?', 'El ticket se marcará como completado.', 'success', 'Sí, resolver')" 
                   class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i> Marcar como Resuelto
                </button>
            <?php endif; ?>

            <?php if ($soporte->estado == 'en_espera' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                <button onclick="confirmarAccion('<?= BASE_URL ?>soportes/reanudar/<?= $soporte->id ?>', '¿Reanudar ticket?', 'El ticket volverá a estar en proceso.', 'info', 'Sí, reanudar')" 
                   class="btn btn-info text-white">
                    <i class="bi bi-play-circle me-1"></i> Reanudar Ticket
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Información del Ticket -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-ticket-detailed me-2"></i>Información del Ticket</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>Estado:</strong></p>
                            <span class="<?= $estado_class ?> fs-6"><?= $estado_texto ?></span>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bi bi-calendar-event me-2"></i>Fecha de Reporte:</strong></p>
                            <p><?= date('d/m/Y h:i A', strtotime($soporte->fecha)) ?></p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <p class="mb-2"><strong><i class="bi bi-exclamation-triangle me-2"></i>Prioridad:</strong></p>
                            <?php
                            $prioridadClass = match($soporte->prioridad ?? 'media') {
                                'alta' => 'badge bg-danger',
                                'media' => 'badge bg-warning text-dark',
                                'baja' => 'badge bg-success',
                                default => 'badge bg-secondary'
                            };
                            ?>
                            <span class="<?= $prioridadClass ?> fs-6"><?= ucfirst($soporte->prioridad ?? 'media') ?></span>
                        </div>
                        <div class="col-md-6 mt-3">
                            <p class="mb-2"><strong><i class="bi bi-calendar-x me-2"></i>Vencimiento (SLA):</strong></p>
                            <?php if (!empty($soporte->fecha_vencimiento)): ?>
                                <?php
                                    $vence = strtotime($soporte->fecha_vencimiento);
                                    $ahora = time();
                                    $esResuelto = $soporte->estado === 'resuelto';
                                    
                                    if ($esResuelto) {
                                        echo '<span class="badge bg-secondary"><i class="bi bi-check-circle me-1"></i>Completado</span>';
                                    } elseif ($ahora > $vence) {
                                        echo '<span class="badge bg-danger animate-pulse"><i class="bi bi-exclamation-triangle me-1"></i>VENCIDO</span>';
                                    } elseif ($ahora > $vence - 3600) { // Menos de 1 hora
                                        echo '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Por vencer</span>';
                                    } else {
                                        echo '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>A tiempo</span>';
                                    }
                                ?>
                                <small class="text-muted d-block mt-1">
                                    <?= date('d/m/Y H:i', $vence) ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted small">No definido</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mt-3">
                            <p class="mb-2"><strong><i class="bi bi-tags me-2"></i>Categoría:</strong></p>
                            <?php if (!empty($soporte->categoria_nombre)): ?>
                                <span class="badge" style="background-color: <?= $soporte->categoria_color ?>; color: white;">
                                    <i class="<?= $soporte->categoria_icono ?> me-1"></i>
                                    <?= htmlspecialchars($soporte->categoria_nombre) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Sin categorizar</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bi bi-person-check me-2"></i>Técnico Asignado:</strong></p>
                            <p><?= htmlspecialchars($soporte->tecnico_asignado ?? 'Sin asignar') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bi bi-calendar-check me-2"></i>Fecha de Cierre:</strong></p>
                            <p>
                                <?php if ($soporte->fecha_cierre): ?>
                                    <span id="fechaCierreDisplay"><?= date('d/m/Y h:i A', strtotime($soporte->fecha_cierre)) ?></span>
                                <?php else: ?>
                                    <span class="text-muted" id="fechaCierreDisplay">Pendiente</span>
                                <?php endif; ?>
                                
                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="abrirModalFechaCierre()" title="<?= $soporte->fecha_cierre ? 'Editar fecha de cierre' : 'Establecer fecha de cierre' ?>">
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
                                if ($soporte->tiempo_atencion_minutos > 240) $badgeClass = 'bg-danger'; // > 4 horas
                                elseif ($soporte->tiempo_atencion_minutos > 120) $badgeClass = 'bg-warning text-dark'; // > 2 horas
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

            <!-- Archivos Adjuntos -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-paperclip me-2"></i>Archivos Adjuntos</h5>
                    <?php if (in_array($soporte->estado, ['pendiente', 'en_proceso'])): ?>
                        <button class="btn btn-sm btn-light" onclick="document.getElementById('inputArchivo').click()">
                            <i class="bi bi-plus-lg me-1"></i> Adjuntar
                        </button>
                        <input type="file" id="inputArchivo" style="display: none;" onchange="subirArchivo(this)">
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div id="listaArchivos" class="list-group list-group-flush">
                        <?php if (empty($archivos)): ?>
                            <p class="text-muted text-center my-3" id="noArchivosMsg">No hay archivos adjuntos.</p>
                        <?php else: ?>
                            <?php foreach ($archivos as $archivo): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php
                                            $icono = 'bi-file-earmark';
                                            if (strpos($archivo->tipo_mime, 'image') !== false) $icono = 'bi-file-earmark-image text-primary';
                                            elseif (strpos($archivo->tipo_mime, 'pdf') !== false) $icono = 'bi-file-earmark-pdf text-danger';
                                            elseif (strpos($archivo->tipo_mime, 'spreadsheet') !== false || strpos($archivo->tipo_mime, 'excel') !== false) $icono = 'bi-file-earmark-excel text-success';
                                            elseif (strpos($archivo->tipo_mime, 'word') !== false) $icono = 'bi-file-earmark-word text-primary';
                                        ?>
                                        <i class="bi <?= $icono ?> fs-4 me-3"></i>
                                        <div>
                                            <h6 class="mb-0">
                                                <a href="<?= BASE_URL ?>soportes/descargar_archivo/<?= $archivo->id ?>" target="_blank" class="text-decoration-none text-dark">
                                                    <?= htmlspecialchars($archivo->nombre_original) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?= number_format($archivo->tamaño_bytes / 1024, 2) ?> KB - 
                                                <?= date('d/m/Y H:i', strtotime($archivo->fecha_subida)) ?> - 
                                                Por: <?= htmlspecialchars($archivo->nombre_usuario ?? 'Desconocido') ?>
                                            </small>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL ?>soportes/descargar_archivo/<?= $archivo->id ?>" class="btn btn-sm btn-outline-primary" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Progress Bar -->
                    <div class="progress mt-3 d-none" id="uploadProgressContainer">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="uploadProgressBar"></div>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="bi bi-chat-dots me-2"></i>Comentarios</h5>
                    <span class="badge bg-secondary"><?= count($comentarios ?? []) ?></span>
                </div>
                <div class="card-body">
                    <!-- Lista de Comentarios -->
                    <div class="mb-4" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($comentarios)): ?>
                            <p class="text-muted text-center py-3">No hay comentarios aún.</p>
                        <?php else: ?>
                            <?php foreach ($comentarios as $comentario): ?>
                                <div class="d-flex mb-3 <?= $comentario->es_interno ? 'ms-4' : '' ?>">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($comentario->nombre_usuario, 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card <?= $comentario->es_interno ? 'border-warning bg-light-warning' : 'bg-light' ?>">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="card-title mb-0 small fw-bold">
                                                        <?= htmlspecialchars($comentario->nombre_usuario) ?>
                                                        <?php if ($comentario->es_interno): ?>
                                                            <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock-fill"></i> Interno</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        <?= date('d/m/Y H:i', strtotime($comentario->fecha)) ?>
                                                    </small>
                                                </div>
                                                <p class="card-text small mb-0"><?= nl2br(htmlspecialchars($comentario->comentario)) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Formulario Nuevo Comentario -->
                    <?php if (in_array($soporte->estado, ['pendiente', 'en_proceso', 'en_espera'])): ?>
                        <hr>
                        <form action="<?= BASE_URL ?>soportes/agregar_comentario" method="POST">
                            <input type="hidden" name="ticket_id" value="<?= $soporte->id ?>">
                            <div class="mb-2">
                                <textarea name="comentario" class="form-control form-control-sm" rows="2" placeholder="Escribe un comentario..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if (in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="es_interno" name="es_interno" value="1">
                                        <label class="form-check-label small text-muted" for="es_interno">
                                            <i class="bi bi-lock"></i> Comentario Interno
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-send me-1"></i> Enviar
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Observaciones del Técnico</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Documente lo realizado</h6>
                    <p class="text-muted small">
                        Registre aquí las acciones realizadas, piezas reemplazadas, configuraciones modificadas y cualquier otra información relevante sobre el servicio prestado.
                    </p>
                    
                    <form action="<?= BASE_URL ?>soportes/guardar_observaciones" method="POST">
                        <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                        
                        <div class="mb-3">
                            <textarea name="observaciones" 
                                      class="form-control" 
                                      rows="6" 
                                      placeholder="Ejemplo: Se verificó fuente de poder defectuosa. Se reemplazó por fuente nueva. Equipo funciona correctamente. Se recomendó al usuario usar regulador de voltaje."
                                      <?= ($soporte->estado == 'resuelto' && $_SESSION['rol'] != 'admin') ? 'readonly' : ''?>><?= htmlspecialchars($soporte->observaciones ?? '') ?></textarea>
                            <div class="form-text">
                                <?php if ($soporte->estado == 'resuelto' && $_SESSION['rol'] == 'admin'): ?>
                                    <i class="bi bi-info-circle"></i> Ticket cerrado. Solo administradores pueden editar.
                                <?php elseif ($soporte->estado == 'resuelto'): ?>
                                    <i class="bi bi-lock"></i> Ticket cerrado. No se pueden modificar las observaciones.
                                <?php else: ?>
                                    <i class="bi bi-info-circle"></i> Las observaciones se pueden actualizar hasta que el ticket se marque como resuelto.
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($soporte->estado != 'resuelto' || $_SESSION['rol'] == 'admin'): ?>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save me-1"></i> Guardar Observaciones
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php elseif (!empty($soporte->observaciones)): ?>
                <!-- Vista solo lectura para otros roles -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Observaciones del Técnico</h5>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-3 rounded border">
                            <p class="mb-0" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($soporte->observaciones)) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna Lateral -->
        <div class="col-lg-4">
            <!-- Información del Equipo -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-pc-display me-2"></i>Equipo Afectado</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Serial</small>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($soporte->equipo_serial ?? 'N/A') ?></p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Tipo</small>
                        <p class="mb-0"><?= ucfirst(htmlspecialchars($soporte->equipo_tipo ?? 'N/A')) ?></p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Modelo</small>
                        <p class="mb-0"><?= htmlspecialchars($soporte->equipo_modelo ?? 'N/A') ?></p>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <small class="text-muted">Departamento</small>
                        <p class="mb-0"><?= htmlspecialchars($soporte->departamento_nombre ?? 'Sin asignar') ?></p>
                    </div>
                </div>
            </div>

            <!-- Repuestos y Materiales -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-tools me-2"></i>Repuestos y Materiales</h5>
                </div>
                <div class="card-body">
                    <!-- Lista de consumos -->
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

            <!-- Firma de Conformidad -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Firma de Conformidad</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($soporte->firma_usuario)): ?>
                        <div class="mb-3">
                            <img src="<?= $soporte->firma_usuario ?>" class="img-fluid border rounded" alt="Firma del Usuario">
                        </div>
                        <p class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Firmado digitalmente</p>
                    <?php else: ?>
                        <div class="mb-3 text-muted">
                            <i class="bi bi-pen display-4"></i>
                            <p class="mt-2">El usuario aún no ha firmado la conformidad del servicio.</p>
                        </div>
                        <?php if (in_array($soporte->estado, ['resuelto', 'en_proceso'])): ?>
                            <button type="button" class="btn btn-outline-dark w-100" onclick="abrirModalFirma()">
                                <i class="bi bi-pencil-square me-1"></i> Firmar Conformidad
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <hr>
                    <a href="<?= BASE_URL ?>soportes/pdf/<?= $soporte->id ?>" target="_blank" class="btn btn-danger w-100">
                        <i class="bi bi-file-pdf me-1"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>

<!-- Modal Firma -->
<div class="modal fade" id="modalFirma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-pen me-2"></i>Firma de Conformidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Por favor, firme en el recuadro de abajo para confirmar la recepción del servicio.</p>
                <div class="border rounded bg-light d-flex justify-content-center">
                    <canvas id="signature-pad" width="400" height="200" style="touch-action: none;"></canvas>
                </div>
                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFirma()">
                        <i class="bi bi-eraser me-1"></i> Limpiar
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= BASE_URL ?>soportes/guardar_firma" method="POST" id="formFirma">
                    <input type="hidden" name="ticket_id" value="<?= $soporte->id ?>">
                    <input type="hidden" name="firma_base64" id="firma_base64">
                    <button type="button" class="btn btn-primary" onclick="guardarFirma()">
                        <i class="bi bi-check-circle me-1"></i> Guardar Firma
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
let signaturePad;

function abrirModalFirma() {
    const modal = new bootstrap.Modal(document.getElementById('modalFirma'));
    modal.show();
    
    // Inicializar SignaturePad después de que el modal se muestre
    const canvas = document.getElementById('signature-pad');
    if (!signaturePad) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });
    } else {
        signaturePad.clear();
    }
    
    // Ajustar tamaño del canvas
    function resizeCanvas() {
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }
    // window.addEventListener("resize", resizeCanvas);
    // resizeCanvas();
}

function limpiarFirma() {
    if (signaturePad) {
        signaturePad.clear();
    }
}

function guardarFirma() {
    if (signaturePad.isEmpty()) {
        Swal.fire('Atención', 'Por favor proporcione una firma.', 'warning');
        return;
    }
    
    const dataUrl = signaturePad.toDataURL();
    document.getElementById('firma_base64').value = dataUrl;
    document.getElementById('formFirma').submit();
}
</script>
    </div>
</div>

<!-- Modal para Editar Fecha de Cierre -->
<div class="modal fade" id="modalEditarFechaCierre" tabindex="-1" aria-labelledby="modalEditarFechaCierreLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarFechaCierreLabel">
                    <i class="bi bi-calendar-check me-2"></i>Editar Fecha de Cierre
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>soportes/actualizar_fecha_cierre" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Esta acción actualizará la fecha de cierre del ticket. Use con precaución.
                    </div>
                    
                    <div class="mb-3">
                        <label for="nueva_fecha_cierre" class="form-label">
                            <strong>Nueva Fecha de Cierre</strong>
                        </label>
                        <input type="datetime-local" 
                               class="form-control" 
                               id="nueva_fecha_cierre" 
                               name="nueva_fecha_cierre" 
                               value="<?= $soporte->fecha_cierre ? date('Y-m-d\TH:i', strtotime($soporte->fecha_cierre)) : '' ?>" 
                               required>
                        <div class="form-text">
                            Seleccione la nueva fecha y hora de cierre del ticket.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function subirArchivo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('archivo', file);
        formData.append('ticket_id', '<?= $soporte->id ?>');

        const progressBar = document.getElementById('uploadProgressBar');
        const progressContainer = document.getElementById('uploadProgressContainer');
        
        progressContainer.classList.remove('d-none');
        progressBar.style.width = '0%';

        fetch('<?= BASE_URL ?>soportes/subir_archivo', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Archivo subido',
                    text: 'El archivo se ha adjuntado correctamente.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.error || 'Error al subir archivo', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error de conexión al subir archivo', 'error');
        })
        .finally(() => {
            progressContainer.classList.add('d-none');
            input.value = ''; // Limpiar input
        });
    }
}

function confirmarAccion(url, titulo, texto, icono, botonTexto) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: botonTexto,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

function abrirModalFechaCierre() {
    const modal = new bootstrap.Modal(document.getElementById('modalEditarFechaCierre'));
    modal.show();
}
</script>
