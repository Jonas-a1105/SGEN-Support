<?php
/**
 * Vista de Lista de Equipos - Diseño Moderno
 * Incluye: KPIs, filtros, búsqueda, vista grid/lista
 */

// Leer preferencia de paginación
$cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
$paginationPerPage = $cookiePerPage;

// Calcular estadísticas
$stats = [
    'total' => count($equipos),
    'disponible' => 0,
    'en_uso' => 0,
    'en_reparacion' => 0,
    'fuera_de_servicio' => 0
];

foreach ($equipos as $e) {
    if (isset($stats[$e->estado])) {
        $stats[$e->estado]++;
    }
}

// Helper para íconos según tipo de equipo
function getEquipoIcon($tipo) {
    $tipo = strtolower($tipo);
    if (strpos($tipo, 'laptop') !== false) return 'bi-laptop';
    if (strpos($tipo, 'impresora') !== false || strpos($tipo, 'printer') !== false) return 'bi-printer';
    if (strpos($tipo, 'tablet') !== false || strpos($tipo, 'telefono') !== false || strpos($tipo, 'movil') !== false) return 'bi-phone';
    if (strpos($tipo, 'servidor') !== false || strpos($tipo, 'server') !== false) return 'bi-hdd-rack';
    return 'bi-pc-display';
}

// Helper para badge de estado
function getStatusBadge($estado) {
    $estados = [
        'disponible' => ['icon' => 'bi-check-circle-fill', 'text' => 'Disponible'],
        'en_uso' => ['icon' => 'bi-person-check', 'text' => 'En Uso'],
        'en_reparacion' => ['icon' => 'bi-tools', 'text' => 'En Reparación'],
        'fuera_de_servicio' => ['icon' => 'bi-x-circle-fill', 'text' => 'Fuera de Servicio']
    ];
    $info = $estados[$estado] ?? ['icon' => 'bi-question-circle', 'text' => ucfirst(str_replace('_', ' ', $estado))];
    return $info;
}
?>



