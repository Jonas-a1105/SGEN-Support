<?php
// Determinar si es una creación o una edición
$es_edicion = ($soporte !== null && $soporte->id);
$valor_id = $es_edicion ? $soporte->id : '';
$valor_equipo_id = $es_edicion ? $soporte->equipo_id : '';
$valor_descripcion = $es_edicion ? $soporte->descripcion : '';
$valor_prioridad = $es_edicion ? $soporte->prioridad : 'media';
$valor_categoria = $es_edicion ? $soporte->categoria_id : '';
$valor_estado = $es_edicion ? $soporte->estado : 'abierto';

// Obtener info del equipo si es edición
$equipo_info = null;
if ($es_edicion && $valor_equipo_id) {
    foreach ($equipo_list as $eq) {
        if ($eq->id == $valor_equipo_id) {
            $equipo_info = $eq;
            break;
        }
    }
}

// Estados disponibles
$estados = [
    'abierto' => ['label' => 'Abierto', 'color' => 'blue'],
    'en_proceso' => ['label' => 'En Progreso', 'color' => 'amber'],
    'en_espera' => ['label' => 'En Espera', 'color' => 'orange'],
    'resuelto' => ['label' => 'Resuelto', 'color' => 'emerald'],
    'cerrado' => ['label' => 'Cerrado', 'color' => 'slate'],
];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/ticket-form.css?v=<?= time() ?>">

