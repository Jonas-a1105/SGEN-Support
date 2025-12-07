<?php 
$totalUsers = count($usuarios);
$activeUsers = $totalUsers;
$adminCount = count(array_filter($usuarios, fn($u) => $u->rol === 'admin'));
$tecnicoCount = count(array_filter($usuarios, fn($u) => $u->rol === 'tecnico'));

// Leer preferencia de paginación
$cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
$paginationPerPage = $cookiePerPage; // Variable para usar en JS
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/modern-modal.css">


<div style="background: #f8fafc; padding: 2rem; min-height: 100vh;">
    <div style="max-width: 1150px; margin: 0 auto;">

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

        <!-- Stats Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #e0e7ff; color: #6366f1; font-size: 1.25rem;">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Total Usuarios</p>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $totalUsers ?></h3>
                </div>
            </div>
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #ede9fe; color: #8b5cf6; font-size: 1.25rem;">
                    <i class="bi bi-shield"></i>
                </div>
                <div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Administradores</p>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $adminCount ?></h3>
                </div>
            </div>
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
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
                <div style="position: relative; max-width: 300px;">
                    <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="userSearchInput" placeholder="Buscar usuario..." 
                           style="width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: white; color: #1e293b;">
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
                        <tr class="user-row" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; color: #1e293b;">
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
                                    <a href="javascript:void(0)" 
                                       class="btn-delete"
                                       style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #dc2626; text-decoration: none;"
                                       title="Eliminar"
                                       data-name="<?= htmlspecialchars($u->username) ?>"
                                       data-delete-url="<?= BASE_URL ?>usuarios/eliminar/<?= $u->id ?>"
                                       data-no-global-delete="true">
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
                <div style="position: relative;">
                    <!-- Carousel Nav Prev -->
                    <button onclick="scrollCarousel(-300)" style="position: absolute; left: -20px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.1); color: #64748b;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    
                    <!-- Cards Grid -->
                    <div id="cardsGrid" style="display: flex; gap: 1rem; overflow-x: auto; scroll-behavior: smooth; padding: 0.5rem; scrollbar-width: none;">
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
                        <div class="user-card" style="flex: 0 0 280px; background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; transition: all 0.2s;">
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
                                <a href="javascript:void(0)" 
                                   class="btn-delete"
                                   style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #dc2626; text-decoration: none;"
                                   title="Eliminar"
                                   data-name="<?= htmlspecialchars($u->username) ?>"
                                   data-delete-url="<?= BASE_URL ?>usuarios/eliminar/<?= $u->id ?>"
                                   data-no-global-delete="true">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Carousel Nav Next -->
                    <button onclick="scrollCarousel(300)" style="position: absolute; right: -20px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.1); color: #64748b;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
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

    </div>
</div>


<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="mm-backdrop">
    <div class="mm-modal">
        <!-- Header -->
        <div class="mm-header">
            <div class="mm-icon-wrapper">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="mm-title">Eliminar Usuario</h3>
                <p class="mm-subtitle">Esta acción es irreversible</p>
            </div>
            <button class="mm-close-btn" onclick="closeDeleteModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="mm-body">
            <div class="mm-message">
                Estás a punto de eliminar permanentemente al usuario <span id="deleteTargetName" class="mm-highlight">TARGET_NAME</span>. 
                Esto borrará su historial, accesos y datos asociados.
            </div>
            <div class="mm-input-group">
                <label class="mm-label">Para confirmar, escribe "ELIMINAR" abajo:</label>
                <div class="mm-input-wrapper">
                    <input type="text" id="deleteInput" class="mm-input" placeholder="ELIMINAR" autocomplete="off">
                    <i class="bi bi-unlock-fill mm-unlock-icon"></i>
                </div>
            </div>
            <div id="deleteNotice" class="mm-notice">
                <i class="bi bi-info-circle" style="margin-top: 2px;"></i>
                <p style="margin:0;">El botón se activará solo cuando el texto coincida exactamente.</p>
            </div>
        </div>
        <!-- Footer -->
        <div class="mm-footer">
            <button class="mm-btn-cancel" onclick="closeDeleteModal()">Cancelar</button>
            <button id="confirmDeleteBtn" class="mm-btn-delete disabled" disabled>
                <i class="bi bi-trash"></i> Eliminar definitivamente
            </button>
        </div>
    </div>
</div>

<style>

#cardsGrid::-webkit-scrollbar { display: none; }
.user-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1); transform: translateY(-4px); }
</style>


<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="mm-backdrop">
    <div class="mm-modal">
        <!-- Header -->
        <div class="mm-header">
            <div class="mm-icon-wrapper">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="mm-title">Eliminar Usuario</h3>
                <p class="mm-subtitle">Esta acción es irreversible</p>
            </div>
            <button class="mm-close-btn" onclick="closeDeleteModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="mm-body">
            <div class="mm-message">
                Estás a punto de eliminar permanentemente al usuario <span id="deleteTargetName" class="mm-highlight">TARGET_NAME</span>. 
                Esto borrará su historial, accesos y datos asociados.
            </div>
            <div class="mm-input-group">
                <label class="mm-label">Para confirmar, escribe "ELIMINAR" abajo:</label>
                <div class="mm-input-wrapper">
                    <input type="text" id="deleteInput" class="mm-input" placeholder="ELIMINAR" autocomplete="off">
                    <i class="bi bi-unlock-fill mm-unlock-icon"></i>
                </div>
            </div>
            <div id="deleteNotice" class="mm-notice">
                <i class="bi bi-info-circle" style="margin-top: 2px;"></i>
                <p style="margin:0;">El botón se activará solo cuando el texto coincida exactamente.</p>
            </div>
        </div>
        <!-- Footer -->
        <div class="mm-footer">
            <button class="mm-btn-cancel" onclick="closeDeleteModal()">Cancelar</button>
            <button id="confirmDeleteBtn" class="mm-btn-delete disabled" disabled>
                <i class="bi bi-trash"></i> Eliminar definitivamente
            </button>
        </div>
    </div>
