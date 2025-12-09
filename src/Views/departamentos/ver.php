<?php
// Get department info
$deptName = htmlspecialchars($departamento->nombre);
$deptLocation = htmlspecialchars($departamento->ubicacion ?? 'Sin ubicación');
$deptDescription = htmlspecialchars($departamento->descripcion ?? 'Sin descripción disponible.');

// Get head name if exists
$headName = 'Sin asignar';
$headInitials = '--';
if (!empty($departamento->jefe_nombre)) {
    $headName = htmlspecialchars($departamento->jefe_nombre);
    $parts = explode(' ', $headName);
    $headInitials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}

// Contact info
$deptEmail = htmlspecialchars($departamento->email ?? 'No disponible');
$deptPhone = htmlspecialchars($departamento->telefono ?? 'No disponible');

// Stats
$employeeCount = count($empleados);
$assetCount = count($equipos);
$ticketCount = $departamento->tickets_abiertos ?? 0;
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/department-detail.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-detail.css?v=<?= time() ?>">

<?php
// Calculate Inventory Stats (Consumables)
$totalInvItems = 0;
$totalInvValue = 0;
$lowStockCount = 0;

if (isset($itemsInventario) && is_array($itemsInventario)) {
    foreach ($itemsInventario as $item) {
        $qty = $item->stock_departamento ?? 0;
        $totalInvItems += $qty;
        $totalInvValue += $qty * ($item->valor_compra ?? 0);
        if ($qty <= ($item->stock_minimo ?? 0)) {
            $lowStockCount++;
        }
    }
}
$formattedInvValue = '$' . number_format($totalInvValue, 2);

// Extract Categories for Filter
$categorias = [];
if (isset($itemsInventario) && is_array($itemsInventario)) {
    foreach ($itemsInventario as $item) {
        if (!empty($item->categoria) && !in_array($item->categoria, $categorias)) {
            $categorias[] = $item->categoria;
        }
    }
    sort($categorias);
}
?>

