<?php
/**
 * Vista de Lista de Soportes - Mesa de Ayuda Moderna
 * Diseño tipo feed con header oscuro, KPIs, filtros y tickets con prioridad visual
 */

// Leer preferencia de paginación
$cookiePerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;
$paginationPerPage = $cookiePerPage; // Variable para usar en JS

// Required Helpers
require_once __DIR__ . '/../../Helpers/ColorHelper.php';
require_once __DIR__ . '/../../Helpers/BadgeHelper.php';
require_once __DIR__ . '/../../Helpers/PermissionHelper.php';
require_once __DIR__ . '/../../Helpers/ViewHelper.php';

use App\Helpers\{ColorHelper, BadgeHelper, PermissionHelper, ViewHelper};

// Obtener datos
$kpis = $kpis ?? ['pendientes' => 0, 'en_proceso' => 0, 'resueltos' => 0, 'alta_prioridad' => 0];
$categorias = $categorias ?? [];

// Calcular "Mis Asignaciones" si hay técnico logueado
$mis_asignados = 0;
$usuario_actual = $_SESSION['username'] ?? '';
foreach ($soportes as $s) {
    if (strtolower($s->tecnico_asignado ?? '') === strtolower($usuario_actual) && $s->estado !== 'resuelto') {
        $mis_asignados++;
    }
}

// Helper para tiempo relativo
function tiempoRelativo($fecha) {
    $ahora = new DateTime();
    $fecha_dt = new DateTime($fecha);
    $diff = $ahora->diff($fecha_dt);
    
    if ($diff->days == 0) {
        if ($diff->h == 0) {
            return "Hace " . max(1, $diff->i) . " min";
        }
        return "Hace " . $diff->h . " hora" . ($diff->h > 1 ? 's' : '');
    } elseif ($diff->days == 1) {
        return "Hace 1 día";
    } elseif ($diff->days < 7) {
        return "Hace " . $diff->days . " días";
    } else {
        return date('d/m/Y', strtotime($fecha));
    }
}