</div>







<script>
function showTableView() {
    document.getElementById('tableView').style.display = 'block';
    document.getElementById('cardsView').style.display = 'none';
    document.getElementById('btnTableView').style.background = '#6366f1';
    document.getElementById('btnTableView').style.color = 'white';
    document.getElementById('btnTableView').style.borderColor = '#6366f1';
    document.getElementById('btnCardsView').style.background = 'white';
    document.getElementById('btnCardsView').style.color = '#64748b';
    document.getElementById('btnCardsView').style.borderColor = '#e2e8f0';
}

function showCardsView() {
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('cardsView').style.display = 'block';
    document.getElementById('btnCardsView').style.background = '#6366f1';
    document.getElementById('btnCardsView').style.color = 'white';
    document.getElementById('btnCardsView').style.borderColor = '#6366f1';
    document.getElementById('btnTableView').style.background = 'white';
    document.getElementById('btnTableView').style.color = '#64748b';
    document.getElementById('btnTableView').style.borderColor = '#e2e8f0';
}

function scrollCarousel(amount) {
    document.getElementById('cardsGrid').scrollBy({ left: amount, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');

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

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1; // Reset page on search
            applyFilters();
        });
    }

    // Aplicar filtros
    function applyFilters() {
        const term = searchInput ? searchInput.value.toLowerCase() : '';
        const rows = document.querySelectorAll('.user-row');
        const cards = document.querySelectorAll('.user-card');
        
        filteredIndices = [];
        
        // 1. Filtrar (Identify matches)
        // Usamos rows como referencia para indices, cards deben coincidir en cantidad
        rows.forEach((row, index) => {
            const card = cards[index];
            const text = row.textContent.toLowerCase();
            const matchesSearch = text.includes(term);
            
            // Ocultar inicialmente
            row.style.display = 'none';
            if(card) card.style.display = 'none'; // card might be inside a carousel div which is problematic if mixed logic? 
            // cards in "Cards View" are inside #cardsGrid. rows in #tableView.
            // Logic assumes 1:1 correspondence and ordering.
            
            if (matchesSearch) {
                 filteredIndices.push(index);
            }
        });

        const totalVisible = filteredIndices.length;
        const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;
        
        if (currentPage > totalPages) currentPage = 1;

        // 2. Paginar
        const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
        const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;
        const visibleIndices = filteredIndices.slice(start, end);
        
        visibleIndices.forEach(idx => {
            if(rows[idx]) rows[idx].style.display = '';
            if(cards[idx]) {
                cards[idx].style.display = 'block'; // Or flex/etc. user-card has default display block or flex?
                // user-card has style="flex: 0 0 280px..." inline.
                // We should respect that but display:none overrides it. 
                // Restore logic: if it was flex, display:flex. But card itself is a div. div is block.
                // It's inside #cardsGrid (display:flex).
                // Safest is to remove display:none (set to '').
                cards[idx].style.display = ''; 
            }
        });

        // 3. UI
        document.getElementById('visibleCountDisplay').textContent = visibleIndices.length;
        document.getElementById('totalCountDisplay').textContent = rows.length;

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
        
        // Hide footer if no items?
        const footer = document.getElementById('paginationFooter');
        if (visibleIndices.length === 0) footer.style.display = 'none';
        else footer.style.display = 'flex';
    }

    // Initial call
    applyFilters();

    // --- MODAL LOGIC ---
    const modal = document.getElementById('deleteModal');
    const input = document.getElementById('deleteInput');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const notice = document.getElementById('deleteNotice');
    let deleteTargetUrl = '';

    // Delegate Click for Delete Buttons (to handle pagination updates)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete');
        if (btn) {
            e.preventDefault();
            deleteTargetUrl = btn.dataset.deleteUrl; // Read from dataset
            document.getElementById('deleteTargetName').textContent = btn.dataset.name;
            
            // Reset State
            input.value = '';
            input.classList.remove('match');
            confirmBtn.classList.add('disabled');
            confirmBtn.classList.remove('active');
            confirmBtn.disabled = true;
            notice.style.display = 'flex';
            confirmBtn.innerHTML = '<i class="bi bi-trash"></i> Eliminar definitivamente';
            confirmBtn.style.opacity = '1';
            
            modal.classList.add('show');
        }
    });

    // Input Validation
    if(input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            
            if (this.value === 'ELIMINAR') {
                this.classList.add('match');
                confirmBtn.classList.remove('disabled');
                confirmBtn.classList.add('active');
                confirmBtn.disabled = false;
                notice.style.display = 'none';
            } else {
                this.classList.remove('match');
                confirmBtn.classList.add('disabled');
                confirmBtn.classList.remove('active');
                confirmBtn.disabled = true;
                notice.style.display = 'flex';
            }
        });
    }

    // Confirm Action
    if(confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (confirmBtn.disabled) return;
            
            // Show loading state
            confirmBtn.innerHTML = '<div class="mm-spinner"></div> Eliminando...';
            confirmBtn.style.opacity = '0.9';
            
            // Navigate
            setTimeout(() => {
                window.location.href = deleteTargetUrl;
            }, 800); // Visual delay
        });
    }

    window.closeDeleteModal = function() {
        modal.classList.remove('show');
    }
});
</script>
</content>