<div class="dd-wrapper">
    <div class="dd-max-w">
        
        <!-- HEADER (Outside Container) -->
        <div class="dd-header">
            <div>
                <a href="<?= BASE_URL ?>departamentos" class="dd-back-btn">
                    <i class="bi bi-arrow-left"></i> Volver a Organización
                </a>
                <div class="dd-title-group">
                    <div class="dd-title-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="dd-title">
                        <h1><?= $deptName ?></h1>
                        <div class="dd-location">
                            <i class="bi bi-geo-alt"></i> <?= $deptLocation ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dd-header-actions">
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>departamentos/editar/<?= $departamento->id ?>" class="dd-btn dd-btn-secondary">
                        <i class="bi bi-pencil"></i> Editar Info
                    </a>
                    <button type="button" class="dd-btn dd-btn-primary" onclick="openAssignModal()">
                        <i class="bi bi-plus-lg"></i> Asignar Recurso
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- MAIN CONTAINER -->
        <div class="dd-main-card">
            
            <!-- Tab Bar -->
            <div class="dd-tab-bar">
                <button type="button" class="dd-tab-btn active" data-tab="overview" onclick="switchDeptTab('overview')">
                    <i class="bi bi-grid-1x2"></i> Visión General
                </button>
                <button type="button" class="dd-tab-btn" data-tab="employees" onclick="switchDeptTab('employees')">
                    <i class="bi bi-people"></i> Empleados
                    <span class="dd-tab-count"><?= $employeeCount ?></span>
                </button>
                <button type="button" class="dd-tab-btn" data-tab="assets" onclick="switchDeptTab('assets')">
                    <i class="bi bi-pc-display"></i> Equipos
                    <span class="dd-tab-count"><?= $assetCount ?></span>
                </button>
                <button type="button" class="dd-tab-btn" data-tab="inventory" onclick="switchDeptTab('inventory')">
                    <i class="bi bi-box-seam"></i> Stock / Suministros
                    <span class="dd-tab-count"><?= $totalInvItems ?></span>
                </button>

            </div>

            <!-- Tab Content -->
            <div class="dd-tab-content">
                
                <!-- TAB: Overview -->
                <div id="panel-overview" class="dd-tab-panel active">
                    <div class="dd-overview-grid">
                        
                        <!-- Info Card -->
                        <div class="dd-info-card">
                            <div class="dd-info-header">
                                <i class="bi bi-briefcase"></i>
                                <h3>Detalles del Departamento</h3>
                            </div>
                            <div class="dd-info-body">
                                <div class="dd-info-section">
                                    <p class="dd-info-label">Descripción</p>
                                    <p class="dd-info-text"><?= $deptDescription ?></p>
                                </div>
                                
                                <hr class="dd-divider">
                                
                                <div class="dd-info-grid">
                                    <div class="dd-info-box">
                                        <div class="dd-head-row">
                                            <div class="dd-head-avatar"><?= $headInitials ?></div>
                                            <div>
                                                <p class="dd-info-label">Responsable</p>
                                                <p class="dd-info-text" style="margin-top: 0.25rem;"><?= $headName ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dd-info-box">
                                        <p class="dd-info-label">Contacto</p>
                                        <div class="dd-contact-pill">
                                            <i class="bi bi-envelope"></i> <?= $deptEmail ?>
                                        </div>
                                        <div class="dd-contact-pill">
                                            <i class="bi bi-telephone"></i> <?= $deptPhone ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Card -->
                        <div class="dd-stats-card">
                            <div class="dd-stats-header">
                                <i class="bi bi-activity"></i>
                                <h3>Estadísticas</h3>
                            </div>
                            
                            <div class="dd-stat-item">
                                <div>
                                    <p class="dd-stat-label">Personal</p>
                                    <p class="dd-stat-value"><?= $employeeCount ?></p>
                                </div>
                                <div class="dd-stat-icon blue">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>

                            <div class="dd-stat-item">
                                <div>
                                    <p class="dd-stat-label">Activos</p>
                                    <p class="dd-stat-value"><?= $assetCount ?></p>
                                </div>
                                <div class="dd-stat-icon emerald">
                                    <i class="bi bi-pc-display"></i>
                                </div>
                            </div>

                            <div class="dd-stat-item">
                                <div>
                                    <p class="dd-stat-label">Tickets</p>
                                    <p class="dd-stat-value"><?= $ticketCount ?></p>
                                </div>
                                <div class="dd-stat-icon amber">
                                    <i class="bi bi-file-text"></i>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- TAB: Employees -->
                <div id="panel-employees" class="dd-tab-panel">
                    <div class="dd-toolbar">
                        <div class="dd-search-wrapper">
                            <i class="bi bi-search dd-search-icon"></i>
                            <input type="text" class="dd-search-input" placeholder="Buscar empleados..." id="empSearchInput" oninput="filterEmployees()">
                        </div>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <button type="button" class="dd-btn dd-btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarEmpleado">
                                <i class="bi bi-person-plus"></i> Asignar Empleado
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($empleados)): ?>
                        <div class="dd-empty-state">
                            <div class="dd-empty-icon"><i class="bi bi-person-x"></i></div>
                            <p class="dd-empty-title">Sin empleados</p>
                            <p class="dd-empty-text">No hay empleados asignados a este departamento.</p>
                        </div>
                    <?php else: ?>
                        <div class="dd-list" id="employeeList">
                            <?php foreach ($empleados as $emp): 
                                $empName = htmlspecialchars("{$emp->nombre} {$emp->apellido}");
                                $empInitials = strtoupper(substr($emp->nombre, 0, 1) . substr($emp->apellido, 0, 1));
                                $empRole = htmlspecialchars($emp->cargo ?? 'Sin cargo');
                                $empStatus = $emp->activo ?? true ? 'active' : 'on_leave';
                            ?>
                                <div class="dd-list-item" data-name="<?= strtolower($empName) ?>">
                                    <div class="dd-item-main">
                                        <div class="dd-item-avatar indigo"><?= $empInitials ?></div>
                                        <div>
                                            <h4 class="dd-item-name"><?= $empName ?></h4>
                                            <p class="dd-item-sub"><?= $empRole ?></p>
                                        </div>
                                    </div>
                                    <div class="dd-item-meta">
                                        <span class="dd-status-badge <?= $empStatus ?>">
                                            <?= $empStatus === 'active' ? 'Activo' : 'Permiso' ?>
                                        </span>
                                        <a href="<?= BASE_URL ?>empleados/editar/<?= $emp->id ?>" class="dd-action-btn" title="Editar">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB: Assets -->
                <div id="panel-assets" class="dd-tab-panel">
                    <div class="dd-toolbar">
                        <div class="dd-search-wrapper">
                            <i class="bi bi-search dd-search-icon"></i>
                            <input type="text" class="dd-search-input" placeholder="Buscar equipos..." id="assetSearchInput" oninput="filterAssets()">
                        </div>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <button type="button" class="dd-btn dd-btn-primary" onclick="openAssignModal()">
                                <i class="bi bi-plus-lg"></i> Asignar Equipo
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($equipos)): ?>
                        <div class="dd-empty-state">
                            <div class="dd-empty-icon"><i class="bi bi-inbox"></i></div>
                            <p class="dd-empty-title">Sin equipos</p>
                            <p class="dd-empty-text">No hay equipos asignados a este departamento.</p>
                        </div>
                    <?php else: ?>
                        <div class="dd-list" id="assetList">
                            <?php foreach ($equipos as $e): 
                                $assetName = htmlspecialchars(($e->marca ?? '') . ' ' . ($e->modelo ?? $e->tipo));
                                $assetSerial = htmlspecialchars($e->codigo_inventario);
                                $assetUser = htmlspecialchars($e->usuario_asignado ?? 'Sin asignar');
                                $assetStatus = $e->estado === 'disponible' || $e->estado === 'en_uso' ? 'active' : 'maintenance';
                                $assetIcon = in_array($e->tipo, ['laptop', 'notebook']) ? 'bi-laptop' : ($e->tipo === 'impresora' ? 'bi-printer' : 'bi-pc-display');
                            ?>
                                <div class="dd-list-item" data-name="<?= strtolower($assetName . ' ' . $assetSerial) ?>">
                                    <div class="dd-item-main">
                                        <div class="dd-item-avatar blue"><i class="bi <?= $assetIcon ?>"></i></div>
                                        <div>
                                            <h4 class="dd-item-name"><?= $assetName ?></h4>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                                                <span class="dd-serial-badge"><?= $assetSerial ?></span>
                                                <span class="dd-user-badge"><i class="bi bi-person"></i> <?= $assetUser ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dd-item-meta">
                                        <span class="dd-status-badge <?= $assetStatus ?>">
                                            <?= $assetStatus === 'active' ? 'En Uso' : 'Mant.' ?>
                                        </span>
                                        <a href="<?= BASE_URL ?>equipos/ver/<?= $e->id ?>" class="dd-action-btn" title="Ver">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB: Inventory Content (Merged from por_departamento.php) -->
                <div id="panel-inventory" class="dd-tab-panel">
                    
                    <!-- Stats Row -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                        <div class="id-stat-item">
                            <p class="id-stat-label">Items en Stock</p>
                            <p class="id-stat-val"><?= $totalInvItems ?></p>
                        </div>
                        <div class="id-stat-item">
                            <p class="id-stat-label">Valor Total</p>
                            <p class="id-stat-val"><?= $formattedInvValue ?></p>
                        </div>
                        <div class="id-stat-item">
                            <p class="id-stat-label text-rose-600">Stock Crítico</p>
                            <p class="id-stat-val critical">
                                <?= $lowStockCount ?>
                                <?php if($lowStockCount > 0): ?>
                                <i class="bi bi-exclamation-triangle-fill text-rose-600 fs-6"></i>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="dd-toolbar">
                         <div class="id-search-box" style="flex: 1; max-width: 400px;">
                            <i class="bi bi-search id-search-icon"></i>
                            <input type="text" id="invSearchInput" class="id-search-input" placeholder="Buscar ítem por código o nombre..." style="width: 100%;">
                        </div>
                        
                        <div class="id-actions" style="margin-left: auto;">
                            <!-- View Toggle -->
                            <div class="id-view-toggle">
                                <button class="id-toggle-btn active" id="btnViewList" title="Vista Lista">
                                    <i class="bi bi-list"></i>
                                </button>
                                <button class="id-toggle-btn" id="btnViewGrid" title="Vista Cuadrícula">
                                    <i class="bi bi-grid"></i>
                                </button>
                            </div>

                            <div class="id-filter-wrapper">
                                <button class="id-btn-filter" id="btnFilter">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <div class="id-filter-dropdown" id="filterDropdown">
                                    <div class="id-filter-header">Filtrar por Categoría</div>
                                    <div class="id-filter-item active" data-filter="all">
                                        <i class="bi bi-grid-3x3"></i> Todas las Categorías
                                    </div>
                                    <?php foreach ($categorias as $cat): ?>
                                    <div class="id-filter-item" data-filter="<?= htmlspecialchars(strtolower($cat)) ?>">
                                        <i class="bi bi-tag"></i> <?= htmlspecialchars($cat) ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div id="listView" class="id-table-container">
                        <table class="id-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="padding-left: 1.5rem;">Item / Código</th>
                                    <th>Categoría</th>
                                    <th>Ubicación Local</th>
                                    <th>Disponibilidad</th>
                                    <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($itemsInventario)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No hay stock asignado a este departamento.</td></tr>
                                <?php else: foreach ($itemsInventario as $item): 
                                    $stock = $item->stock_departamento ?? 0;
                                    $min = $item->stock_minimo ?? 0;
                                    $isLow = $stock <= $min;
                                    $pct = min(($stock / max($min * 2, 10)) * 100, 100); 
                                ?>
                                    <tr class="inv-item-row" data-search="<?= strtolower($item->nombre . ' ' . $item->codigo) ?>" data-category="<?= strtolower($item->categoria ?? '') ?>">
                                        <td style="padding-left: 1.5rem;">
                                            <div class="id-item-cell">
                                                <div class="id-item-icon">
                                                    <i class="bi bi-box-seam"></i>
                                                </div>
                                                <div class="id-item-info">
                                                    <p><?= htmlspecialchars($item->nombre) ?></p>
                                                    <span class="id-item-code"><?= htmlspecialchars($item->codigo) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="id-badge id-badge-indigo"><?= htmlspecialchars($item->categoria) ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($item->ubicacion ?? 'General') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="id-availability">
                                                <div class="id-avail-text">
                                                    <span class="<?= $isLow ? 'text-rose-600' : 'text-emerald-600' ?>"><?= $stock ?> disponibles</span>
                                                </div>
                                                <div class="id-avail-bar">
                                                    <div class="id-avail-fill <?= $isLow ? 'bg-rose-500' : 'bg-emerald-500' ?>" style="width: <?= $pct ?>%"></div>
                                                </div>
                                                <?php if($isLow): ?>
                                                    <span class="text-rose-600" style="font-size: 0.7rem; display: flex; align-items: center; gap: 0.25rem;">
                                                        <i class="bi bi-exclamation-triangle"></i> Stock Bajo
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="padding-right: 1.5rem;" class="id-actions-cell">
                                            <div class="id-row-actions">
                                                <button class="id-action-btn view" title="Ver Detalles" onclick="location.href='<?= BASE_URL ?>inventario/ver/<?= $item->id ?>'"><i class="bi bi-eye"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                     <!-- Grid View -->
                    <div id="gridView" class="id-grid-container" style="display: none;">
                        <?php if (empty($itemsInventario)): ?>
                             <div class="id-empty-state">
                                <i class="bi bi-box-seam id-empty-icon"></i>
                                <p>No hay stock asignado.</p>
                            </div>
                        <?php else: foreach ($itemsInventario as $item): 
                             $stock = $item->stock_departamento ?? 0;
                             $min = $item->stock_minimo ?? 0;
                             $isLow = $stock <= $min;
                        ?>
                            <div class="id-card inv-item-card" data-search="<?= strtolower($item->nombre . ' ' . $item->codigo) ?>" data-category="<?= strtolower($item->categoria ?? '') ?>" onclick="location.href='<?= BASE_URL ?>inventario/ver/<?= $item->id ?>'">
                                <div class="id-card-header">
                                    <div class="id-card-icon"><i class="bi bi-box-seam"></i></div>
                                    <span class="id-status-badge <?= $isLow ? 'id-status-low' : 'id-status-ok' ?>"><?= $isLow ? 'Bajo' : 'Ok' ?></span>
                                </div>
                                <h3 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.25rem;"><?= htmlspecialchars($item->nombre) ?></h3>
                                <p style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 1rem;"><?= htmlspecialchars($item->codigo) ?> &bull; <?= htmlspecialchars($item->ubicacion ?? 'General') ?></p>
                                <div class="id-card-footer">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #475569;"><?= $stock ?> Unidades</span>
                                    <i class="bi bi-three-dots-vertical text-muted"></i>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                     <!-- Empty State Search (Hidden by default) -->
                    <div id="emptySearch" class="id-empty-state" style="display: none;">
                        <i class="bi bi-search id-empty-icon"></i>
                        <p>No se encontraron items que coincidan con la búsqueda.</p>
                    </div>
                </div>

            </div>
        </div>