// Helper para clase de categoría
function getCategoriaClass($cat) {
    $cat_lower = strtolower($cat ?? '');
    if (strpos($cat_lower, 'hardware') !== false) return 'hardware';
    if (strpos($cat_lower, 'red') !== false || strpos($cat_lower, 'network') !== false) return 'redes';
    if (strpos($cat_lower, 'software') !== false) return 'software';
    return 'default';
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/helpdesk-moderno.css?v=<?= time() ?>">

<div class="helpdesk-container">
    
    <!-- Header Blanco -->
    <nav class="helpdesk-nav">
        <div style="max-width: 1150px; margin: 0 auto; width: 100%; display: flex; justify-content: space-between; align-items: center;">
            <div class="helpdesk-brand">
                <div class="helpdesk-brand-icon">
                    <i class="bi bi-ticket-perforated-fill text-white"></i>
                </div>
                <div>
                    <h1>Gestión de Soportes</h1>
                    <p>Control y seguimiento de tickets</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>reportes/soportes" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex">
                        <i class="bi bi-download me-1"></i>Exportar
                    </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>soportes/crear" class="btn-nuevo-ticket">
                    <i class="bi bi-plus-lg"></i>
                    <span class="d-none d-sm-inline">Nuevo Ticket</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-4 py-4" style="max-width: 1200px; margin: 0 auto;">
        
        <!-- KPI Cards -->
        <div class="helpdesk-kpis">
            <div class="helpdesk-kpi critical">
                <div class="helpdesk-kpi-bg">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <p class="helpdesk-kpi-label">Críticos Pendientes</p>
                    <h2 class="helpdesk-kpi-value critical"><?= $kpis['alta_prioridad'] ?></h2>
                </div>
                <div class="helpdesk-kpi-icon rose">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>

            <div class="helpdesk-kpi">
                <div>
                    <p class="helpdesk-kpi-label">Cola General</p>
                    <h2 class="helpdesk-kpi-value amber"><?= $kpis['pendientes'] ?></h2>
                </div>
                <div class="helpdesk-kpi-icon amber">
                    <i class="bi bi-list-ul"></i>
                </div>
            </div>

            <div class="helpdesk-kpi">
                <div>
                    <p class="helpdesk-kpi-label">En Proceso</p>
                    <h2 class="helpdesk-kpi-value blue"><?= $kpis['en_proceso'] ?></h2>
                </div>
                <div class="helpdesk-kpi-icon blue">
                    <i class="bi bi-gear-fill"></i>
                </div>
            </div>

            <div class="helpdesk-kpi">
                <div>
                    <p class="helpdesk-kpi-label">Mis Asignaciones</p>
                    <h2 class="helpdesk-kpi-value emerald"><?= $mis_asignados ?></h2>
                </div>
                <div class="helpdesk-kpi-icon emerald">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="helpdesk-filters">
            <div class="helpdesk-tabs">
                <button class="helpdesk-tab active" data-filter="todos">Todos</button>
                <button class="helpdesk-tab" data-filter="pendiente">Pendientes</button>
                <button class="helpdesk-tab" data-filter="mis_tickets">Mis Tickets</button>
                <button class="helpdesk-tab" data-filter="resuelto">Resueltos</button>
            </div>

            <div class="helpdesk-search">
                <i class="bi bi-search"></i>
                <input type="text" id="searchTickets" placeholder="Buscar ticket, serial o problema...">
            </div>
        </div>

        <!-- Ticket Feed -->
        <div class="helpdesk-feed">
            <div id="ticketFeed">
                <?php foreach ($soportes as $s): 
                    $prioridad_class = strtolower($s->prioridad ?? 'media');
                    $estado_class = strtolower(str_replace(' ', '_', $s->estado ?? 'pendiente'));
                    $categoria_class = getCategoriaClass($s->categoria_nombre ?? '');
                    $tiene_tecnico = !empty($s->tecnico_asignado);
                    $es_mi_ticket = strtolower($s->tecnico_asignado ?? '') === strtolower($usuario_actual);
                    $tiempo = tiempoRelativo($s->fecha);
                ?>
                    <div class="helpdesk-ticket priority-<?= $prioridad_class ?>" 
                         data-estado="<?= $estado_class ?>"
                         data-tecnico="<?= $es_mi_ticket ? 'mio' : 'otro' ?>"
                         data-search="<?= htmlspecialchars(strtolower(($s->descripcion ?? '') . ' ' . ($s->equipo_serial ?? '') . ' ' . ($s->solicitante_nombre ?? ''))) ?>">
                        
                        <!-- Status Icon -->
                        <div class="helpdesk-status-icon <?= $estado_class ?>" 
                             title="<?= ucfirst(str_replace('_', ' ', $s->estado)) ?>">
                            <?php if ($s->estado === 'resuelto'): ?>
                                <i class="bi bi-check-circle-fill"></i>
                            <?php elseif ($s->estado === 'en_proceso'): ?>
                                <i class="bi bi-arrow-repeat"></i>
                            <?php elseif ($s->estado === 'espera'): ?>
                                <i class="bi bi-pause-circle-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-clock"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Content -->
                        <div class="helpdesk-ticket-content">
                            <div class="helpdesk-ticket-meta">
                                <span class="helpdesk-ticket-id">#T-<?= $s->id ?></span>
                                
                                <span class="helpdesk-category <?= $categoria_class ?>">
                                    <?php if ($categoria_class === 'hardware'): ?>
                                        <i class="bi bi-pc-display"></i>
                                    <?php elseif ($categoria_class === 'redes'): ?>
                                        <i class="bi bi-wifi"></i>
                                    <?php elseif ($categoria_class === 'software'): ?>
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    <?php else: ?>
                                        <i class="bi bi-tag"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($s->categoria_nombre ?? 'General') ?>
                                </span>

                                <?php if (strtolower($s->prioridad ?? '') === 'alta'): ?>
                                    <span class="helpdesk-urgente">
                                        <i class="bi bi-exclamation-triangle-fill"></i> URGENTE
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="helpdesk-ticket-title" title="<?= htmlspecialchars($s->descripcion) ?>">
                                <?= htmlspecialchars(substr($s->descripcion ?? 'Sin descripción', 0, 80)) ?>
                                <?= strlen($s->descripcion ?? '') > 80 ? '...' : '' ?>
                            </h3>

                            <div class="helpdesk-ticket-info">
                                <span title="Solicitante">
                                    <i class="bi bi-person"></i>
                                    <?php if (!empty($s->solicitante_id)): ?>
                                        <a href="<?= BASE_URL ?>empleados/ver/<?= $s->solicitante_id ?>" class="helpdesk-solicitante-link">
                                            <?= htmlspecialchars($s->solicitante_nombre) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin solicitante</span>
                                    <?php endif; ?>
                                    • <?= htmlspecialchars($s->departamento_nombre ?? '') ?>
                                </span>
                                <span class="d-none d-sm-inline" style="color: var(--help-slate-300);">|</span>
                                <span class="d-none d-sm-flex" title="Equipo">
                                    <?= htmlspecialchars(($s->equipo_tipo ?? 'Equipo') . ' - ' . ($s->equipo_serial ?? 'S/N')) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Actions Area -->
                        <div class="helpdesk-ticket-actions">
                            <!-- Assignee -->
                            <div class="helpdesk-assignee">
                                <?php if ($tiene_tecnico): ?>
                                    <div class="helpdesk-assignee-info">
                                        <p class="helpdesk-assignee-label">Técnico</p>
                                        <p class="helpdesk-assignee-name"><?= htmlspecialchars($s->tecnico_asignado) ?></p>
                                    </div>
                                    <div class="helpdesk-assignee-avatar" title="<?= htmlspecialchars($s->tecnico_asignado) ?>">
                                        <?= strtoupper(substr($s->tecnico_asignado, 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <a href="#" 
                                       class="helpdesk-assign-btn"
                                       onclick="event.preventDefault(); AssignTechModal.open({
                                           id: '<?= $s->id ?>',
                                           title: '<?= addslashes(htmlspecialchars(substr($s->descripcion ?? '', 0, 50))) ?>...',
                                           device: '<?= addslashes(htmlspecialchars(($s->equipo_tipo ?? 'Equipo') . ' ' . ($s->equipo_serial ?? ''))) ?>',
                                           location: '<?= addslashes(htmlspecialchars($s->departamento_nombre ?? 'General')) ?>',
                                           category: '<?= addslashes(htmlspecialchars($s->categoria_nombre ?? 'General')) ?>',
                                           priority: '<?= strtolower($s->prioridad ?? 'media') ?>',
                                           timeElapsed: '<?= tiempoRelativo($s->fecha) ?>'
                                       })">
                                        <i class="bi bi-person-plus"></i>
                                        <span>Asignar</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Time & Quick Links -->
                            <div class="helpdesk-ticket-time">
                                <p class="helpdesk-ticket-time-value"><?= $tiempo ?></p>
                                <div class="helpdesk-quick-links">
                                    <a href="<?= BASE_URL ?>soportes/ver/<?= $s->id ?>#comments" 
                                       class="helpdesk-quick-link" title="Ver comentarios">
                                        <i class="bi bi-chat-dots"></i>
                                        <span><?= $s->comentarios_count ?? 0 ?></span>
                                    </a>
                                    <a href="<?= BASE_URL ?>soportes/ver/<?= $s->id ?>#notes" 
                                       class="helpdesk-quick-link" title="Ver observaciones">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="helpdesk-action-buttons">
                                <a href="<?= BASE_URL ?>soportes/ver/<?= $s->id ?>" class="helpdesk-action-btn" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                                    <a href="<?= BASE_URL ?>soportes/editar/<?= $s->id ?>" class="helpdesk-action-btn edit" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>soportes/eliminar/<?= $s->id ?>" 
                                       class="helpdesk-action-btn delete" 
                                       title="Eliminar"
                                       data-no-global-delete="true"
                                       onclick="event.preventDefault(); DeleteModal.open(this.href, {
                                           id: '#T-<?= $s->id ?>',
                                           title: '<?= addslashes(htmlspecialchars(str_replace(["\r", "\n"], ' ', substr($s->descripcion ?? '', 0, 60)))) ?>...',
                                           author: '<?= addslashes(htmlspecialchars($s->solicitante_nombre ?? 'Sin solicitante')) ?>'
                                       })">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($soportes)): ?>
                <div class="helpdesk-empty">
                    <i class="bi bi-inbox"></i>
                    <h3 class="mt-3">No hay tickets</h3>
                    <p class="text-muted">¡Excelente! La bandeja está vacía.</p>
                </div>
            <?php endif; ?>

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
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.helpdesk-tab');
    const searchInput = document.getElementById('searchTickets');
    const tickets = document.querySelectorAll('.helpdesk-ticket');
    
    let currentFilter = 'todos';
    let currentSearch = '';

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

    // Filter tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            currentPage = 1; 
            applyFilters();
        });
    });

    // Search
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.toLowerCase();
        currentPage = 1;
        applyFilters();
    });

    function applyFilters() {
        filteredIndices = [];

        // 1. Filter
        tickets.forEach((ticket, index) => {
            const estado = ticket.getAttribute('data-estado');
            const tecnico = ticket.getAttribute('data-tecnico');
            const searchText = ticket.getAttribute('data-search');

            let matchesFilter = false;
            
            if (currentFilter === 'todos') {
                matchesFilter = true;
            } else if (currentFilter === 'mis_tickets') {
                matchesFilter = tecnico === 'mio';
            } else {
                matchesFilter = estado === currentFilter;
            }

            const matchesSearch = searchText.includes(currentSearch);
            
            // Hide initially
            ticket.style.display = 'none';

            if (matchesFilter && matchesSearch) {
                 filteredIndices.push(index);
            }
        });

        const totalVisible = filteredIndices.length;
        const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;
        
        if (currentPage > totalPages) currentPage = 1;

        // 2. Paginate
        const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
        const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;
        const visibleIndices = filteredIndices.slice(start, end);
        
        visibleIndices.forEach(idx => {
            tickets[idx].style.display = 'flex';
        });

        // 3. UI
        document.getElementById('visibleCountDisplay').textContent = visibleIndices.length;
        document.getElementById('totalCountDisplay').textContent = tickets.length;

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

        // Hide pagination if no results?
        const footer = document.getElementById('paginationFooter');
        if (visibleIndices.length === 0 && totalVisible === 0) {
             // If completely empty, maybe show empty state message handled by PHP or static HTML?
             // Original PHP has a check for empty($soportes). 
             // But if filtering returns 0, we should show empty state?
             // There is no JS empty state element in the original code. 
             // Logic: If visibleIndices.length == 0, footer will show "0 de 5".
             // We can keep it or hide it. Keeping it is fine.
             footer.style.display = 'flex'; 
        } else {
            footer.style.display = 'flex';
        }
    }
    
    // Init
    applyFilters();
});
</script>
