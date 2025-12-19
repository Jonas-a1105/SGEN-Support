<?php
// Configuración de estados
$estadoConfig = [
    'disponible' => ['class' => 'ed-status-active', 'text' => 'Disponible'],
    'en_uso' => ['class' => 'ed-status-active', 'text' => 'En Uso'],
    'nuevo' => ['class' => 'ed-status-active', 'text' => 'Nuevo'],
    'usado' => ['class' => 'ed-status-maintenance', 'text' => 'Usado'],
    'en_reparacion' => ['class' => 'ed-status-maintenance', 'text' => 'En Reparación'],
    'fuera_de_servicio' => ['class' => 'ed-status-retired', 'text' => 'Fuera de Servicio'],
    'en_reserva' => ['class' => 'ed-status-maintenance', 'text' => 'En Reserva'],
];
$estado = $equipo->estado ?? 'disponible';
$estadoInfo = $estadoConfig[$estado] ?? $estadoConfig['disponible'];

// Calcular progreso de garantía
$warrantyPercent = 0;
$warrantyStatus = 'expired';
$warrantyRemaining = '';
if (!empty($equipo->fecha_compra) && !empty($equipo->garantia)) {
    $start = strtotime($equipo->fecha_compra);
    $end = strtotime($equipo->garantia);
    $now = time();
    $total = $end - $start;
    $elapsed = $now - $start;
    
    if ($total > 0) {
        $warrantyPercent = max(0, min(100, (($total - $elapsed) / $total) * 100));
        if ($now < $end) {
            $warrantyStatus = $warrantyPercent > 33 ? 'active' : 'warning';
            $diasRestantes = ceil(($end - $now) / 86400);
            $warrantyRemaining = $diasRestantes > 365 
                ? round($diasRestantes / 365, 1) . ' años' 
                : round($diasRestantes / 30) . ' meses';
        }
    }
}

// Icono según tipo de equipo
$tipoIcon = match(strtolower($equipo->tipo ?? 'computadora')) {
    'laptop', 'portatil' => 'bi-laptop',
    'impresora' => 'bi-printer',
    'monitor' => 'bi-display',
    'telefono', 'celular' => 'bi-phone',
    'servidor' => 'bi-hdd-rack',
    'red', 'router', 'switch' => 'bi-router',
    default => 'bi-pc-display'
};
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/equipo-detail.css?v=<?= time() ?>">