</div>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>css/modal-resource-assign.css?v=<?= time() ?>">

<!-- New Unified Resource Assignment Modal -->
<div id="resourceAssignModal" class="ram-overlay" onclick="if(event.target === this) closeResourceModal()">
    <div class="ram-modal">
        <!-- Header -->
        <div class="ram-header">
            <div class="ram-header-top">
                <div>
                    <h2 class="ram-title">Asignar Recurso</h2>
                    <p class="ram-subtitle">Selecciona qué deseas vincular al departamento</p>
                </div>
                <button type="button" class="ram-close-btn" onclick="closeResourceModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="ram-tab-container">
                <div class="ram-tab-switcher">
                    <button type="button" class="ram-tab-btn active equipment" data-type="equipment" onclick="switchResourceTab('equipment')">
                        <i class="bi bi-box-seam"></i> Asignar Equipo
                    </button>
                    <button type="button" class="ram-tab-btn employee" data-type="employee" onclick="switchResourceTab('employee')">
                        <i class="bi bi-person-plus"></i> Asignar Personal
                    </button>
                </div>
            </div>

            <!-- Search -->
            <div class="ram-search-container">
                <div class="ram-search-wrapper">
                    <input type="text" class="ram-search-input" id="ramSearchInput" placeholder="Buscar por serial, modelo o tipo..." oninput="filterResourceList()">
                    <i class="bi bi-search ram-search-icon"></i>
                </div>
            </div>
        </div>

        <!-- List Container -->
        <div class="ram-list-container">
            <div class="ram-list" id="ramList">
                <!-- Items will be loaded via AJAX -->
            </div>
            <div class="ram-empty" id="ramEmpty" style="display: none;">
                <i class="bi bi-inbox"></i>
                <p>No se encontraron resultados.</p>
            </div>
            <div class="ram-empty" id="ramLoading">
                <i class="bi bi-arrow-repeat"></i>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="ram-footer">
            <button type="button" class="ram-btn ram-btn-cancel" onclick="closeResourceModal()">Cancelar</button>
            <button type="button" class="ram-btn ram-btn-confirm" id="ramConfirmBtn" disabled onclick="confirmResourceAssign()">
                Confirmar Equipo
            </button>
        </div>
    </div>
