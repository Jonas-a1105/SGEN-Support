<?php
/**
 * Vista de Lista de Departamentos - Diseño Moderno
 * Incluye: Cards coloridas, métricas, barra de progreso, vista grid/lista
 */

// Colores para departamentos (ciclo si hay más)
$colores = ['indigo', 'orange', 'pink', 'emerald', 'cyan'];

// Helper para obtener color de departamento
function getDeptColor($index, $colores) {
    return $colores[$index % count($colores)];
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/departamentos-moderno.css?v=<?= time() ?>">

<div class="departamentos-container">
    
    <!-- Header -->
    <div class="departamentos-header">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                        <i class="bi bi-building text-white fs-4"></i>
                    </div>
                    <div>
                        <h1 class="departamentos-title">Departamentos</h1>
                        <p class="departamentos-subtitle mb-0">Visión general de las unidades organizativas.</p>
                    </div>
                </div>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>departamentos/crear" class="btn-crear-dept">
                        <i class="bi bi-plus-lg"></i>
                        <span>Crear Departamento</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <main class="container-fluid px-4 py-4" style="max-width: 1400px;">
        
        <!-- Toolbar -->
        <div class="departamentos-toolbar">
            <!-- Search -->
            <div class="search-box-dept">
                <input type="text" id="searchDepartamentos" placeholder="Buscar por nombre...">
                <i class="bi bi-search"></i>
            </div>

            <!-- View Toggle -->
            <div class="view-toggle-dept">
                <button id="btnViewGridDept" class="active" title="Vista Grid">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button id="btnViewListDept" title="Vista Lista">
                    <i class="bi bi-list-ul"></i>
                </button>
            </div>
        </div>

        <!-- Grid View -->
        <div id="gridViewDept" class="departamentos-grid">
            <?php foreach ($departamentos as $index => $d): 
                $color = getDeptColor($index, $colores);
                
                // Usar datos reales (vienen del controlador)
                $empleados_count = $d->empleados_count ?? 0;
                $activos_count = $d->equipos_count ?? 0;
                $codigo = 'DEPT-' . str_pad($d->id, 2, '0', STR_PAD_LEFT);
                $descripcion = $d->descripcion ?? 'Gestión y administración del área de ' . strtolower($d->nombre) . '.';
            ?>
                <div class="dept-card" data-search="<?= htmlspecialchars(strtolower($d->nombre)) ?>">
                    <!-- Stripe de color -->
                    <div class="dept-card-stripe <?= $color ?>"></div>

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="dept-icon-box <?= $color ?>">
                            <i class="bi bi-building"></i>
                        </div>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <div class="dropdown">
                                <button class="btn btn-link p-2 text-muted" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>departamentos/editar/<?= $d->id ?>">
                                            <i class="bi bi-pencil me-2"></i>Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" 
                                           href="<?= BASE_URL ?>departamentos/eliminar/<?= $d->id ?>"
                                           onclick="return confirm('¿Eliminar este departamento?')">
                                            <i class="bi bi-trash me-2"></i>Eliminar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h3 class="dept-card-title"><?= htmlspecialchars($d->nombre) ?></h3>
                    <div class="dept-card-code"><?= $codigo ?></div>
                    <p class="dept-card-desc"><?= htmlspecialchars($descripcion) ?></p>

                    <!-- Metrics -->
                    <div class="dept-metrics">
                        <div class="dept-metric">
                            <div class="dept-metric-header">
                                <i class="bi bi-pc-display"></i>
                                <span>Equipos</span>
                            </div>
                            <div class="dept-metric-value"><?= $activos_count ?></div>
                        </div>
                        <div class="dept-metric">
                            <div class="dept-metric-header">
                                <i class="bi bi-people"></i>
                                <span>Empleados</span>
                            </div>
                            <div class="dept-metric-value"><?= $empleados_count ?></div>
                        </div>
                    </div>

                    <!-- Manager & Progress -->
                    <div class="dept-card-footer">
                        <?php if(!empty($d->jefe_area_nombre)): ?>
                            <div class="dept-manager">
                                <div class="dept-manager-info">
                                    <div class="dept-manager-avatar <?= $color ?>">
                                        <?= substr($d->jefe_area_nombre, 0, 1) ?>
                                    </div>
                                    <span class="dept-manager-name"><?= htmlspecialchars($d->jefe_area_nombre) ?></span>
                                </div>
                                <span class="dept-manager-badge <?= $color ?>">Jefe</span>
                            </div>
                        <?php else: ?>
                            <div style="height: 38px;"></div> 
                        <?php endif; ?>

                        <div class="dept-progress-wrapper">
                            <div class="dept-progress-bar">
                                <div class="dept-progress-fill <?= $color ?>" style="width: <?= rand(40, 90) ?>%"></div>
                            </div>
                            <div class="dept-progress-info">
                                <span class="dept-progress-label">Inventario Asignado</span>
                                <span class="dept-progress-value"><?= rand(40, 90) ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hover Action -->
                    <div class="dept-hover-action">
                        <a href="<?= BASE_URL ?>departamentos/ver/<?= $d->id ?>">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- List View (Hidden by default) -->
        <div id="listViewDept" class="departamentos-table-container" style="display: none;">
            <table class="departamentos-table">
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th>Equipos</th>
                        <th>Empleados</th>
                        <th>Jefe de Área</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departamentos as $index => $d): 
                        $color = getDeptColor($index, $colores);
                        $empleados_count = $d->empleados_count ?? 0;
                        $activos_count = $d->equipos_count ?? 0;
                        $codigo = 'DEPT-' . str_pad($d->id, 2, '0', STR_PAD_LEFT);
                    ?>
                        <tr data-search="<?= htmlspecialchars(strtolower($d->nombre)) ?>">
                            <td>
                                <div class="dept-table-info">
                                    <div class="dept-table-icon <?= $color ?>-light text-<?= $color ?>">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="dept-table-name"><?= htmlspecialchars($d->nombre) ?></div>
                                        <div class="dept-table-code"><?= $codigo ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= $activos_count ?></span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= $empleados_count ?></span>
                            </td>
                            <td>
                                <?= !empty($d->jefe_area_nombre) ? htmlspecialchars($d->jefe_area_nombre) : '<span class="text-muted">--</span>' ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>departamentos/ver/<?= $d->id ?>" class="dept-table-action" title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <a href="<?= BASE_URL ?>departamentos/editar/<?= $d->id ?>" class="dept-table-action" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="emptyStateDept" class="empty-state-dept" style="display: none;">
            <i class="bi bi-building-slash" style="font-size: 3rem; color: var(--dept-slate-300);"></i>
            <h3 class="mt-3">No se encontraron departamentos</h3>
            <p class="text-muted">Prueba cambiando el término de búsqueda.</p>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridView = document.getElementById('gridViewDept');
    const listView = document.getElementById('listViewDept');
    const emptyState = document.getElementById('emptyStateDept');
    const searchInput = document.getElementById('searchDepartamentos');
    const btnViewGrid = document.getElementById('btnViewGridDept');
    const btnViewList = document.getElementById('btnViewListDept');
    
    let currentView = 'grid';

    // Toggle view
    btnViewGrid.addEventListener('click', () => {
        gridView.style.display = 'grid';
        listView.style.display = 'none';
        btnViewGrid.classList.add('active');
        btnViewList.classList.remove('active');
        currentView = 'grid';
        filterItems();
    });

    btnViewList.addEventListener('click', () => {
        gridView.style.display = 'none';
        listView.style.display = 'block';
        btnViewList.classList.add('active');
        btnViewGrid.classList.remove('active');
        currentView = 'list';
        filterItems();
    });

    // Search
    searchInput.addEventListener('input', filterItems);

    function filterItems() {
        const term = searchInput.value.toLowerCase();
        let hasMatches = false;

        if (currentView === 'grid') {
            const cards = gridView.querySelectorAll('.dept-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-search');
                if (name.includes(term)) {
                    card.style.display = 'flex';
                    hasMatches = true;
                } else {
                    card.style.display = 'none';
                }
            });
        } else {
            const rows = listView.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const name = row.getAttribute('data-search');
                if (name.includes(term)) {
                    row.style.display = 'table-row';
                    hasMatches = true;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (!hasMatches) {
            emptyState.style.display = 'block';
            if(currentView === 'grid') gridView.style.display = 'none';
            else listView.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            if(currentView === 'grid') gridView.style.display = 'grid';
            else listView.style.display = 'block';
        }
    }
});
</script>
