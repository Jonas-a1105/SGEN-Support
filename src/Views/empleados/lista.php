<?php
// Leer preferencia de paginación
$cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
$paginationPerPage = $cookiePerPage;

// Helper functions
if (!function_exists('getInitials')) {
    function getInitials($firstname, $lastname = '') {
        $initials = strtoupper(substr($firstname, 0, 1));
        if (!empty($lastname)) {
            $initials .= strtoupper(substr($lastname, 0, 1));
        }
        return $initials;
    }
}

if (!function_exists('getAvatarColor')) {
    function getAvatarColor($seed) {
        $colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e'];
        $index = crc32($seed) % count($colors);
        return $colors[abs($index)];
    }
}

if (!function_exists('getDeptClass')) {
    function getDeptClass($dept) {
        $dept = strtolower($dept);
        if(strpos($dept, 'tec') !== false) return 'dept-tech';
        if(strpos($dept, 'rh') !== false || strpos($dept, 'human') !== false) return 'dept-hr';
        if(strpos($dept, 'fin') !== false) return 'dept-finance';
        if(strpos($dept, 'ven') !== false || strpos($dept, 'com') !== false) return 'dept-sales';
        return 'dept-default';
    }
}

// Estadísticas rápidas
$stats = [
    'total' => count($empleados),
    'vinculados' => 0,
    'sin_vincular' => 0
];

// Obtener departamentos únicos
$departamentos_unicos = [];

foreach ($empleados as $e) {
    if (!empty($e->usuario_username)) {
        $stats['vinculados']++;
    } else {
        $stats['sin_vincular']++;
    }
    
    if (!empty($e->departamento_nombre) && !in_array($e->departamento_nombre, $departamentos_unicos)) {
        $departamentos_unicos[] = $e->departamento_nombre;
    }
}

// Helper para obtener iniciales
function getInitials($nombre, $apellido = '') {
    $initials = strtoupper(substr($nombre, 0, 1));
    if (!empty($apellido)) {
        $initials .= strtoupper(substr($apellido, 0, 1));
    }
    return $initials;
}

// Helper para color de avatar basado en nombre
function getAvatarColor($nombre) {
    $colors = ['blue', 'indigo', 'purple', 'emerald', 'amber'];
    return $colors[strlen($nombre) % count($colors)];
}