<div class="tf-container">
    <div class="tf-max-w" style="width: 1150px; max-width: 100%;">
        
        <!-- HEADER -->
        <?php if ($es_edicion): ?>
        <!-- Edit Mode Header -->
        <div class="tf-header-edit">
            <div class="tf-header-left">
                <a href="<?= BASE_URL ?>soportes" class="tf-back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <div class="tf-header-title-row">
                        <h1 class="tf-title">Ticket #<?= $soporte->id ?></h1>
                        <span class="tf-status-badge tf-status-<?= $valor_estado ?>">
                            <?= $estados[$valor_estado]['label'] ?? ucfirst($valor_estado) ?>
                        </span>
                    </div>
                    <p class="tf-subtitle">
                        Creado el <?= date('d M Y', strtotime($soporte->fecha_apertura ?? 'now')) ?> 
                        <?php if (!empty($soporte->updated_at)): ?>
                        • Última actualización <?= date('d/m/Y H:i', strtotime($soporte->updated_at)) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="tf-header-actions">
                <a href="<?= BASE_URL ?>soportes/eliminar/<?= $soporte->id ?>" 
                   class="tf-action-btn tf-action-delete"
                   data-no-global-delete="true"
                   onclick="event.preventDefault(); DeleteModal.open(this.href, {
                       id: '#T-<?= $soporte->id ?>',
                       title: '<?= addslashes(htmlspecialchars(str_replace(["\r", "\n"], ' ', substr($soporte->descripcion ?? '', 0, 60)))) ?>...',
                       author: '<?= addslashes(htmlspecialchars($soporte->usuario_nombre ?? 'Sin solicitante')) ?>'
                   })">
                    <i class="bi bi-trash"></i>
                    Eliminar
                </a>
                <a href="<?= BASE_URL ?>bitacora?tipo=soporte&id=<?= $soporte->id ?>" class="tf-action-btn">
                    <i class="bi bi-clock-history"></i>
                    Historial
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Create Mode Header -->
        <div class="tf-header">
            <a href="<?= BASE_URL ?>soportes" class="tf-back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="tf-title">Nuevo Ticket de Soporte</h1>
                <p class="tf-subtitle">Reporta una incidencia técnica para su resolución.</p>
            </div>
        </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>soportes/guardar" method="POST" id="ticketForm">
            <input type="hidden" name="id" value="<?= htmlspecialchars($valor_id) ?>">
            <input type="hidden" id="equipo_id" name="equipo_id" value="<?= htmlspecialchars($valor_equipo_id) ?>" required>
            <input type="hidden" id="departamento_id" name="departamento_id" value="">

            <!-- Contenedor principal con bordes -->
            <div class="tf-main-container">
            <div class="tf-grid">
                
                <!-- LEFT COLUMN: DEVICE CONTEXT -->
                <div class="tf-left-col">
                    
                    <?php if ($es_edicion && $equipo_info): ?>
                    <!-- Edit Mode: Device Info Card -->
                    <div class="tf-device-card-static">
                        <div class="tf-device-header-static">
                            <span class="tf-device-header-label">
                                <i class="bi bi-display"></i> Activo Afectado
                            </span>
                            <button type="button" class="tf-change-device-btn" onclick="toggleDeviceSearch()">
                                <span id="change_device_text">Cambiar</span>
                            </button>
                        </div>
                        
                        <div id="device_info_static" class="tf-device-body-static">
                            <div class="tf-device-icon-static">
                                <?php
                                    $tipo = strtolower($equipo_info->tipo ?? '');
                                    $icon = 'bi-pc-display';
                                    if (strpos($tipo, 'laptop') !== false) $icon = 'bi-laptop';
                                    elseif (strpos($tipo, 'impresora') !== false) $icon = 'bi-printer';
                                    elseif (strpos($tipo, 'servidor') !== false) $icon = 'bi-hdd-rack';
                                ?>
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <h4 class="tf-device-name-static"><?= htmlspecialchars($equipo_info->marca . ' ' . $equipo_info->modelo) ?></h4>
                            <p class="tf-device-code-static"><?= htmlspecialchars($equipo_info->codigo_inventario ?? $equipo_info->numero_serie) ?></p>
                            
                            <div class="tf-device-details-static">
                                <div class="tf-device-detail-row">
                                    <i class="bi bi-building"></i>
                                    <span><?= htmlspecialchars($equipo_info->departamento_nombre ?? 'Sin Asignar') ?></span>
                                </div>
                                <div class="tf-device-detail-row">
                                    <i class="bi bi-tag"></i>
                                    <span><?= strtoupper($equipo_info->tipo ?? 'EQUIPO') ?></span>
                                </div>
                                <div class="tf-device-detail-row">
                                    <i class="bi bi-person-fill"></i>
                                    <span>
                                        <?php if (!empty($equipo_info->empleado_nombre)): ?>
                                            <?= htmlspecialchars($equipo_info->empleado_nombre) ?>
                                            <?php if (!empty($equipo_info->empleado_cargo)): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($equipo_info->empleado_cargo) ?>)</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em class="text-muted">Sin asignar</em>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden search panel -->
                        <div id="device_search_panel" class="tf-device-search-panel" style="display: none;">
                            <label class="tf-search-label">Buscar nuevo equipo</label>
                            <div class="tf-search-wrapper">
                                <input type="text" id="busqueda_equipo" class="tf-search-input" placeholder="Código / Serial...">
                                <button type="button" id="btn_buscar_equipo" class="tf-search-btn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <p id="mensaje_busqueda" class="tf-search-error">Equipo no encontrado.</p>
                        </div>
                    </div>
                    
                    <!-- Reported By Card (Edit Mode Only) -->
                    <div class="tf-info-card">
                        <h3 class="tf-info-card-label">Reportado Por</h3>
                        <div class="tf-reporter-info">
                            <div class="tf-reporter-avatar">
                                <?= strtoupper(substr($soporte->usuario_nombre ?? 'U', 0, 2)) ?>
                            </div>
                            <div class="tf-reporter-details">
                                <p class="tf-reporter-name"><?= htmlspecialchars($soporte->usuario_nombre ?? 'Usuario') ?></p>
                                <p class="tf-reporter-role"><?= htmlspecialchars($soporte->departamento_nombre ?? '') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Technician Info Card (Edit Mode Only) -->
                     <div class="tf-info-card" style="margin-top: 1rem;">
                        <h3 class="tf-info-card-label">Técnico Asignado</h3>
                        <div class="tf-reporter-info">
                            <?php if (!empty($soporte->tecnico_asignado)): ?>
                                <div class="tf-reporter-avatar" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                                    <?= strtoupper(substr($soporte->tecnico_username ?? 'T', 0, 2)) ?>
                                </div>
                                <div class="tf-reporter-details">
                                    <p class="tf-reporter-name"><?= htmlspecialchars($soporte->tecnico_asignado) ?></p>
                                    <p class="tf-reporter-role">Soporte Técnico</p>
                                </div>
                            <?php else: ?>
                                <div class="tf-reporter-avatar" style="background: #f1f5f9; color: #94a3b8;">
                                    <i class="bi bi-person-x-fill"></i>
                                </div>
                                <div class="tf-reporter-details">
                                    <p class="tf-reporter-name" style="color: #94a3b8;">Sin asignar</p>
                                    <p class="tf-reporter-role">Pendiente</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- Create Mode: Search Box -->
                    <div class="tf-search-card">
                        <label class="tf-search-label">Buscar Equipo Afectado</label>
                        <div class="tf-search-wrapper">
                            <i class="bi bi-search tf-search-icon"></i>
                            <input 
                                type="text" 
                                id="busqueda_equipo"
                                class="tf-search-input" 
                                placeholder="Serial, Código o Cédula..."
                            >
                            <button type="button" id="btn_buscar_equipo" class="tf-search-btn">Buscar</button>
                        </div>
                        <p class="tf-search-hint">Ingresa el identificador del activo para vincularlo al ticket.</p>
                        <p id="mensaje_busqueda" class="tf-search-error">Equipo no encontrado.</p>
                    </div>

                    <!-- Device Card (hidden by default for create mode) -->
                    <div id="device_card" class="tf-device-card" style="display: none;">
                        <div class="tf-device-header">
                            <span class="tf-device-badge">
                                <i class="bi bi-check-circle-fill"></i>
                                Equipo Seleccionado
                            </span>
                            <button type="button" class="tf-device-close" onclick="limpiarEquipo()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="tf-device-body">
                            <div class="tf-device-icon">
                                <i class="bi bi-pc-display" id="device_icon"></i>
                            </div>
                            <h3 id="device_name" class="tf-device-name">Dell Optiplex</h3>
                            <p id="device_serial" class="tf-device-code">S/N: 1234567</p>
                            
                            <div class="tf-device-details">
                                <div class="tf-device-detail-item">
                                    <i class="bi bi-building tf-device-detail-icon"></i>
                                    <div>
                                        <p class="tf-device-detail-label">Departamento</p>
                                        <p id="device_dept" class="tf-device-detail-value">-</p>
                                    </div>
                                </div>
                                <div class="tf-device-detail-item">
                                    <i class="bi bi-geo-alt tf-device-detail-icon"></i>
                                    <div>
                                        <p class="tf-device-detail-label">Tipo</p>
                                        <p id="device_type" class="tf-device-detail-value">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State (Create Mode) -->
                    <div id="empty_device" class="tf-empty-device">
                        <div class="tf-empty-icon">
                            <i class="bi bi-display"></i>
                        </div>
                        <p class="tf-empty-title">Ningún equipo seleccionado</p>
                        <p class="tf-empty-text">Realiza una búsqueda para cargar los datos.</p>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- RIGHT COLUMN: THE FORM -->
                <div class="tf-form-card">
                    
                    <?php if ($es_edicion): ?>
                    <!-- Status Workflow (Edit Mode Only) -->
                    <div class="tf-status-section">
                        <label class="tf-status-section-label">Estado del Ticket</label>
                        <div class="tf-status-buttons">
                            <?php foreach ($estados as $key => $estado): ?>
                            <button type="button" 
                                    class="tf-status-btn <?= ($valor_estado == $key) ? 'active' : '' ?>"
                                    data-status="<?= $key ?>"
                                    onclick="selectStatus(this, '<?= $key ?>')">
                                <span class="tf-status-dot tf-status-dot-<?= $estado['color'] ?>"></span>
                                <?= $estado['label'] ?>
                                <?php if ($valor_estado == $key): ?>
                                <i class="bi bi-check-circle-fill tf-status-check"></i>
                                <?php endif; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="estado" name="estado" value="<?= htmlspecialchars($valor_estado) ?>">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Section: Details -->
                    <div class="tf-section">
                        <div class="tf-section-header">
                            <span class="tf-section-number"><?= $es_edicion ? '' : '1' ?></span>
                            <h3 class="tf-section-title">Detalles del Incidente</h3>
                        </div>

                        <div class="tf-two-col">
                            <!-- Category Selection -->
                            <div>
                                <label class="tf-field-label">Categoría del Problema</label>
                                <div class="tf-category-grid">
                                    <?php foreach ($categoria_list as $cat): ?>
                                    <?php 
                                        $icon = 'bi-question-circle';
                                        $catName = strtolower($cat->nombre);
                                        if (strpos($catName, 'hardware') !== false) $icon = 'bi-cpu';
                                        elseif (strpos($catName, 'software') !== false) $icon = 'bi-file-earmark-code';
                                        elseif (strpos($catName, 'red') !== false || strpos($catName, 'internet') !== false) $icon = 'bi-wifi';
                                        elseif (strpos($catName, 'periferico') !== false || strpos($catName, 'periférico') !== false) $icon = 'bi-mouse';
                                        elseif (strpos($catName, 'impresora') !== false) $icon = 'bi-printer';
                                        elseif (strpos($catName, 'correo') !== false || strpos($catName, 'email') !== false) $icon = 'bi-envelope';
                                    ?>
                                    <button 
                                        type="button" 
                                        class="tf-category-btn <?= ($valor_categoria == $cat->id) ? 'active' : '' ?>" 
                                        data-category="<?= $cat->id ?>"
                                        onclick="selectCategory(this, <?= $cat->id ?>)"
                                    >
                                        <i class="bi <?= $icon ?>"></i>
                                        <span><?= htmlspecialchars($cat->nombre) ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" id="categoria_id" name="categoria_id" value="<?= htmlspecialchars($valor_categoria) ?>">
                            </div>

                            <!-- Priority Selection -->
                            <div>
                                <label class="tf-field-label">Nivel de Prioridad</label>
                                <div class="tf-priority-list">
                                    <button type="button" class="tf-priority-btn low <?= ($valor_prioridad == 'baja') ? 'active' : '' ?>" onclick="selectPriority(this, 'baja')">
                                        <i class="bi bi-clock"></i>
                                        <span>Baja</span>
                                        <i class="bi bi-check-circle-fill tf-priority-check"></i>
                                    </button>
                                    <button type="button" class="tf-priority-btn normal <?= ($valor_prioridad == 'media') ? 'active' : '' ?>" onclick="selectPriority(this, 'media')">
                                        <i class="bi bi-check-circle"></i>
                                        <span>Media</span>
                                        <i class="bi bi-check-circle-fill tf-priority-check"></i>
                                    </button>
                                    <button type="button" class="tf-priority-btn high <?= ($valor_prioridad == 'alta') ? 'active' : '' ?>" onclick="selectPriority(this, 'alta')">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <span>Alta</span>
                                        <i class="bi bi-check-circle-fill tf-priority-check"></i>
                                    </button>
                                    <button type="button" class="tf-priority-btn critical <?= ($valor_prioridad == 'critica') ? 'active' : '' ?>" onclick="selectPriority(this, 'critica')">
                                        <i class="bi bi-lightning"></i>
                                        <span>Crítica</span>
                                        <i class="bi bi-check-circle-fill tf-priority-check"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="prioridad" name="prioridad" value="<?= htmlspecialchars($valor_prioridad) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="tf-divider"></div>

                    <!-- Section: Description + Resolution (side by side when applicable) -->
                    <div class="tf-section">
                        <div class="tf-section-header">
                            <span class="tf-section-number"><?= $es_edicion ? '' : '2' ?></span>
                            <h3 class="tf-section-title">Descripción<?= ($es_edicion && in_array($valor_estado, ['resuelto', 'cerrado'])) ? ' y Resolución' : '' ?></h3>
                        </div>
                        
                        <?php if ($es_edicion && in_array($valor_estado, ['resuelto', 'cerrado'])): ?>
                        <!-- Two column layout for Description + Resolution -->
                        <div class="tf-description-resolution-grid">
                            <div class="tf-description-col">
                                <label class="tf-inline-label">Problema Reportado</label>
                                <div class="tf-textarea-wrapper">
                                    <textarea 
                                        id="descripcion" 
                                        name="descripcion" 
                                        class="tf-textarea tf-textarea-compact" 
                                        placeholder="Describe detalladamente qué está fallando..."
                                        required
                                    ><?= htmlspecialchars($valor_descripcion) ?></textarea>
                                </div>
                            </div>
                            <div class="tf-resolution-col">
                                <label class="tf-inline-label tf-resolution-inline-label">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Nota de Resolución
                                </label>
                                <div class="tf-textarea-wrapper">
                                    <textarea 
                                        id="resolucion" 
                                        name="resolucion" 
                                        class="tf-textarea tf-textarea-compact tf-textarea-resolution"
                                        placeholder="Describe cómo se solucionó..."
                                    ><?= htmlspecialchars($soporte->observaciones ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Single column for Description only -->
                        <div class="tf-textarea-wrapper">
                            <textarea 
                                id="descripcion" 
                                name="descripcion" 
                                class="tf-textarea" 
                                placeholder="Describe detalladamente qué está fallando..."
                                required
                            ><?= htmlspecialchars($valor_descripcion) ?></textarea>
                            <i class="bi bi-chat-square-text tf-textarea-icon"></i>
                        </div>
                        <p class="tf-textarea-hint">Mínimo 20 caracteres.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="tf-actions">
                        <a href="<?= BASE_URL ?>soportes" class="tf-btn-cancel">
                            <?= $es_edicion ? 'Descartar Cambios' : 'Cancelar' ?>
                        </a>
                        <button type="submit" class="tf-btn-save">
                            <i class="bi bi-check-lg"></i>
                            <?= $es_edicion ? 'Actualizar' : 'Guardar' ?> Ticket
                        </button>
                    </div>

                </div>

            </div>
            </div><!-- Cierre tf-main-container -->
        </form>

    </div>
</div>

<!-- Modal para seleccionar equipo cuando hay múltiples -->
<div class="modal fade" id="modalSeleccionEquipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 1rem; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9;">
                <h5 class="modal-title" style="font-weight: 700;">Seleccionar Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color: #64748b; margin-bottom: 1rem;">El empleado tiene múltiples equipos asignados. Seleccione uno:</p>
                <div id="lista_equipos" class="tf-modal-list">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnBuscar = document.getElementById('btn_buscar_equipo');
    const inputBusqueda = document.getElementById('busqueda_equipo');
    const mensajeBusqueda = document.getElementById('mensaje_busqueda');
    
    const inputEquipoId = document.getElementById('equipo_id');
    const inputDeptoId = document.getElementById('departamento_id');
    
    const deviceCard = document.getElementById('device_card');
    const emptyDevice = document.getElementById('empty_device');

    // Category selection
    window.selectCategory = function(btn, id) {
        document.querySelectorAll('.tf-category-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('categoria_id').value = id;
    };

    // Priority selection
    window.selectPriority = function(btn, value) {
        document.querySelectorAll('.tf-priority-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('prioridad').value = value;
    };

    // Status selection (Edit mode)
    window.selectStatus = function(btn, value) {
        document.querySelectorAll('.tf-status-btn').forEach(b => {
            b.classList.remove('active');
            b.querySelector('.tf-status-check')?.remove();
        });
        btn.classList.add('active');
        const check = document.createElement('i');
        check.className = 'bi bi-check-circle-fill tf-status-check';
        btn.appendChild(check);
        document.getElementById('estado').value = value;
        
        // Show/hide resolution section based on status
        const resSection = document.querySelector('.tf-resolution-section');
        if (resSection) {
            resSection.style.display = (value === 'resuelto' || value === 'cerrado') ? 'block' : 'none';
        }
    };

    // Toggle device search (Edit mode)
    window.toggleDeviceSearch = function() {
        const panel = document.getElementById('device_search_panel');
        const info = document.getElementById('device_info_static');
        const text = document.getElementById('change_device_text');
        
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            info.style.display = 'none';
            text.textContent = 'Cancelar';
        } else {
            panel.style.display = 'none';
            info.style.display = 'block';
            text.textContent = 'Cambiar';
        }
    };

    // Fill device card
    function llenarCamposEquipo(data) {
        inputEquipoId.value = data.id;
        if (inputDeptoId) inputDeptoId.value = data.departamento_id || '';
        
        const deviceNameEl = document.getElementById('device_name');
        const deviceSerialEl = document.getElementById('device_serial');
        const deviceDeptEl = document.getElementById('device_dept');
        const deviceTypeEl = document.getElementById('device_type');
        
        if (deviceNameEl) deviceNameEl.textContent = `${data.marca} ${data.modelo}`;
        if (deviceSerialEl) deviceSerialEl.textContent = `S/N: ${data.serial}`;
        if (deviceDeptEl) deviceDeptEl.textContent = data.departamento_nombre || 'Sin Asignar';
        if (deviceTypeEl) deviceTypeEl.textContent = data.tipo ? data.tipo.toUpperCase() : 'EQUIPO';
        
        // Set icon based on type
        const iconEl = document.getElementById('device_icon');
        if (iconEl) {
            iconEl.className = 'bi ';
            const tipo = (data.tipo || '').toLowerCase();
            if (tipo.includes('laptop') || tipo.includes('portatil')) {
                iconEl.classList.add('bi-laptop');
            } else if (tipo.includes('impresora')) {
                iconEl.classList.add('bi-printer');
            } else if (tipo.includes('servidor')) {
                iconEl.classList.add('bi-hdd-rack');
            } else {
                iconEl.classList.add('bi-pc-display');
            }
        }
        
        if (deviceCard) deviceCard.style.display = 'block';
        if (emptyDevice) emptyDevice.style.display = 'none';
        if (mensajeBusqueda) mensajeBusqueda.classList.remove('show');
        
        // For edit mode, close search panel
        const panel = document.getElementById('device_search_panel');
        if (panel) {
            panel.style.display = 'none';
            const info = document.getElementById('device_info_static');
            if (info) info.style.display = 'block';
            const text = document.getElementById('change_device_text');
            if (text) text.textContent = 'Cambiar';
        }
    }

    window.limpiarEquipo = function() {
        inputEquipoId.value = '';
        if (inputDeptoId) inputDeptoId.value = '';
        if (deviceCard) deviceCard.style.display = 'none';
        if (emptyDevice) emptyDevice.style.display = 'block';
    };

    function mostrarModalSeleccion(equipos) {
        const listaEquipos = document.getElementById('lista_equipos');
        listaEquipos.innerHTML = '';
        
        equipos.forEach(equipo => {
            const item = document.createElement('div');
            item.className = 'tf-modal-item';
            item.innerHTML = `
                <p class="tf-modal-item-title">${equipo.tipo.toUpperCase()} - ${equipo.marca} ${equipo.modelo}</p>
                <p class="tf-modal-item-subtitle">Serial: ${equipo.serial} • ${equipo.departamento_nombre || 'Sin Asignar'}</p>
            `;
            item.addEventListener('click', function() {
                llenarCamposEquipo(equipo);
                const modalEl = document.getElementById('modalSeleccionEquipo');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
            listaEquipos.appendChild(item);
        });
        
        const modalEl = document.getElementById('modalSeleccionEquipo');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    if (btnBuscar) {
        btnBuscar.addEventListener('click', function() {
            const query = inputBusqueda.value.trim();
            if (!query) return;

            fetch('<?= BASE_URL ?>equipos/apiBuscar?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if (data.found) {
                        if (mensajeBusqueda) mensajeBusqueda.classList.remove('show');
                        
                        if (data.multiple) {
                            mostrarModalSeleccion(data.equipos);
                        } else {
                            llenarCamposEquipo(data);
                        }
                    } else {
                        if (mensajeBusqueda) {
                            mensajeBusqueda.textContent = data.error || 'Equipo no encontrado.';
                            mensajeBusqueda.classList.add('show');
                        }
                        limpiarEquipo();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (mensajeBusqueda) {
                        mensajeBusqueda.textContent = 'Error al buscar equipo.';
                        mensajeBusqueda.classList.add('show');
                    }
                });
        });
    }

    // Allow Enter key to search
    if (inputBusqueda) {
        inputBusqueda.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnBuscar.click();
            }
        });
    }
});
</script>