</div>

<!-- Hidden forms for submission -->
<form id="formAsignarEquipo" action="<?= BASE_URL ?>departamentos/asignarEquipo" method="POST" style="display:none;">
    <input type="hidden" name="departamento_id" value="<?= $departamento->id ?>">
    <input type="hidden" name="identificador" id="equipoIdentificador">
</form>
<form id="formAsignarEmpleado" action="<?= BASE_URL ?>departamentos/asignarEmpleado" method="POST" style="display:none;">
    <input type="hidden" name="departamento_id" value="<?= $departamento->id ?>">
    <input type="hidden" name="empleado_id" id="empleadoId">
</form>

<script>
// Tab switching for department details
function switchDeptTab(tab) {
    document.querySelectorAll('.dd-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    document.querySelectorAll('.dd-tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    document.getElementById('panel-' + tab).classList.add('active');
}

// Filter employees in department detail
function filterEmployees() {
    const query = document.getElementById('empSearchInput').value.toLowerCase();
    document.querySelectorAll('#employeeList .dd-list-item').forEach(item => {
        const name = item.dataset.name;
        item.style.display = name.includes(query) ? '' : 'none';
    });
}

// Filter assets in department detail
function filterAssets() {
    const query = document.getElementById('assetSearchInput').value.toLowerCase();
    document.querySelectorAll('#assetList .dd-list-item').forEach(item => {
        const name = item.dataset.name;
        item.style.display = name.includes(query) ? '' : 'none';
    });
}

// ===== INVENTORY TAB SCRIPTS =====
document.addEventListener('DOMContentLoaded', function() {
    const btnList = document.getElementById('btnViewList');
    const btnGrid = document.getElementById('btnViewGrid');
    const listView = document.getElementById('listView');
    const gridView = document.getElementById('gridView');
    const emptySearch = document.getElementById('emptySearch');
    const searchInput = document.getElementById('invSearchInput');
    const btnFilter = document.getElementById('btnFilter');
    const filterDropdown = document.getElementById('filterDropdown');
    const filterItems_el = document.querySelectorAll('.id-filter-item');
    
    let currentView = 'list';
    let currentFilter = 'all';

    if (btnList && btnGrid) {
        // View Toggle
        btnList.addEventListener('click', () => {
            listView.style.display = 'block';
            gridView.style.display = 'none';
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
            currentView = 'list';
            applyInventoryFilters();
        });

        btnGrid.addEventListener('click', () => {
            listView.style.display = 'none';
            gridView.style.display = 'grid';
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
            currentView = 'grid';
            applyInventoryFilters();
        });
    }

    if (btnFilter) {
        // Filter Dropdown Toggle
        btnFilter.addEventListener('click', (e) => {
            e.stopPropagation();
            filterDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            if(filterDropdown) filterDropdown.classList.remove('show');
        });
    }

    if (filterItems_el.length > 0) {
        // Filter Item Selection
        filterItems_el.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                filterItems_el.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                currentFilter = item.getAttribute('data-filter');
                if(filterDropdown) filterDropdown.classList.remove('show');
                applyInventoryFilters();
            });
        });
    }

    if (searchInput) {
        // Search
        searchInput.addEventListener('input', applyInventoryFilters);
    }

    function applyInventoryFilters() {
        if (!searchInput) return;
        const term = searchInput.value.toLowerCase();
        let hasMatches = false;

        // Filter List Rows
        const rows = document.querySelectorAll('.inv-item-row');
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            const categoryData = row.getAttribute('data-category');
            
            const matchesSearch = searchData.includes(term);
            const matchesFilter = (currentFilter === 'all') || (categoryData === currentFilter);
            
            if (matchesSearch && matchesFilter) {
                row.style.display = 'table-row';
                hasMatches = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter Grid Cards
        const cards = document.querySelectorAll('.inv-item-card');
        cards.forEach(card => {
            const searchData = card.getAttribute('data-search');
            const categoryData = card.getAttribute('data-category');
            
            const matchesSearch = searchData.includes(term);
            const matchesFilter = (currentFilter === 'all') || (categoryData === currentFilter);
            
            if (matchesSearch && matchesFilter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/Hide Empty State
        if (emptySearch) {
            if (!hasMatches && (rows.length > 0 || cards.length > 0)) { // Only show empty state if there ARE items but they are filtered out
                emptySearch.style.display = 'block';
                listView.style.display = 'none';
                gridView.style.display = 'none';
            } else {
                emptySearch.style.display = 'none';
                if (!hasMatches && rows.length === 0 && cards.length === 0) {
                     // Really empty, handled by PHP if/else
                } else {
                    if(currentView === 'list') listView.style.display = 'block';
                    else gridView.style.display = 'grid';
                }
            }
        }
    }
});

// ===== Resource Assignment Modal =====
let ramActiveTab = 'equipment';
let ramData = { equipment: [], employee: [] };
let ramSelectedItem = null;

function openAssignModal() {
    const modal = document.getElementById('resourceAssignModal');
    
    // Reset state
    ramSelectedItem = null;
    document.getElementById('ramSearchInput').value = '';
    updateConfirmButton();
    
    // Show modal with animation (like inventory modal)
    modal.style.display = 'flex';
    // Trigger reflow for animation
    modal.offsetHeight;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Load data
    loadResourceData('equipment');
    loadResourceData('employee');
}

function closeResourceModal() {
    const modal = document.getElementById('resourceAssignModal');
    modal.classList.remove('active');
    // Wait for animation to finish before hiding
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
    document.body.style.overflow = '';
}

function switchResourceTab(tab) {
    ramActiveTab = tab;
    ramSelectedItem = null;
    
    // Update tab buttons
    document.querySelectorAll('.ram-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.type === tab);
    });
    
    // Update search placeholder
    const searchInput = document.getElementById('ramSearchInput');
    searchInput.placeholder = tab === 'equipment' 
        ? 'Buscar por serial, modelo o tipo...' 
        : 'Buscar por nombre o cargo...';
    searchInput.value = '';
    
    updateConfirmButton();
    renderResourceList();
}

function loadResourceData(type) {
    const url = type === 'equipment' 
        ? '<?= BASE_URL ?>departamentos/apiEquiposDisponibles'
        : '<?= BASE_URL ?>departamentos/apiEmpleadosDisponibles';
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            ramData[type] = data;
            if (ramActiveTab === type) {
                renderResourceList();
            }
        })
        .catch(err => {
            console.error('Error loading', type, err);
            ramData[type] = [];
            if (ramActiveTab === type) {
                renderResourceList();
            }
        });
}

