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

<link rel="stylesheet" href="<?= BASE_URL ?>css/equipos-moderno.css?v=<?= time() ?>">

<div class="equipos-container">
    
    <!-- Header -->
    <div class="equipos-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
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
                        <span class="d-none d-sm-inline">Nuevo Equipo</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <main class="container-fluid px-4 py-4" style="max-width: 1400px;">
        
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
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="todos">Todos</button>
                <button class="filter-tab" data-filter="disponible">Disponible</button>
                <button class="filter-tab" data-filter="en_uso">En Uso</button>
                <button class="filter-tab" data-filter="en_reparacion">Reparación</button>
                <button class="filter-tab" data-filter="fuera_de_servicio">Baja</button>
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
                <div class="equipo-card" data-estado="<?= $e->estado ?>">
                    <div class="equipo-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="equipo-card-icon">
                                <i class="bi <?= getEquipoIcon($e->tipo) ?>"></i>
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
                        <tr data-estado="<?= $e->estado ?>">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="equipo-table-icon">
                                        <i class="bi <?= getEquipoIcon($e->tipo) ?>"></i>
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
                                    <button class="btn btn-sm btn-link text-danger delete-equipo-btn" data-id="<?= $e->id ?>" data-tipo="<?= $e->tipo ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modern Pagination Footer -->
        <div id="paginationFooter" style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <span style="font-size: 0.875rem; color: #64748b;">
                    Mostrando <strong style="color: #0f172a;" id="visibleCountDisplay">0</strong> de <strong style="color: #0f172a;" id="totalCountDisplay">0</strong>
                </span>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                    <select id="itemsPerPageSelector" onchange="changeClientItemsPerPage(this.value)" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.75rem; color: #475569; background: white; cursor: pointer;">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="-1">Todos</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button id="btnPrevPage" onclick="prevPage()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; cursor: pointer; transition: all 0.2s;">
                    <i class="bi bi-chevron-left"></i> Anterior
                </button>
                <span style="font-size: 0.875rem; color: #475569; font-weight: 500; padding: 0 0.5rem;">
                    Página <span id="currentPageDisplay">1</span> / <span id="totalPagesDisplay">1</span>
                </span>
                <button id="btnNextPage" onclick="nextPage()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; cursor: pointer; transition: all 0.2s;">
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

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const emptyState = document.getElementById('emptyState');
    const btnViewList = document.getElementById('btnViewList');
    const btnViewGrid = document.getElementById('btnViewGrid');
    const searchInput = document.getElementById('searchEquipos');
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    let currentFilter = 'todos';
    let currentSearch = '';
    let currentView = 'grid';

    // Toggle de vista
    btnViewGrid.addEventListener('click', function() {
        currentView = 'grid';
        gridView.style.display = 'grid';
        listView.style.display = 'none';
        btnViewGrid.classList.add('active');
        btnViewList.classList.remove('active');
        applyFilters();
    });

    btnViewList.addEventListener('click', function() {
        currentView = 'list';
        gridView.style.display = 'none';
        listView.style.display = 'block';
        btnViewList.classList.add('active');
        btnViewGrid.classList.remove('active');
        applyFilters();
    });

    // Filtros de estado
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            applyFilters();
        });
    });

    // Variables de Paginación
    let itemsPerPage = <?= $paginationPerPage ?>;
    let currentPage = 1;
    let filteredIndices = []; // Indices de items que coinciden con filtro
    
    // Init selector
    const selector = document.getElementById('itemsPerPageSelector');
    if(selector) selector.value = itemsPerPage;

    window.changeClientItemsPerPage = function(val) {
        itemsPerPage = parseInt(val);
        if (window.PaginationPrefs) PaginationPrefs.set(itemsPerPage);
        else {
             const expires = new Date();
             expires.setFullYear(expires.getFullYear() + 1);
             document.cookie = 'sgen_pagination_per_page=' + itemsPerPage + ';expires=' + expires.toUTCString() + ';path=/';
        }
        currentPage = 1;
        applyFilters();
    };

    window.prevPage = function() {
        if (currentPage > 1) {
            currentPage--;
            applyFilters();
        }
    };
    window.nextPage = function() {
        const totalPages = Math.ceil(filteredIndices.length / itemsPerPage);
        if ((itemsPerPage === -1 && currentPage === 1) || (itemsPerPage !== -1 && currentPage < totalPages)) {
            currentPage++;
            applyFilters();
        }
    };

    // Aplicar filtros y paginación
    function applyFilters() {
        // Reset indices
        filteredIndices = [];
        const cards = gridView.querySelectorAll('.equipo-card');
        const rows = listView.querySelectorAll('tbody tr');
        
        // 1. Filtrar
        cards.forEach((card, index) => {
            const estado = card.getAttribute('data-estado');
            const text = card.textContent.toLowerCase();
            const matchesFilter = currentFilter === 'todos' || estado === currentFilter;
            const matchesSearch = text.includes(currentSearch);
            
            // Ocultar inicialmente
            card.style.display = 'none';
            if(rows[index]) rows[index].style.display = 'none';

            if (matchesFilter && matchesSearch) {
                filteredIndices.push(index);
            }
        });

        const totalVisible = filteredIndices.length;
        const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;
        
        // Validar página actual
        if (currentPage > totalPages) currentPage = 1;

        // 2. Paginar
        const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
        const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;
        const visibleIndices = filteredIndices.slice(start, end);
        
        visibleIndices.forEach(idx => {
            cards[idx].style.display = 'flex';
            if(rows[idx]) rows[idx].style.display = '';
        });

        // 3. Actualizar UI Footer
        document.getElementById('visibleCountDisplay').textContent = visibleIndices.length; // O totalVisible? Logs muestra "Showing X of Y". X usually items on page? No, "Showing 1-10 of 50". My UI says "Showing [count] of [total]". I'll use totalVisible.
        // Wait, "Mostrando X de Y" usually matches standard tables.
        // I will set visibleCountDisplay to totalVisible (filtered count) 
        // and totalCountDisplay to... total records?
        // Let's stick to "Mostrando [filtered] de [total]" logic or "Mostrando [filtered]"
        // The logs ui says: Mostrando <span id="visibleCount">10</span> ...
        // Logs logic: updates #visibleCount with `rows.length` (which is visible on page).
        
        // Let's update visibleCountDisplay with total matching the filter.
        document.getElementById('visibleCountDisplay').textContent = totalVisible;
        document.getElementById('totalCountDisplay').textContent = cards.length;

        document.getElementById('currentPageDisplay').textContent = currentPage;
        document.getElementById('totalPagesDisplay').textContent = totalPages;
        
        const btnPrev = document.getElementById('btnPrevPage');
        const btnNext = document.getElementById('btnNextPage');
        
        btnPrev.disabled = currentPage === 1;
        btnPrev.style.opacity = currentPage === 1 ? '0.5' : '1';
        btnPrev.style.pointerEvents = currentPage === 1 ? 'none' : 'auto';
        
        btnNext.disabled = currentPage === totalPages;
        btnNext.style.opacity = currentPage === totalPages ? '0.5' : '1';
        btnNext.style.pointerEvents = currentPage === totalPages ? 'none' : 'auto';

        // Mostrar/ocultar empty state
        if (totalVisible === 0) {
            emptyState.style.display = 'block';
            if (currentView === 'grid') gridView.style.display = 'none';
            else listView.style.display = 'none';
            document.getElementById('paginationFooter').style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            if (currentView === 'grid') gridView.style.display = 'grid';
            else listView.style.display = 'block';
             document.getElementById('paginationFooter').style.display = 'flex';
        }
    }

    // Limpiar filtros
    window.clearFilters = function() {
        currentFilter = 'todos';
        currentSearch = '';
        searchInput.value = '';
        filterTabs.forEach(t => t.classList.remove('active'));
        if(filterTabs[0]) filterTabs[0].classList.add('active');
        currentPage = 1;
        applyFilters();
    };
    
    // Initial call
    applyFilters();
});
</script>