// Helper para clase de departamento
function getDeptClass($dept) {
    $dept_lower = strtolower($dept ?? '');
    if (strpos($dept_lower, 'inform') !== false) return 'informatica';
    if (strpos($dept_lower, 'catastro') !== false) return 'catastro';
    if (strpos($dept_lower, 'recursos') !== false || strpos($dept_lower, 'rrhh') !== false) return 'rrhh';
    if (strpos($dept_lower, 'ventas') !== false) return 'ventas';
    return 'default';
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/empleados-moderno.css?v=<?= time() ?>">

<div class="empleados-container">
    
    <!-- Header con Stats -->
    <div class="empleados-header">
        <div style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                        <i class="bi bi-people-fill text-white fs-4"></i>
                    </div>
                    <div>
                        <h1 class="empleados-title">Directorio de Personal</h1>
                        <p class="empleados-subtitle mb-0">Gestiona el talento humano de la organización.</p>
                    </div>
                </div>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>empleados/crear" class="btn-nuevo-empleado">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Nuevo Empleado</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon indigo">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <p class="stat-label">Total Empleados</p>
                        <p class="stat-value"><?= $stats['total'] ?></p>
                    </div>
                </div>
                <div class="stat-card emerald">
                    <div class="stat-icon emerald">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <p class="stat-label">Usuarios Activos</p>
                        <p class="stat-value"><?= $stats['vinculados'] ?></p>
                    </div>
                </div>
                <div class="stat-card amber">
                    <div class="stat-icon amber">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div>
                        <p class="stat-label">Sin Usuario</p>
                        <p class="stat-value"><?= $stats['sin_vincular'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="container-fluid px-4 py-4" style="max-width: 1400px;">
        
        <!-- Toolbar -->
        <div class="empleados-toolbar">
            <!-- Department Filters -->
            <div class="dept-filters">
                <button class="dept-filter-btn active" data-dept="todos">Todos</button>
                <?php foreach ($departamentos_unicos as $dept): ?>
                    <button class="dept-filter-btn" data-dept="<?= htmlspecialchars($dept) ?>">
                        <?= htmlspecialchars($dept) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Search -->
                <div class="search-box-emp">
                    <input type="text" id="searchEmpleados" placeholder="Buscar empleado...">
                    <i class="bi bi-search"></i>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle-emp">
                    <button id="btnViewListEmp" title="Vista Lista">
                        <i class="bi bi-list-ul"></i>
                    </button>
                    <button id="btnViewGridEmp" class="active" title="Vista Grid">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div id="gridViewEmp" class="empleados-grid">
            <?php foreach ($empleados as $e): 
                $nombre_completo = trim($e->nombre . ' ' . ($e->apellido ?? ''));
                $initials = getInitials($e->nombre, $e->apellido ?? '');
                $avatar_color = getAvatarColor($nombre_completo);
                $dept_class = getDeptClass($e->departamento_nombre ?? '');
                $is_active = !empty($e->usuario_username);
            ?>
                <div class="empleado-card" 
                     data-dept="<?= htmlspecialchars($e->departamento_nombre ?? '') ?>"
                     data-search="<?= htmlspecialchars(strtolower($nombre_completo . ' ' . ($e->email ?? ''))) ?>">
                    
                    <!-- Status Indicator -->
                    <div class="empleado-status-indicator <?= $is_active ? 'active' : 'inactive' ?>" 
                         title="<?= $is_active ? 'Usuario Activo' : 'Sin usuario vinculado' ?>">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <!-- Header -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="empleado-avatar <?= $avatar_color ?>">
                            <?= $initials ?>
                        </div>
                        <div class="empleado-info">
                            <h3 class="empleado-name"><?= htmlspecialchars($nombre_completo) ?></h3>
                            <p class="empleado-cargo"><?= htmlspecialchars($e->cargo ?? 'Sin cargo') ?></p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="empleado-details">
                        <!-- Department -->
                        <div class="empleado-detail-row">
                            <i class="bi bi-building"></i>
                            <?php if (!empty($e->departamento_nombre)): ?>
                                <span class="dept-badge <?= $dept_class ?>" 
                                      onclick="filterByDept('<?= htmlspecialchars($e->departamento_nombre) ?>')"
                                      title="Filtrar por <?= htmlspecialchars($e->departamento_nombre) ?>">
                                    <?= htmlspecialchars($e->departamento_nombre) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Sin departamento</span>
                            <?php endif; ?>
                        </div>

                        <!-- Email -->
                        <div class="empleado-detail-row email-row" 
                             onclick="copyEmail('<?= htmlspecialchars($e->email ?? '') ?>', this)"
                             title="Clic para copiar email">
                            <i class="bi bi-envelope"></i>
                            <span class="email-text"><?= htmlspecialchars($e->email ?? 'Sin email') ?></span>
                            <i class="bi bi-clipboard copy-icon"></i>
                        </div>

                        <!-- Cedula -->
                        <div class="empleado-detail-row">
                            <i class="bi bi-person-vcard"></i>
                            <span class="font-monospace"><?= htmlspecialchars($e->cedula ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="empleado-card-footer">
                        <div>
                            <span class="acceso-label">Acceso</span>
                            <?php if ($is_active): ?>
                                <div class="acceso-badge active">@<?= htmlspecialchars($e->usuario_username) ?></div>
                            <?php else: ?>
                                <div class="acceso-badge inactive">Inactivo</div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>empleados/editar/<?= $e->id ?>" 
                                   class="empleado-action-btn" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= BASE_URL ?>empleados/eliminar/<?= $e->id ?>" 
                                   class="empleado-action-btn" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar a <?= htmlspecialchars($nombre_completo) ?>?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- List View -->
        <div id="listViewEmp" class="empleados-table-container" style="display: none;">
            <table class="empleados-table">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Contacto</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <th class="text-end">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $e): 
                        $nombre_completo = trim($e->nombre . ' ' . ($e->apellido ?? ''));
                        $initials = getInitials($e->nombre, $e->apellido ?? '');
                        $avatar_color = getAvatarColor($nombre_completo);
                        $dept_class = getDeptClass($e->departamento_nombre ?? '');
                        $is_active = !empty($e->usuario_username);
                    ?>
                        <tr data-dept="<?= htmlspecialchars($e->departamento_nombre ?? '') ?>"
                            data-search="<?= htmlspecialchars(strtolower($nombre_completo . ' ' . ($e->email ?? ''))) ?>">
                            <td>
                                <div class="empleado-table-info">
                                    <div class="empleado-table-avatar empleado-avatar <?= $avatar_color ?>">
                                        <?= $initials ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($nombre_completo) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($e->cedula ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($e->email ?? 'Sin email') ?></td>
                            <td>
                                <?php if (!empty($e->departamento_nombre)): ?>
                                    <span class="dept-badge <?= $dept_class ?>">
                                        <?= htmlspecialchars($e->departamento_nombre) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_active): ?>
                                    <span class="acceso-badge active">Activo</span>
                                <?php else: ?>
                                    <span class="acceso-badge inactive">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="<?= BASE_URL ?>empleados/editar/<?= $e->id ?>" 
                                           class="empleado-action-btn" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>empleados/eliminar/<?= $e->id ?>" 
                                           class="empleado-action-btn" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Eliminar a <?= htmlspecialchars($nombre_completo) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
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
        <div id="emptyStateEmp" class="empty-state-emp" style="display: none;">
            <i class="bi bi-search" style="font-size: 3rem; color: var(--emp-slate-300);"></i>
            <h3 class="mt-3">No se encontraron empleados</h3>
            <p class="text-muted">Prueba cambiando los filtros o el término de búsqueda.</p>
            <button onclick="clearFiltersEmp()" class="btn btn-link">Limpiar filtros</button>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridView = document.getElementById('gridViewEmp');
    const listView = document.getElementById('listViewEmp');
    const emptyState = document.getElementById('emptyStateEmp');
    const btnViewList = document.getElementById('btnViewListEmp');
    const btnViewGrid = document.getElementById('btnViewGridEmp');
    const searchInput = document.getElementById('searchEmpleados');
    const filterBtns = document.querySelectorAll('.dept-filter-btn');
    
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

    // Filtros de departamento
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-dept');
            applyFilters();
        });
    });

    // Filtrar por dept desde badge
    window.filterByDept = function(dept) {
        currentFilter = dept;
        filterBtns.forEach(b => {
            b.classList.remove('active');
            if (b.getAttribute('data-dept') === dept) {
                b.classList.add('active');
            }
        });
        applyFilters();
    };

    // Búsqueda
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.toLowerCase();
        applyFilters();
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

    // Aplicar filtros
    function applyFilters() {
        // Reset indices
        filteredIndices = [];
        const cards = gridView.querySelectorAll('.empleado-card');
        const rows = listView.querySelectorAll('tbody tr');
        let visibleCount = 0;

        // 1. Filtrar (Identify matches)
        cards.forEach((card, index) => {
            const dept = card.getAttribute('data-dept');
            const searchText = card.getAttribute('data-search');
            const matchesFilter = currentFilter === 'todos' || dept === currentFilter;
            const matchesSearch = searchText.includes(currentSearch);
            
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

        // 2. Paginar (Show slice)
        const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
        const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;
        const visibleIndices = filteredIndices.slice(start, end);
        
        visibleIndices.forEach(idx => {
            cards[idx].style.display = 'flex';
            if(rows[idx]) rows[idx].style.display = '';
        });

        // 3. Actualizar UI
        document.getElementById('visibleCountDisplay').textContent = visibleIndices.length;
        document.getElementById('totalCountDisplay').textContent = cards.length; // Total loaded

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

        // Empty state
        if (visibleIndices.length === 0) {
            emptyState.style.display = 'block';
            if (currentView === 'grid') gridView.style.display = 'none';
            else listView.style.display = 'none';
            document.getElementById('paginationFooter').style.display = 'none'; // Hide footer if filtered out
        } else {
            emptyState.style.display = 'none';
            if (currentView === 'grid') gridView.style.display = 'grid';
            else listView.style.display = 'block';
            document.getElementById('paginationFooter').style.display = 'flex';
        }
    }

    // Limpiar filtros
    window.clearFiltersEmp = function() {
        currentFilter = 'todos';
        currentSearch = '';
        searchInput.value = '';
        filterBtns.forEach(b => {
            b.classList.remove('active');
            if (b.getAttribute('data-dept') === 'todos') {
                b.classList.add('active');
            }
        });
        currentPage = 1;
        applyFilters();
    };
    
    // Initial call
    applyFilters();

    // Copiar email
    window.copyEmail = function(email, element) {
        if (!email) return;
        
        navigator.clipboard.writeText(email).then(() => {
            const originalHTML = element.innerHTML;
            const copyIcon = element.querySelector('.copy-icon');
            if (copyIcon) {
                copyIcon.outerHTML = '<span class="copied-feedback"><i class="bi bi-check"></i> Copiado</span>';
            }
            
            setTimeout(() => {
                element.innerHTML = originalHTML;
            }, 2000);
        });
    };
});
</script>