<?php 
$totalUsers = count($usuarios);
$activeUsers = $totalUsers;
$adminCount = count(array_filter($usuarios, fn($u) => $u->rol === 'admin'));
$tecnicoCount = count(array_filter($usuarios, fn($u) => $u->rol === 'tecnico'));

// Leer preferencia de paginación
$cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
$paginationPerPage = $cookiePerPage; // Variable para usar en JS
?>



<div id="usuariosContainer" class="users-modern-container" data-per-page="<?= $paginationPerPage ?>">
    <div class="users-modern-wrapper">

        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="padding: 0.5rem; background: #e0e7ff; border-radius: 0.5rem; color: #6366f1; font-size: 1.25rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Gestión de Usuarios</h1>
                    <p style="color: #64748b; margin: 0; font-size: 0.875rem;">Administra el acceso y roles de tu equipo.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>usuarios/crear" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background: #6366f1; color: white; border-radius: 0.75rem; text-decoration: none; font-weight: 500;">
                <i class="bi bi-plus-lg"></i> Nuevo Usuario
            </a>
        </div>

        <!-- Main Content Card Container -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">
        
        <!-- Stats Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #e0e7ff; color: #6366f1; font-size: 1.25rem;">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Total Usuarios</p>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $totalUsers ?></h3>
                </div>
            </div>
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #ede9fe; color: #8b5cf6; font-size: 1.25rem;">
                    <i class="bi bi-shield"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Administradores</p>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $adminCount ?></h3>
                </div>
            </div>
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #d1fae5; color: #10b981; font-size: 1.25rem;">
                    <i class="bi bi-wrench"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Técnicos</p>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $tecnicoCount ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Main Card -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden;">
            
            <!-- Toolbar -->
            <div style="padding: 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <!-- Left: Bulk Controls + Search -->
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
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
                    
                    <!-- Search -->
                    <div style="position: relative; max-width: 300px;">
                        <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="userSearchInput" placeholder="Buscar usuario..." 
                               style="width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: white; color: #1e293b;">
                    </div>
                </div>
                
                <!-- View Toggle -->
                <div style="display: flex; gap: 0.5rem;">
                    <button id="btnTableView" onclick="showTableView()" style="padding: 0.5rem 0.75rem; background: #6366f1; border: 1px solid #6366f1; border-radius: 0.5rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; color: white;">
                        <i class="bi bi-list-ul"></i> Tabla
                    </button>
                    <button id="btnCardsView" onclick="showCardsView()" style="padding: 0.5rem 0.75rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; color: #64748b;">
                        <i class="bi bi-grid-3x3-gap"></i> Cards
                    </button>
                </div>
            </div>

            <!-- Table View -->
            <div id="tableView" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; table-layout: fixed;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Usuario</th>
                            <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Rol & Depto</th>
                            <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Estado</th>
                            <th style="padding: 0.875rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <?php
                            $avatarBg = match($u->rol) {
                                'admin' => '#e0e7ff',
                                'tecnico' => '#dbeafe',
                                default => '#d1fae5'
                            };
                            $avatarColor = match($u->rol) {
                                'admin' => '#6366f1',
                                'tecnico' => '#3b82f6',
                                default => '#10b981'
                            };
                            $roleBg = match($u->rol) {
                                'admin' => '#ede9fe',
                                'tecnico' => '#dbeafe',
                                default => '#fef3c7'
                            };
                            $roleColor = match($u->rol) {
                                'admin' => '#7c3aed',
                                'tecnico' => '#3b82f6',
                                default => '#b45309'
                            };
                            $displayName = !empty($u->empleado_nombre) 
                                ? trim($u->empleado_nombre . ' ' . ($u->empleado_apellido ?? ''))
                                : $u->username;
                            $initials = strtoupper(substr($displayName, 0, 2));
                            $email = $u->empleado_email ?? null;
                        ?>
                        <tr class="user-row" data-bulk-item data-user-id="<?= $u->id ?>" style="border-bottom: 1px solid #f1f5f9; position: relative;">
                            <td style="padding: 1rem; color: #1e293b; position: relative;">
                                <!-- Bulk Checkbox -->
                                <div class="bulk-checkbox">
                                    <i class="bi bi-check-circle-fill icon-checked"></i>
                                    <i class="bi bi-circle icon-unchecked"></i>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; background: <?= $avatarBg ?>; color: <?= $avatarColor ?>;">
                                        <?= $initials ?>
                                    </div>
                                    <div>
                                        <p style="font-weight: 600; color: #0f172a; margin: 0;"><?= htmlspecialchars($displayName) ?></p>
                                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.125rem 0 0 0;">@<?= htmlspecialchars($u->username) ?></p>
                                        <?php if ($email): ?>
                                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;"><i class="bi bi-envelope"></i> <?= htmlspecialchars($email) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem; color: #1e293b;">
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 500; border-radius: 0.25rem; background: <?= $roleBg ?>; color: <?= $roleColor ?>;">
                                    <?php if($u->rol === 'admin'): ?><i class="bi bi-shield"></i><?php endif; ?>
                                    <?= ucfirst($u->rol) ?>
                                </span>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    <i class="bi bi-building"></i>
                                    <?= !empty($u->departamento_nombre) ? htmlspecialchars($u->departamento_nombre) : 'Sin Asignar' ?>
                                </div>
                            </td>
                            <td style="padding: 1rem; color: #1e293b;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                                    <span style="font-size: 0.875rem; color: #475569;">Activo</span>
                                </div>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="<?= BASE_URL ?>usuarios/editar/<?= $u->id ?>" 
                                       style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fef3c7; color: #d97706; text-decoration: none;"
                                       title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <?php if ($u->id != 1): ?>
                                    <a href="<?= BASE_URL ?>usuarios/eliminar/<?= $u->id ?>" 
                                       class="btn-delete"
                                       data-no-global-delete="true"
                                       style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #dc2626; text-decoration: none;"
                                       title="Eliminar"
                                       onclick="return confirmDeleteUser(event, this.href, '<?= addslashes(htmlspecialchars($u->username)) ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cards View -->
            <div id="cardsView" style="display: none; padding: 1.5rem;">
                <div>
                    <!-- Cards Grid -->
                    <div id="cardsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; padding: 0.5rem;">
                        <?php foreach ($usuarios as $u): ?>
                        <?php
                            $avatarBg = match($u->rol) {
                                'admin' => '#e0e7ff',
                                'tecnico' => '#dbeafe',
                                default => '#d1fae5'
                            };
                            $avatarColor = match($u->rol) {
                                'admin' => '#6366f1',
                                'tecnico' => '#3b82f6',
                                default => '#10b981'
                            };
                            $roleBg = match($u->rol) {
                                'admin' => '#ede9fe',
                                'tecnico' => '#dbeafe',
                                default => '#fef3c7'
                            };
                            $roleColor = match($u->rol) {
                                'admin' => '#7c3aed',
                                'tecnico' => '#3b82f6',
                                default => '#b45309'
                            };
                            $displayName = !empty($u->empleado_nombre) 
                                ? trim($u->empleado_nombre . ' ' . ($u->empleado_apellido ?? ''))
                                : $u->username;
                            $initials = strtoupper(substr($displayName, 0, 2));
                            $email = $u->empleado_email ?? null;
                        ?>
                        <div class="user-card" 
                             data-bulk-item
                             data-user-id="<?= $u->id ?>"
                             style="flex: 0 0 280px; background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; transition: all 0.2s; position: relative;">
                            <!-- Bulk Checkbox -->
                            <div class="bulk-checkbox" style="top: 15px; left: 15px;">
                                <i class="bi bi-check-circle-fill icon-checked"></i>
                                <i class="bi bi-circle icon-unchecked"></i>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <div style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; background: <?= $avatarBg ?>; color: <?= $avatarColor ?>;">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <h4 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;"><?= htmlspecialchars($displayName) ?></h4>
                                    <p style="font-size: 0.75rem; color: #64748b; margin: 0;">@<?= htmlspecialchars($u->username) ?></p>
                                    <?php if ($email): ?>
                                    <p style="font-size: 0.75rem; color: #64748b; margin: 0;"><i class="bi bi-envelope"></i> <?= htmlspecialchars($email) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 500; border-radius: 0.25rem; background: <?= $roleBg ?>; color: <?= $roleColor ?>;">
                                    <?php if($u->rol === 'admin'): ?><i class="bi bi-shield"></i><?php endif; ?>
                                    <?= ucfirst($u->rol) ?>
                                </span>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                                    <i class="bi bi-building"></i>
                                    <?= !empty($u->departamento_nombre) ? htmlspecialchars($u->departamento_nombre) : 'Sin Asignar' ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                                <a href="<?= BASE_URL ?>usuarios/editar/<?= $u->id ?>" 
                                   style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fef3c7; color: #d97706; text-decoration: none;"
                                   title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <?php if ($u->id != 1): ?>
                                <a href="<?= BASE_URL ?>usuarios/eliminar/<?= $u->id ?>" 
                                   class="btn-delete"
                                   data-no-global-delete="true"
                                   style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #dc2626; text-decoration: none;"
                                   title="Eliminar"
                                   onclick="return confirmDeleteUser(event, this.href, '<?= addslashes(htmlspecialchars($u->username)) ?>')">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Modern Pagination Footer -->
            <div id="paginationFooter" style="padding: 1rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
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
        </div>
        
        </div><!-- End content card container -->

    </div>