<div class="ed-container">
    <div class="ed-max-w">

        <!-- TOP NAVIGATION -->
        <div class="ed-top-nav">
            <?php 
            $backUrl = BASE_URL . 'equipos';
            $backText = 'Volver a Gestión de Equipos';
            
            if (isset($_GET['from']) && $_GET['from'] === 'inventario_equipos') {
                $backUrl = BASE_URL . 'inventario?tab=equipos';
                $backText = 'Volver a Inventario (Stock)';
            }
            ?>
            <a href="<?= $backUrl ?>" class="ed-back-btn">
                <i class="bi bi-arrow-left"></i>
                <?= $backText ?>
            </a>
            <div class="ed-actions">
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" class="ed-btn-edit">
                    <i class="bi bi-pencil"></i>
                    Editar
                </a>
                <?php endif; ?>
                <div class="ed-dropdown">
                    <button class="ed-btn-more" onclick="toggleEdDropdown(event)">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="ed-dropdown-menu" id="ed-dropdown-menu">
                        <a href="<?= BASE_URL ?>equipos/historial/<?= $equipo->id ?>" class="ed-dropdown-item">
                            <i class="bi bi-clock-history"></i>
                            Ver Historial de Cambios
                        </a>
                        <a href="<?= BASE_URL ?>equipos/imprimir/<?= $equipo->id ?>" class="ed-dropdown-item" target="_blank">
                            <i class="bi bi-printer"></i>
                            Imprimir Ficha
                        </a>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                        <div class="ed-dropdown-divider"></div>
                        <a href="#" onclick="duplicarEquipo(<?= $equipo->id ?>); return false;" class="ed-dropdown-item ed-no-delete">
                            <i class="bi bi-copy"></i>
                            Duplicar Equipo
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card with Border -->
        <div class="ed-main-content-card">

        <!-- MAIN GRID -->
        <div class="ed-grid">
            
            <!-- LEFT COLUMN -->
            <div class="ed-left-col">
                
                <!-- Identity Card -->
                <div class="ed-identity-card">
                    <div class="ed-qr-decoration">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    
                    <div class="ed-equip-icon" <?= !empty($equipo->imagen) ? 'style="background-image: url('.BASE_URL.'uploads/equipos/'.$equipo->imagen.'); background-size: cover; background-position: center; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"' : '' ?>>
                        <?php if (empty($equipo->imagen)): ?>
                            <i class="bi <?= $tipoIcon ?>"></i>
                        <?php endif; ?>
                    </div>
                    
                    <span class="ed-status-badge <?= $estadoInfo['class'] ?>">
                        <span class="pulse"></span>
                        <?= $estadoInfo['text'] ?>
                    </span>

                    <h1 class="ed-equip-name"><?= htmlspecialchars(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')) ?></h1>
                    <p class="ed-equip-type"><?= ucfirst($equipo->tipo ?? 'Equipo') ?></p>

                    <div class="ed-divider"></div>

                    <div class="ed-info-row">
                        <span class="ed-info-label">Código</span>
                        <span class="ed-info-value"><?= htmlspecialchars($equipo->codigo_inventario ?? 'N/A') ?></span>
                    </div>
                    <div class="ed-info-row">
                        <span class="ed-info-label">Serial</span>
                        <span class="ed-info-value"><?= htmlspecialchars($equipo->numero_serie ?? 'N/A') ?></span>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="ed-location-card">
                    <h3 class="ed-location-title">
                        <i class="bi bi-geo-alt"></i>
                        Ubicación Actual
                    </h3>
                    
                    <div class="ed-location-item">
                        <div class="ed-location-icon blue">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <p class="ed-location-label">Departamento</p>
                            <p class="ed-location-value"><?= htmlspecialchars($equipo->departamento_nombre ?? 'No Asignado') ?></p>
                            <p class="ed-location-sub"><?= htmlspecialchars($equipo->ubicacion_fisica ?? 'Sin ubicación específica') ?></p>
                        </div>
                    </div>

                    <div class="ed-location-item">
                        <div class="ed-location-icon orange">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <p class="ed-location-label">Asignado a</p>
                            <?php if (empty($equipo->empleado_nombre) || $equipo->empleado_nombre === 'No Asignado'): ?>
                                <span class="ed-location-unassigned">-- Sin asignar --</span>
                            <?php else: ?>
                                <p class="ed-location-value"><?= htmlspecialchars($equipo->empleado_nombre) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <button class="ed-btn-location" onclick="alert('Función en desarrollo')">
                        Cambiar Ubicación / Asignación
                    </button>
                    <?php endif; ?>
                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="ed-right-col">
                
                <!-- Tabs Navigation -->
                <div class="ed-tabs-nav">
                    <button class="ed-tab-btn active" onclick="showEdTab('specs')" id="ed-tab-specs">
                        <i class="bi bi-cpu"></i>
                        Especificaciones
                    </button>
                    <button class="ed-tab-btn" onclick="showEdTab('purchase')" id="ed-tab-purchase">
                        <i class="bi bi-receipt"></i>
                        Adquisición
                    </button>
                    <button class="ed-tab-btn" onclick="showEdTab('support')" id="ed-tab-support">
                        <i class="bi bi-chat-square-text"></i>
                        Soportes
                        <?php if (!empty($soportes)): ?>
                        <span class="ed-tab-badge"><?= count($soportes) ?></span>
                        <?php endif; ?>
                    </button>
                    <button class="ed-tab-btn" onclick="showEdTab('maintenance')" id="ed-tab-maintenance">
                        <i class="bi bi-tools"></i>
                        Mantenimiento
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="ed-tab-content">
                    
                    <!-- Tab: Especificaciones -->
                    <div id="ed-panel-specs" class="ed-tab-panel active">
                        <h3 class="ed-section-title">
                            <i class="bi bi-cpu"></i>
                            Especificaciones Técnicas
                        </h3>

                        <?php if (!empty($equipo->imagen)): ?>
                        <div class="ed-image-container" style="margin-bottom: 1.5rem; text-align: center; background: #f8fafc; border-radius: 1rem; padding: 1rem; border: 1px solid #f1f5f9;">
                            <img src="<?= BASE_URL ?>uploads/equipos/<?= $equipo->imagen ?>" alt="Foto del equipo" style="max-width: 100%; max-height: 400px; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                            <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;"><i class="bi bi-zoom-in"></i> Click para ampliar</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="ed-specs-grid">
                            <div class="ed-spec-card">
                                <div class="ed-spec-icon"><i class="bi bi-cpu"></i></div>
                                <div>
                                    <p class="ed-spec-label">Procesador</p>
                                    <p class="ed-spec-value"><?= htmlspecialchars($equipo->procesador ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <div class="ed-spec-card">
                                <div class="ed-spec-icon"><i class="bi bi-memory"></i></div>
                                <div>
                                    <p class="ed-spec-label">Memoria RAM</p>
                                    <p class="ed-spec-value"><?= htmlspecialchars($equipo->memoria_ram ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <div class="ed-spec-card">
                                <div class="ed-spec-icon"><i class="bi bi-hdd"></i></div>
                                <div>
                                    <p class="ed-spec-label">Almacenamiento</p>
                                    <p class="ed-spec-value"><?= htmlspecialchars($equipo->almacenamiento ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <div class="ed-spec-card">
                                <div class="ed-spec-icon"><i class="bi bi-windows"></i></div>
                                <div>
                                    <p class="ed-spec-label">Sistema Operativo</p>
                                    <p class="ed-spec-value"><?= htmlspecialchars($equipo->sistema_operativo ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="ed-network-section">
                            <h4 class="ed-network-title">Detalles de Red</h4>
                            <div class="ed-network-grid">
                                <div>
                                    <span class="ed-network-label">Dirección IP</span>
                                    <span class="ed-network-value"><?= htmlspecialchars($equipo->direccion_ip ?? 'N/A') ?></span>
                                </div>
                                <div>
                                    <span class="ed-network-label">MAC Address</span>
                                    <span class="ed-network-value"><?= htmlspecialchars($equipo->mac_address ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Adquisición -->
                    <div id="ed-panel-purchase" class="ed-tab-panel">
                        <div class="ed-purchase-header">
                            <h3 class="ed-section-title">
                                <i class="bi bi-file-earmark-text" style="color: #059669;"></i>
                                Adquisición y Garantía
                            </h3>
                        </div>

                        <div class="ed-purchase-card">
                            <div class="ed-purchase-top">
                                <div>
                                    <p class="ed-vendor-label">Proveedor</p>
                                    <p class="ed-vendor-name"><?= htmlspecialchars($equipo->proveedor ?? 'No registrado') ?></p>
                                    <?php if (!empty($equipo->proveedor_rif)): ?>
                                    <p class="ed-order-id">RIF: <?= htmlspecialchars($equipo->proveedor_rif) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <p class="ed-price-label">Valor de Compra</p>
                                    <p class="ed-price-value"><?= $equipo->valor_compra ? '$' . number_format($equipo->valor_compra, 2) : 'N/A' ?></p>
                                </div>
                            </div>

                            <!-- Warranty Progress Bar -->
                            <?php if (!empty($equipo->garantia)): ?>
                            <div class="ed-warranty-section">
                                <div class="ed-warranty-header">
                                    <span class="ed-warranty-label">Estado de Garantía</span>
                                    <span class="ed-warranty-status <?= $warrantyStatus ?>">
                                        <?php if ($warrantyStatus === 'expired'): ?>
                                            Expirada
                                        <?php else: ?>
                                            Activa (<?= $warrantyRemaining ?> restantes)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="ed-warranty-bar">
                                    <div class="ed-warranty-fill <?= $warrantyStatus ?>" style="width: <?= $warrantyPercent ?>%"></div>
                                </div>
                                <div class="ed-warranty-dates">
                                    <span>Inicio: <?= $equipo->fecha_compra ? date('d M Y', strtotime($equipo->fecha_compra)) : 'N/A' ?></span>
                                    <span>Fin: <?= date('d M Y', strtotime($equipo->garantia)) ?></span>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="ed-warranty-section">
                                <p style="text-align: center; color: #94a3b8; margin: 0;">Sin información de garantía</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tab: Soportes -->
                    <div id="ed-panel-support" class="ed-tab-panel">
                        <?php if (empty($soportes)): ?>
                        <div class="ed-empty-state">
                            <div class="ed-empty-icon">
                                <i class="bi bi-chat-square-text"></i>
                            </div>
                            <h3 class="ed-empty-title">Sin tickets activos</h3>
                            <p class="ed-empty-text">Este equipo funciona correctamente. No hay reportes de soporte pendientes.</p>
                            <a href="<?= BASE_URL ?>soportes/crear?equipo_id=<?= $equipo->id ?>" class="ed-btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                Crear Ticket
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="ed-tickets-list">
                            <?php foreach ($soportes as $s): ?>
                            <?php
                            $sEstado = match($s->estado) {
                                'pendiente' => ['bg' => '#fef3c7', 'color' => '#d97706'],
                                'en_proceso' => ['bg' => '#dbeafe', 'color' => '#2563eb'],
                                'en_espera' => ['bg' => '#f1f5f9', 'color' => '#64748b'],
                                'resuelto' => ['bg' => '#d1fae5', 'color' => '#059669'],
                                default => ['bg' => '#f1f5f9', 'color' => '#64748b']
                            };
                            ?>
                            <div class="ed-ticket-item">
                                <div>
                                    <span class="ed-ticket-id">#<?= $s->id ?></span>
                                    <span class="ed-ticket-desc">- <?= htmlspecialchars(substr($s->descripcion, 0, 40)) ?><?= strlen($s->descripcion) > 40 ? '...' : '' ?></span>
                                    <div class="ed-ticket-meta">
                                        <span class="ed-ticket-date"><?= date('d/m/Y H:i', strtotime($s->fecha)) ?></span>
                                        <span class="ed-ticket-status" style="background: <?= $sEstado['bg'] ?>; color: <?= $sEstado['color'] ?>;">
                                            <?= ucfirst(str_replace('_', ' ', $s->estado)) ?>
                                        </span>
                                    </div>
                                </div>
                                <a href="<?= BASE_URL ?>soportes/ver/<?= $s->id ?>" class="ed-ticket-link">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="ed-tickets-footer">
                            <a href="<?= BASE_URL ?>soportes/crear?equipo_id=<?= $equipo->id ?>" class="ed-btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                Crear Ticket
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Mantenimiento -->
                    <div id="ed-panel-maintenance" class="ed-tab-panel">
                        <div class="ed-maint-header">
                            <h3 class="ed-section-title">
                                <i class="bi bi-tools" style="color: #d97706;"></i>
                                Historial de Mantenimiento
                            </h3>
                            <?php 
                            // Buscar próximo mantenimiento programado
                            $proximoMant = null;
                            if (!empty($mantenimientos)) {
                                foreach ($mantenimientos as $m) {
                                    if (!empty($m->proxima_fecha) && strtotime($m->proxima_fecha) > time()) {
                                        $proximoMant = $m->proxima_fecha;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <?php if ($proximoMant): ?>
                            <span class="ed-next-maint">
                                Próximo: <?= date('d M', strtotime($proximoMant)) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (empty($mantenimientos)): ?>
                        <div class="ed-empty-state">
                            <div class="ed-empty-icon">
                                <i class="bi bi-tools"></i>
                            </div>
                            <h3 class="ed-empty-title">Sin historial de mantenimiento</h3>
                            <p class="ed-empty-text">No hay mantenimientos registrados para este equipo.</p>
                            <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                            <a href="<?= BASE_URL ?>mantenimientos/crear?equipo_id=<?= $equipo->id ?>" class="ed-btn-dark">
                                <i class="bi bi-plus-lg"></i>
                                Nuevo Mantenimiento
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="ed-timeline">
                            <?php foreach (array_slice($mantenimientos, 0, 5) as $m): ?>
                            <div class="ed-timeline-item">
                                <div class="ed-timeline-dot"></div>
                                <p class="ed-timeline-date"><?= date('d M Y', strtotime($m->fecha)) ?></p>
                                <div class="ed-timeline-card">
                                    <h4 class="ed-timeline-title"><?= ucfirst($m->tipo_mantenimiento ?? 'Mantenimiento') ?></h4>
                                    <p class="ed-timeline-desc"><?= htmlspecialchars(substr($m->descripcion ?? '', 0, 100)) ?></p>
                                    <?php if (!empty($m->realizado_por)): ?>
                                    <span class="ed-timeline-user">
                                        <i class="bi bi-person"></i>
                                        Realizado por: <?= htmlspecialchars($m->realizado_por) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="ed-maint-footer">
                            <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                            <a href="<?= BASE_URL ?>mantenimientos/crear?equipo_id=<?= $equipo->id ?>" class="ed-btn-dark">
                                <i class="bi bi-plus-lg"></i>
                                Nuevo Mantenimiento
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </div>

        </div><!-- End ed-main-content-card -->
    </div>
</div>

<script>
function showEdTab(tabId) {
    // Hide all panels
    document.querySelectorAll('.ed-tab-panel').forEach(p => p.classList.remove('active'));
    
    // Reset all tabs
    document.querySelectorAll('.ed-tab-btn').forEach(t => t.classList.remove('active'));
    
    // Show selected panel
    document.getElementById('ed-panel-' + tabId).classList.add('active');
    
    // Activate selected tab
    document.getElementById('ed-tab-' + tabId).classList.add('active');
}

// Dropdown toggle
function toggleEdDropdown(event) {
    event.stopPropagation();
    const menu = document.getElementById('ed-dropdown-menu');
    menu.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.ed-dropdown');
    const menu = document.getElementById('ed-dropdown-menu');
    if (dropdown && !dropdown.contains(e.target)) {
        menu.classList.remove('show');
    }
});

// Duplicate equipment
function duplicarEquipo(id) {
    Swal.fire({
        title: '¿Duplicar equipo?',
        text: 'Se creará una copia de este equipo en el mismo departamento.',
        icon: 'question',
        iconColor: '#4f46e5',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= BASE_URL ?>equipos/duplicar/' + id;
        }
    });
}
</script>
