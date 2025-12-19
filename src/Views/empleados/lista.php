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



<div class="empleados-container">
    
    <!-- Header con Stats -->
    <!-- Header con Stats -->
    <div class="empleados-header">
        <div class="empleados-header-container">
            <div class="d-flex align-items-center gap-2">
                <div class="empleados-logo">
                    <i class="bi bi-people-fill"></i>
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
    </div>

    <main id="empleadosMain" class="empleados-main-container">
        
        <!-- Main Content Card Container -->
        <div class="empleados-content-card">
        
        <!-- Stats Grid -->
        <div class="stats-grid" style="margin-bottom: 1.5rem;">
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
        
        <!-- Toolbar -->
        <div class="empleados-toolbar">
            <!-- Left: Bulk Controls + Department Filters -->
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Bulk Delete Controls -->
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
                
                <!-- Department Filters -->
                <div class="dept-filters">
                    <button class="dept-filter-btn active" data-dept="todos">Todos</button>
                    <?php foreach ($departamentos_unicos as $dept): ?>
                        <button class="dept-filter-btn" data-dept="<?= htmlspecialchars($dept) ?>">
                            <?= htmlspecialchars($dept) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
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
                     data-bulk-item
                     data-empleado-id="<?= $e->id ?>"
                     data-dept="<?= htmlspecialchars($e->departamento_nombre ?? '') ?>"
                     data-search="<?= htmlspecialchars(strtolower($nombre_completo . ' ' . ($e->email ?? ''))) ?>">
                    
                    <!-- Bulk Checkbox -->
                    <div class="bulk-checkbox">
                        <i class="bi bi-check-circle-fill icon-checked"></i>
                        <i class="bi bi-circle icon-unchecked"></i>
                    </div>
                    
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
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>empleados/editar/<?= $e->id ?>" 
                                   class="empleado-action-btn btn-action-edit" 
                                   data-no-global-delete="true"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= BASE_URL ?>empleados/eliminar/<?= $e->id ?>" 
                                   class="empleado-action-btn btn-delete" 
                                   data-turbo="false"
                                   data-name="<?= htmlspecialchars($nombre_completo) ?>"
                                   title="Eliminar">
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
                        <tr data-bulk-item
                            data-empleado-id="<?= $e->id ?>"
                            data-dept="<?= htmlspecialchars($e->departamento_nombre ?? '') ?>"
                            data-search="<?= htmlspecialchars(strtolower($nombre_completo . ' ' . ($e->email ?? ''))) ?>">
                            <td style="position: relative;">
                                <!-- Bulk Checkbox -->
                                <div class="bulk-checkbox">
                                    <i class="bi bi-check-circle-fill icon-checked"></i>
                                    <i class="bi bi-circle icon-unchecked"></i>
                                </div>
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
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= BASE_URL ?>empleados/editar/<?= $e->id ?>" 
                                           class="empleado-action-btn btn-action-edit" 
                                           data-no-global-delete="true"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>empleados/eliminar/<?= $e->id ?>" 
                                           class="empleado-action-btn btn-delete" 
                                           data-turbo="false"
                                           data-name="<?= htmlspecialchars($nombre_completo) ?>"
                                           title="Eliminar">
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
        
        </div><!-- End empleados-content-card -->

    </main>
</div>

<!-- Empleados Logic -->
<script src="<?= BASE_URL ?>js/empleados.js?v=2.6.0"></script>
<script>
    // Helper simple para usar en el onclick inline
    // (Removed: We now use Global Delete Interceptor in app.js via .btn-delete class)

    
    // Initialize Bulk Delete (Run immediately)
    if (window.BulkDelete) {
        BulkDelete.init({
            containerId: 'empleadosMain',
            itemSelector: '[data-bulk-item]',
            itemIdAttribute: 'data-empleado-id',
            deleteUrl: BASE_URL + 'empleados/eliminar_masivo',
            entityName: 'empleados',
            entityNameSingular: 'empleado',
            toggleId: 'bulkModeToggle',
            selectAllId: 'bulkSelectAll',
            selectAllContainerId: 'bulkSelectAllContainer',
            deleteButtonId: 'bulkDeleteBtn',
            countSpanId: 'bulkSelectedCount'
        });
    }
</script>