function renderResourceList() {
    const list = document.getElementById('ramList');
    const empty = document.getElementById('ramEmpty');
    const loading = document.getElementById('ramLoading');
    const items = getFilteredItems();
    
    loading.style.display = 'none';
    
    if (items.length === 0) {
        list.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    
    empty.style.display = 'none';
    
    list.innerHTML = items.map(item => {
        const isSelected = ramSelectedItem?.id === item.id;
        const isUnavailable = item.status === 'maintenance' || item.status === 'on_leave';
        const typeClass = ramActiveTab === 'equipment' ? 'equipment' : 'employee';
        
        const iconHtml = ramActiveTab === 'equipment' 
            ? `<i class="bi ${getEquipmentIcon(item.type)}"></i>`
            : item.avatar;
        
        const statusHtml = isUnavailable 
            ? `<span class="ram-item-status">${ramActiveTab === 'equipment' ? 'En Mantenimiento' : 'De Permiso'}</span>` 
            : '';
        
        const metaHtml = ramActiveTab === 'equipment'
            ? `<span class="ram-item-code">${item.code || ''}</span><span>• ${item.type || ''}</span>`
            : `<span class="ram-item-code">${item.cedula || ''}</span><span>• ${item.role || ''}</span>`;
        
        return `
            <div class="ram-item ${isSelected ? 'selected' : ''} ${isUnavailable ? 'unavailable' : ''}" 
                 data-id="${item.id}" 
                 data-code="${item.code || ''}"
                 onclick="${isUnavailable ? '' : `selectResourceItem(${item.id})`}">
                <div class="ram-item-icon ${typeClass}">${iconHtml}</div>
                <div class="ram-item-info">
                    <div class="ram-item-header">
                        <h4 class="ram-item-name">${item.name}</h4>
                        ${statusHtml}
                    </div>
                    <div class="ram-item-meta">${metaHtml}</div>
                </div>
                <div class="ram-item-check">
                    <i class="bi bi-check-lg"></i>
                </div>
            </div>
        `;
    }).join('');
}

function getEquipmentIcon(type) {
    const icons = {
        'laptop': 'bi-laptop',
        'notebook': 'bi-laptop',
        'computadora': 'bi-pc-display',
        'desktop': 'bi-pc-display',
        'monitor': 'bi-display',
        'impresora': 'bi-printer',
        'telefono': 'bi-phone',
        'smartphone': 'bi-phone',
        'tablet': 'bi-tablet'
    };
    return icons[type?.toLowerCase()] || 'bi-box';
}

function getFilteredItems() {
    const query = document.getElementById('ramSearchInput').value.toLowerCase().trim();
    const items = ramData[ramActiveTab] || [];
    
    console.log('Search query:', query, 'Items count:', items.length);
    
    if (!query) return items;
    
    const filtered = items.filter(item => {
        const nameMatch = (item.name || '').toLowerCase().includes(query);
        const codeMatch = (item.code || '').toLowerCase().includes(query);
        const typeMatch = (item.type || '').toLowerCase().includes(query);
        const roleMatch = (item.role || '').toLowerCase().includes(query);
        const cedulaMatch = (item.cedula || '').toLowerCase().includes(query);
        const serialMatch = (item.serial || '').toLowerCase().includes(query);
        return nameMatch || codeMatch || typeMatch || roleMatch || cedulaMatch || serialMatch;
    });
    
    console.log('Filtered count:', filtered.length);
    return filtered;
}

function filterResourceList() {
    console.log('filterResourceList called');
    renderResourceList();
}

function selectResourceItem(id) {
    const items = ramData[ramActiveTab] || [];
    ramSelectedItem = items.find(i => i.id === id) || null;
    
    // Update visual selection
    document.querySelectorAll('.ram-item').forEach(el => {
        el.classList.toggle('selected', parseInt(el.dataset.id) === id);
    });
    
    updateConfirmButton();
}

function updateConfirmButton() {
    const btn = document.getElementById('ramConfirmBtn');
    btn.disabled = !ramSelectedItem;
    btn.textContent = ramActiveTab === 'equipment' ? 'Confirmar Equipo' : 'Confirmar Empleado';
}

function confirmResourceAssign() {
    if (!ramSelectedItem) return;
    
    const btn = document.getElementById('ramConfirmBtn');
    btn.classList.add('loading');
    btn.disabled = true;
    btn.textContent = 'Asignando...';
    
    if (ramActiveTab === 'equipment') {
        document.getElementById('equipoIdentificador').value = ramSelectedItem.code;
        document.getElementById('formAsignarEquipo').submit();
    } else {
        document.getElementById('empleadoId').value = ramSelectedItem.id;
        document.getElementById('formAsignarEmpleado').submit();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeResourceModal();
    }
});
</script>