<div class="equipos-container" data-per-page="<?= $paginationPerPage ?>">
    
    <!-- Header -->
    <div class="equipos-header">
        <div class="equipos-header-container">
            <div class="d-flex align-items-center gap-2">
                <div class="equipos-logo">
                    <i class="bi bi-pc-display-horizontal"></i>
                </div>
                <span class="equipos-title">Gestión de Equipos <span>Tecnológicos</span></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>departamentos" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex align-items-center gap-2">
                    <i class="bi bi-building"></i> Departamentos
                </a>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>equipos/crear" class="btn-nuevo-equipo">
                        <i class="bi bi-plus-lg"></i>
                        <span>Nuevo Equipo</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <main id="equiposMain" class="equipos-main-container">
        
        <!-- Main Content Card Container -->
        <div class="equipos-content-card">
        
        <!-- KPI Cards -->
        <div class="kpi-grid mb-4">
            <div class="kpi-card total">
                <p class="kpi-label">Total Activos</p>
                <p class="kpi-value"><?= $stats['total'] ?></p>
                <i class="bi bi-box-seam kpi-icon indigo"></i>
            </div>
            <div class="kpi-card operativos">
                <p class="kpi-label">Operativos</p>
                <p class="kpi-value emerald"><?= $stats['en_uso'] + $stats['disponible'] ?></p>
                <i class="bi bi-check-circle kpi-icon emerald"></i>
            </div>
            <div class="kpi-card reparacion">
                <p class="kpi-label">En Reparación</p>
                <p class="kpi-value amber"><?= $stats['en_reparacion'] ?></p>
                <i class="bi bi-tools kpi-icon amber"></i>
                <?php if ($stats['en_reparacion'] > 0): ?>
                    <p class="kpi-subtitle">Requieren atención</p>
                <?php endif; ?>
            </div>
            <div class="kpi-card baja">
                <p class="kpi-label">Fuera de Servicio</p>
                <p class="kpi-value slate"><?= $stats['fuera_de_servicio'] ?></p>
                <i class="bi bi-x-circle kpi-icon rose"></i>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="equipos-toolbar">
            <!-- Left: Bulk Controls + Filter Tabs -->
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                <!-- Bulk Delete Controls - Solo Admin -->
                <div class="bulk-controls">
                    <div class="form-check form-switch mb-0" title="Activar selección múltiple">
                        <input class="form-check-input bulk-toggle" type="checkbox" id="bulkModeToggle" style="cursor: pointer; width: 3em; height: 1.5em;">
                    </div>
                    <div id="bulkSelectAllContainer" class="bulk-select-all">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bulkSelectAll" style="cursor: pointer; border-color: #cbd5e1;">
                            <label class="form-check-label text-muted fs-sm user-select-none" for="bulkSelectAll" style="cursor: pointer;">Todo</label>
                        </div>
                    </div>
                    <button id="bulkDeleteBtn" class="bulk-delete-btn">
                        <i class="bi bi-trash"></i>
                        <span id="bulkSelectedCount">0</span> seleccionados
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="todos">Todos</button>
                    <button class="filter-tab" data-filter="disponible">Disponible</button>
                    <button class="filter-tab" data-filter="en_uso">En Uso</button>
                    <button class="filter-tab" data-filter="en_reparacion">Reparación</button>
                    <button class="filter-tab" data-filter="fuera_de_servicio">Baja</button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Search -->
                <div class="search-box">
                    <input type="text" id="searchEquipos" placeholder="Buscar equipo...">
                    <i class="bi bi-search"></i>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle">
                    <button id="btnViewList" title="Vista Lista">
                        <i class="bi bi-list-ul"></i>
                    </button>
                    <button id="btnViewGrid" class="active" title="Vista Grid">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div id="gridView" class="equipos-grid">
            <?php foreach ($equipos as $e): 
                $statusInfo = getStatusBadge($e->estado);
            ?>
                <div class="equipo-card" 
                     data-bulk-item
                     data-equipo-id="<?= $e->id ?>"
                     data-estado="<?= $e->estado ?>">
                    <!-- Bulk Checkbox -->
                    <div class="bulk-checkbox">
                        <i class="bi bi-check-circle-fill icon-checked"></i>
                        <i class="bi bi-circle icon-unchecked"></i>
                    </div>
                    <div class="equipo-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="equipo-card-icon" <?= !empty($e->imagen) ? 'style="background-image: url('.BASE_URL.'uploads/equipos/'.$e->imagen.'); background-size: cover; background-position: center; border: 1px solid #e2e8f0;"' : '' ?>>
                                <?php if (empty($e->imagen)): ?>
                                    <i class="bi <?= getEquipoIcon($e->tipo) ?>"></i>
                                <?php endif; ?>
                            </div>
                            <a href="<?= BASE_URL ?>equipos/ver/<?= $e->id ?>" class="text-decoration-none" title="Ver detalles">
                                <i class="bi bi-arrow-up-right text-primary"></i>
                            </a>
                        </div>
                        
                        <h3 class="equipo-card-title" title="<?= htmlspecialchars($e->marca . ' ' . $e->modelo) ?>">
                            <?= htmlspecialchars($e->marca . ' ' . $e->modelo) ?>
                        </h3>
                        <p class="equipo-card-serial">ID: <?= htmlspecialchars($e->numero_serie) ?></p>
                        
                        <div class="equipo-card-details">
                            <div class="equipo-detail-row">
                                <span class="equipo-detail-label">Ubicación:</span>
                                <span class="equipo-detail-value" title="<?= htmlspecialchars($e->departamento_nombre ?? 'Sin Asignar') ?>">
                                    <?= htmlspecialchars($e->departamento_nombre ?? 'Sin Asignar') ?>
                                </span>
                            </div>
                            <div class="equipo-detail-row">
                                <span class="equipo-detail-label">Tipo:</span>
                                <span class="equipo-detail-value"><?= ucfirst($e->tipo) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="equipo-card-footer">
                        <span class="status-badge <?= $e->estado ?>">
                            <i class="bi <?= $statusInfo['icon'] ?>"></i>
                            <?= $statusInfo['text'] ?>
                        </span>
                        <div class="equipo-actions">
                            <a href="<?= BASE_URL ?>equipos/ver/<?= $e->id ?>" class="equipo-action-btn view" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <a href="<?= BASE_URL ?>equipos/editar/<?= $e->id ?>" class="equipo-action-btn edit" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= BASE_URL ?>equipos/eliminar/<?= $e->id ?>" 
                                   class="equipo-action-btn delete" 
                                   data-turbo="false"
                                   data-no-global-delete="true"
                                   title="Eliminar"
                                   onclick="event.preventDefault(); event.stopPropagation(); confirmDeleteEquipo(event, this.href, '<?= addslashes(htmlspecialchars($e->marca . ' ' . $e->modelo)) ?>'); return false;">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- List View (hidden by default) -->
        <div id="listView" class="equipos-table-container" style="display: none;">
            <table class="equipos-table">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Departamento</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipos as $e): 
                        $statusInfo = getStatusBadge($e->estado);
                    ?>
                        <tr data-bulk-item
                            data-equipo-id="<?= $e->id ?>"
                            data-estado="<?= $e->estado ?>">
                            <td style="position: relative;">
                                <!-- Bulk Checkbox -->
                                <div class="bulk-checkbox">
                                    <i class="bi bi-check-circle-fill icon-checked"></i>
                                    <i class="bi bi-circle icon-unchecked"></i>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="equipo-table-icon" <?= !empty($e->imagen) ? 'style="background-image: url('.BASE_URL.'uploads/equipos/'.$e->imagen.'); background-size: cover; background-position: center; border: 1px solid #e2e8f0;"' : '' ?>>
                                        <?php if (empty($e->imagen)): ?>
                                            <i class="bi <?= getEquipoIcon($e->tipo) ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="equipo-table-name" title="<?= htmlspecialchars($e->marca . ' ' . $e->modelo) ?>">
                                            <?= htmlspecialchars($e->marca . ' ' . $e->modelo) ?>
                                        </div>
                                        <div class="equipo-table-serial"><?= htmlspecialchars($e->numero_serie) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($e->departamento_nombre ?? 'Sin Asignar') ?></td>
                            <td><?= ucfirst($e->tipo) ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= 
                                    $e->estado === 'disponible' ? 'success' : 
                                    ($e->estado === 'en_uso' ? 'primary' : 
                                    ($e->estado === 'en_reparacion' ? 'warning' : 'secondary')) 
                                ?> bg-opacity-10 text-<?= 
                                    $e->estado === 'disponible' ? 'success' : 
                                    ($e->estado === 'en_uso' ? 'primary' : 
                                    ($e->estado === 'en_reparacion' ? 'warning' : 'secondary')) 
                                ?> req-status-badge">
                                    <i class="bi <?= $statusInfo['icon'] ?> me-1"></i>
                                    <?= $statusInfo['text'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>equipos/ver/<?= $e->id ?>" class="btn btn-sm btn-link text-primary"><i class="bi bi-eye"></i></a>
                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <a href="<?= BASE_URL ?>equipos/editar/<?= $e->id ?>" class="btn btn-sm btn-link text-secondary"><i class="bi bi-pencil"></i></a>
                                    <a href="<?= BASE_URL ?>equipos/eliminar/<?= $e->id ?>" 
                                       class="btn btn-sm btn-link text-danger" 
                                       data-turbo="false"
                                       data-no-global-delete="true"
                                       title="Eliminar"
                                       onclick="event.preventDefault(); event.stopPropagation(); confirmDeleteEquipo(event, this.href, '<?= addslashes(htmlspecialchars($e->marca . ' ' . $e->modelo)) ?>'); return false;">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modern Pagination Footer -->
        <div id="paginationFooter" class="equipos-pagination-footer">
            <div class="equipos-pagination-info">
                <span class="text-slate-500 fs-sm">
                    Mostrando <strong class="text-slate-900" id="visibleCountDisplay">0</strong> de <strong class="text-slate-900" id="totalCountDisplay">0</strong>
                </span>
                <div class="equipos-pagination-controls">
                    <span class="text-slate-400 fs-xs">Mostrar:</span>
                    <select id="itemsPerPageSelector" onchange="changeClientItemsPerPage(this.value)" class="pagination-select">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="-1">Todos</option>
                    </select>
                </div>
            </div>
            <div class="equipos-pagination-controls">
                <button id="btnPrevPage" onclick="prevPage()" class="pagination-btn">
                    <i class="bi bi-chevron-left"></i> Anterior
                </button>
                <span class="pagination-page-info">
                    Página <span id="currentPageDisplay">1</span> / <span id="totalPagesDisplay">1</span>
                </span>
                <button id="btnNextPage" onclick="nextPage()" class="pagination-btn">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <div class="empty-state-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3>No se encontraron equipos</h3>
            <p>Prueba cambiando los filtros o el término de búsqueda.</p>
            <button onclick="clearFilters()">Limpiar filtros</button>
        </div>
        
        </div><!-- End equipos-content-card -->

    </main>
</div>

</div>

<!-- Equipos Logic -->
<script src="<?= BASE_URL ?>js/equipos.js?v=2.6.0"></script>
<script>
    function confirmDeleteEquipo(e, url, name) {
        e.preventDefault();
        SimpleDeleteModal.open(url, {
            type: 'Equipo',
            name: name,
            warning: 'Esta acción eliminará el equipo del inventario permanentemente.'
        });
        return false;
    }
    
    <?php if ($_SESSION['rol'] === 'admin'): ?>
    // Initialize Bulk Delete - Solo Admin (Run immediately)
    if (window.BulkDelete) {
        BulkDelete.init({
            containerId: 'equiposMain',
            itemSelector: '[data-bulk-item]',
            itemIdAttribute: 'data-equipo-id',
            deleteUrl: BASE_URL + 'equipos/eliminar_masivo',
            entityName: 'equipos',
            entityNameSingular: 'equipo',
            toggleId: 'bulkModeToggle',
            selectAllId: 'bulkSelectAll',
            selectAllContainerId: 'bulkSelectAllContainer',
            deleteButtonId: 'bulkDeleteBtn',
            countSpanId: 'bulkSelectedCount'
        });
    }
    <?php endif; ?>
</script>