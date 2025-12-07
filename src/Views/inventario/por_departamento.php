<?php
/**
 * Vista de Inventario por Departamento - Diseño Moderno
 */

// Calcular estadísticas
$totalItems = 0;
$totalValue = 0;
$lowStockCount = 0;

foreach ($items as $item) {
    $qty = $item->stock_departamento ?? 0;
    $totalItems += $qty;
    $totalValue += $qty * ($item->valor_compra ?? 0);
    if ($qty <= ($item->stock_minimo ?? 0)) {
        $lowStockCount++;
    }
}

// Extraer categorías únicas para el filtro
$categorias = [];
foreach ($items as $item) {
    if (!empty($item->categoria) && !in_array($item->categoria, $categorias)) {
        $categorias[] = $item->categoria;
    }
}
sort($categorias);

// Formatear valor (suponiendo moneda local o $)
$formattedValue = '$' . number_format($totalValue, 2);

// Manager name
$managerName = $departamento->jefe_nombre ?? 'No asignado';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-detail.css?v=<?= time() ?>">

<div class="id-container">
    <div class="id-max-w-6xl">

        <!-- --- BREADCRUMB --- -->
        <div class="id-breadcrumb-row">
            <div class="id-breadcrumb">
                <a href="<?= BASE_URL ?>inventario">
                    <i class="bi bi-arrow-left"></i>
                    Volver a Departamentos
                </a>
                <span class="id-breadcrumb-sep">/</span>
                <span>Inventario</span>
                <span class="id-breadcrumb-sep">/</span>
                <span class="id-breadcrumb-current"><?= htmlspecialchars($departamento->nombre) ?></span>
            </div>
            
            <!-- History Button (Top Right) -->
            <a href="<?= BASE_URL ?>inventario/historial-departamento/<?= $departamento->id ?>" class="id-btn-history">
                <i class="bi bi-clock-history"></i>
                Historial
                <span class="id-btn-badge"><?= count($movimientos ?? []) ?></span>
            </a>
        </div>

        <!-- --- WORKSPACE HEADER --- -->
        <div class="id-header">
            <div class="id-header-deco"></div>
            
            <div class="id-header-content">
                <div class="id-info-group">
                    <div class="id-dept-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="id-dept-details">
                        <h1 class="id-dept-title"><?= htmlspecialchars($departamento->nombre) ?></h1>
                        <div class="id-dept-meta">
                            <i class="bi bi-geo-alt"></i>
                            <span><?= htmlspecialchars($departamento->ubicacion ?? 'Sin ubicación') ?> &bull; <?= htmlspecialchars($departamento->descripcion ?? 'Oficina Principal') ?></span>
                        </div>
                        <div class="id-dept-manager">
                            <span>Responsable:</span> <?= htmlspecialchars($managerName) ?>
                        </div>
                    </div>
                </div>

                <div class="id-stats-group">
                    <div class="id-stat-item">
                        <p class="id-stat-label">Items</p>
                        <p class="id-stat-val"><?= $totalItems ?></p>
                    </div>
                    <div class="id-stat-item">
                        <p class="id-stat-label">Valor</p>
                        <p class="id-stat-val"><?= $formattedValue ?></p>
                    </div>
                    <div class="id-stat-item">
                         <p class="id-stat-label text-rose-600">Crítico</p>
                         <p class="id-stat-val critical">
                             <?= $lowStockCount ?>
                             <?php if($lowStockCount > 0): ?>
                                <i class="bi bi-exclamation-triangle-fill text-rose-600 fs-6"></i>
                             <?php endif; ?>
                         </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- --- TOOLBAR --- -->
        <div class="id-toolbar">
            <div class="id-search-box">
                <i class="bi bi-search id-search-icon"></i>
                <input 
                    type="text" 
                    id="invSearchInput" 
                    class="id-search-input" 
                    placeholder="Buscar ítem por código o nombre..."
                >
            </div>
            
            <div class="id-actions">
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
                        <i class="bi bi-funnel"></i>
                        Filtrar
                    </button>
                    <div class="id-filter-dropdown" id="filterDropdown">
                        <div class="id-filter-header">Filtrar por Categoría</div>
                        <div class="id-filter-item active" data-filter="all">
                            <i class="bi bi-grid-3x3"></i>
                            Todas las Categorías
                        </div>
                        <?php foreach ($categorias as $cat): ?>
                        <div class="id-filter-item" data-filter="<?= htmlspecialchars(strtolower($cat)) ?>">
                            <i class="bi bi-tag"></i>
                            <?= htmlspecialchars($cat) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 
                <?php if($_SESSION['rol'] === 'admin'): ?>
                <a href="#" onclick="alert('Para asignar stock, vaya a Inventario General > Item > Distribución')" class="id-btn-primary" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="bi bi-plus-lg"></i>
                    Asignar Stock
                </a>
                <?php endif; ?> 
                -->
            </div>
        </div>

        <!-- --- CONTENT AREA --- -->
        
        <!-- List View -->
        <div id="listView" class="id-table-container">
            <table class="id-table">
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
                    <?php if (empty($items)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No hay items asignados a este departamento.</td></tr>
                    <?php else: foreach ($items as $item): 
                        $stock = $item->stock_departamento ?? 0;
                        $min = $item->stock_minimo ?? 0;
                        $isLow = $stock <= $min;
                        $pct = min(($stock / max($min * 2, 10)) * 100, 100); // Simple bar calc
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
                            <td>
                                <span class="id-badge id-badge-indigo"><?= htmlspecialchars($item->categoria) ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($item->ubicacion ?? 'General') ?>
                                </div>
                            </td>
                            <td>
                                <div class="id-availability">
                                    <div class="id-avail-text">
                                        <span class="<?= $isLow ? 'text-rose-600' : 'text-emerald-600' ?>">
                                            <?= $stock ?> disponibles
                                        </span>
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
                                    <button class="id-action-btn view" title="Ver Detalles" onclick="location.href='<?= BASE_URL ?>inventario/ver/<?= $item->id ?>'">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if($_SESSION['rol'] === 'admin'): ?>
                                    <button class="id-action-btn edit" title="Editar" onclick="location.href='<?= BASE_URL ?>inventario/editar/<?= $item->id ?>'">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="id-action-btn delete" title="Eliminar" onclick="if(confirm('¿Eliminar este ítem del inventario?')) location.href='<?= BASE_URL ?>inventario/eliminar/<?= $item->id ?>'">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Grid View -->
        <div id="gridView" class="id-grid-container" style="display: none;">
            <?php if (empty($items)): ?>
                 <div class="id-empty-state">
                    <i class="bi bi-box-seam id-empty-icon"></i>
                    <p>No hay items asignados a este departamento.</p>
                </div>
            <?php else: foreach ($items as $item): 
                 $stock = $item->stock_departamento ?? 0;
                 $min = $item->stock_minimo ?? 0;
                 $isLow = $stock <= $min;
            ?>
                <div class="id-card inv-item-card" data-search="<?= strtolower($item->nombre . ' ' . $item->codigo) ?>" data-category="<?= strtolower($item->categoria ?? '') ?>" onclick="location.href='<?= BASE_URL ?>inventario/ver/<?= $item->id ?>'">
                    <div class="id-card-header">
                        <div class="id-card-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <span class="id-status-badge <?= $isLow ? 'id-status-low' : 'id-status-ok' ?>">
                            <?= $isLow ? 'Bajo' : 'Ok' ?>
                        </span>
                    </div>
                    <h3 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.25rem;"><?= htmlspecialchars($item->nombre) ?></h3>
                    <p style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 1rem;">
                        <?= htmlspecialchars($item->codigo) ?> &bull; <?= htmlspecialchars($item->ubicacion ?? 'General') ?>
                    </p>
                    
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

<script>
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

    // View Toggle
    btnList.addEventListener('click', () => {
        listView.style.display = 'block';
        gridView.style.display = 'none';
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
        currentView = 'list';
        applyFilters();
    });

    btnGrid.addEventListener('click', () => {
        listView.style.display = 'none';
        gridView.style.display = 'grid';
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
        currentView = 'grid';
        applyFilters();
    });

    // Filter Dropdown Toggle
    btnFilter.addEventListener('click', (e) => {
        e.stopPropagation();
        filterDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
        filterDropdown.classList.remove('show');
    });

    // Filter Item Selection
    filterItems_el.forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            filterItems_el.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            currentFilter = item.getAttribute('data-filter');
            filterDropdown.classList.remove('show');
            applyFilters();
        });
    });

    // Search
    searchInput.addEventListener('input', applyFilters);

    function applyFilters() {
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
        if (!hasMatches) {
            emptySearch.style.display = 'block';
            listView.style.display = 'none';
            gridView.style.display = 'none';
        } else {
            emptySearch.style.display = 'none';
            if(currentView === 'list') listView.style.display = 'block';
            else gridView.style.display = 'grid';
        }
    }
});
</script>