</div>



<!-- Bulk Delete Assets -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/bulk-delete.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>js/bulk-delete.js?v=<?= time() ?>"></script>

<!-- Modern Simple Delete Modal Integration -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/modal-simple-delete-modern.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>js/modal-simple-delete-modern.js?v=<?= time() ?>"></script>

<script src="<?= BASE_URL ?>js/usuarios.js?v=<?= time() ?>"></script>
<script>
    function confirmDeleteUser(e, url, name) {
        e.preventDefault();
        SimpleDeleteModal.open(url, {
            type: 'Usuario',
            name: name,
            warning: 'Esta acción eliminará el acceso del usuario al sistema.'
        });
        return false;
    }
    
    // Initialize Bulk Delete
    document.addEventListener('DOMContentLoaded', function() {
        BulkDelete.init({
            containerId: 'usuariosContainer',
            itemSelector: '[data-bulk-item]',
            itemIdAttribute: 'data-user-id',
            deleteUrl: BASE_URL + 'usuarios/eliminar_masivo',
            entityName: 'usuarios',
            entityNameSingular: 'usuario',
            toggleId: 'bulkModeToggle',
            selectAllId: 'bulkSelectAll',
            selectAllContainerId: 'bulkSelectAllContainer',
            deleteButtonId: 'bulkDeleteBtn',
            countSpanId: 'bulkSelectedCount'
        });
    });
</script>

</content>