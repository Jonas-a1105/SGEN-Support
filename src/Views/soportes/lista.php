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

<link rel="stylesheet" href="<?= BASE_URL ?>css/bulk-delete.css">

<style>
/* New styles for left-aligned checkboxes */
.ticket-checkbox {
    display: none;
    position: absolute;
    left: 15px; /* Position at left */
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.5rem;
    z-index: 20;
    background: white;
    border-radius: 50%;
    line-height: 1;
    /* Box shadow to make it pop over content if needed, though padding handles space */
    box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
}

/* Icon Toggle Logic */
.ticket-checkbox .icon-checked { display: none; color: #ef4444; }
.ticket-checkbox .icon-unchecked { display: block; color: #cbd5e1; transition: color 0.2s; }
.helpdesk-ticket:hover .ticket-checkbox .icon-unchecked { color: #94a3b8; }

/* Show Checked icon when parent is selected */
#ticketFeed.selection-active .helpdesk-ticket.selected .ticket-checkbox .icon-checked { display: block; }
#ticketFeed.selection-active .helpdesk-ticket.selected .ticket-checkbox .icon-unchecked { display: none; }


/* Fix: Target #ticketFeed explicitly since JS adds class there */
#ticketFeed.selection-active .helpdesk-ticket { 
    cursor: pointer; 
    padding-left: 60px !important; /* Shift content right */
    transition: all 0.2s ease;
}

#ticketFeed.selection-active .helpdesk-ticket .ticket-checkbox {
    display: block;
    animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes popIn {
    0% { opacity: 0; transform: translateY(-50%) scale(0.5); }
    100% { opacity: 1; transform: translateY(-50%) scale(1); }
}

#ticketFeed.selection-active .helpdesk-ticket.selected {
    /* Border removed as requested */
    background-color: #fef2f2 !important;
}

/* --- Custom Delete Modal Styles --- */
.custom-modal-backdrop {
    position: fixed;
    inset: 0;
    width: 100%; 
    height: 100%;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(0.75px);
    z-index: 9999; /* Highest priority */
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}
.custom-modal-backdrop.active {
    opacity: 1;
    pointer-events: auto;
}
.custom-modal-content {
    background: white;
    width: 90%;
    max-width: 450px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95);
    transition: transform 0.2s ease;
}
.custom-modal-backdrop.active .custom-modal-content {
    transform: scale(1);
}
.modal-header-danger {
    background-color: #fef2f2;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #fee2e2;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.icon-danger-box {
    background-color: #fee2e2;
    color: #dc2626;
    padding: 0.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-warning-box {
    background-color: #fffbeb;
    border: 1px solid #fef3c7;
    color: #b45309;
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.85rem;
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.security-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    font-weight: 700;
    letter-spacing: 2px;
    outline: none;
    transition: all 0.2s;
    text-transform: uppercase;
}
.security-input.match {
    border-color: #ef4444;
    color: #b91c1c;
    background-color: #fef2f2;
}
.security-input:focus {
    border-color: #94a3b8;
}
.btn-cancel-custom {
    background: white;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 600;
    padding: 0.6rem 1rem;
    border-radius: 0.75rem;
    width: 100%;
    transition: all 0.2s;
}
.btn-cancel-custom:hover { background: #f8fafc; color: #1e293b; }
.btn-delete-custom {
    background: #f1f5f9;
    color: #94a3b8;
    border: none;
    font-weight: 700;
    padding: 0.6rem 1rem;
    border-radius: 0.75rem;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: not-allowed;
    transition: all 0.2s;
    box-shadow: none;
}
.btn-delete-custom.active {
    background: #dc2626;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);
}
.btn-delete-custom.active:hover { background: #b91c1c; }
.btn-delete-custom.active:active { transform: scale(0.98); }
</style>

<div class="helpdesk-container">
    
    <!-- Header Blanco -->
    <nav class="helpdesk-nav">
        <div class="helpdesk-header-container">
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
                    <a href="<?= BASE_URL ?>reportes/soportes" target="_blank" class="btn btn-outline-secondary d-none d-md-inline-flex">
                        <i class="bi bi-download me-1"></i>Exportar
                    </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>soportes/crear" class="btn-nuevo-ticket">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nuevo Ticket</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-4 py-4 helpdesk-main-container">
        
        <!-- Main Content Card Container -->
        <div class="helpdesk-content-card">
        
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
        <div class="helpdesk-filters d-flex align-items-center gap-2 flex-wrap">
            
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <div class="d-flex align-items-center gap-2" style="transition: all 0.3s ease;">
                <!-- Toggle Switch (Standalone) -->
                <div class="form-check form-switch mb-0" title="Activar selección múltiple">
                    <input class="form-check-input" type="checkbox" id="checkBulkMode" style="cursor: pointer; width: 3em; height: 1.5em;">
                </div>
                <!-- Select All Checkbox (Hidden by default) -->
                <div id="containerSelectAll" class="animate-fadeIn d-none" style="transition: all 0.3s ease;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkSelectAll" style="cursor: pointer; border-color: #cbd5e1;">
                        <label class="form-check-label text-muted fs-sm user-select-none" for="checkSelectAll" style="cursor: pointer;">Todo</label>
                    </div>
                </div>
                <!-- Modern Delete Button -->
                <button id="btnBulkTicketDelete" class="bulk-delete-btn">
                    <i class="bi bi-trash"></i>
                    <span id="ticketSelectedCount">0</span> seleccionados
                </button>
            </div>
            <?php endif; ?>

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
            <div id="ticketFeed" data-per-page="<?= $paginationPerPage ?>">
                <?php foreach ($soportes as $s): 
                    $prioridad_class = strtolower($s->prioridad ?? 'media');
                    $estado_class = strtolower(str_replace(' ', '_', $s->estado ?? 'pendiente'));
                    $categoria_class = getCategoriaClass($s->categoria_nombre ?? '');
                    $tiene_tecnico = !empty($s->tecnico_asignado);
                    $es_mi_ticket = strtolower($s->tecnico_asignado ?? '') === strtolower($usuario_actual);
                    $tiempo = tiempoRelativo($s->fecha);
                ?>
                    <div class="helpdesk-ticket priority-<?= $prioridad_class ?>" 
                         data-ticket-id="<?= $s->id ?>"
                         data-estado="<?= $estado_class ?>"
                         data-tecnico="<?= $es_mi_ticket ? 'mio' : 'otro' ?>"
                         data-search="<?= htmlspecialchars(strtolower(($s->descripcion ?? '') . ' ' . ($s->equipo_serial ?? '') . ' ' . ($s->solicitante_nombre ?? ''))) ?>">
                        
                        <div class="ticket-checkbox">
                            <i class="bi bi-check-circle-fill icon-checked"></i>
                            <i class="bi bi-circle icon-unchecked"></i>
                        </div>
                        
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
                                    <div class="helpdesk-assignee-avatar" title="<?= htmlspecialchars($s->tecnico_asignado) ?>">
                                        <?= strtoupper(substr($s->tecnico_asignado, 0, 1)) ?>
                                    </div>
                                    <div class="helpdesk-assignee-info">
                                        <p class="helpdesk-assignee-label">Técnico</p>
                                        <p class="helpdesk-assignee-name"><?= htmlspecialchars($s->tecnico_asignado) ?></p>
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

        </div><!-- End helpdesk-feed -->

        <!-- Modern Pagination Footer (outside feed, inside content-card) -->
        <div id="paginationFooter" class="helpdesk-pagination-footer">
            <div class="helpdesk-pagination-info">
                <span class="text-slate-500 fs-sm">
                    Mostrando <strong class="text-slate-900" id="visibleCountDisplay">0</strong> de <strong class="text-slate-900" id="totalCountDisplay">0</strong>
                </span>
                <div class="helpdesk-pagination-controls">
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
            <div class="helpdesk-pagination-controls">
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
        
        </div><!-- End helpdesk-content-card -->

    </main>
</div>

<!-- Custom Delete Modal Markup -->
<div id="customDeleteModal" class="custom-modal-backdrop">
    <div class="custom-modal-content">
        <!-- Header -->
        <div class="modal-header-danger">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-danger-box">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h3 class="m-0 fs-6 fw-bold text-danger-900" style="color: #7f1d1d;">Eliminar Selección</h3>
                </div>
            </div>
            <button class="btn btn-link text-decoration-none p-0" id="closeModalBtn" style="color: #fca5a5;">
                <i class="bi bi-x-lg" style="font-size: 1.2rem;"></i>
            </button>
        </div>

        <div class="p-4">
            <!-- Context -->
            <p class="text-secondary fs-sm mb-0">
                Estás a punto de eliminar permanentemente <strong id="deleteCountDisplay" class="text-dark bg-light px-1 rounded border">0 tickets</strong>.
            </p>
            
            <div class="modal-warning-box">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <div>
                    <strong>Advertencia:</strong> Se perderán comentarios, archivos y el historial completo. Esta acción no se puede deshacer.
                </div>
            </div>

            <!-- Input -->
            <div class="mt-4 mb-4 position-relative">
                <label class="d-block text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    PARA CONFIRMAR, ESCRIBE "ELIMINAR" ABAJO
                </label>
                <div class="position-relative">
                    <input type="text" id="deleteConfirmationInput" class="security-input" placeholder="ELIMINAR" autocomplete="off">
                    <div id="unlockIcon" class="position-absolute d-none text-danger animate-pulse" style="right: 15px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-unlock-fill fs-5"></i>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="d-flex gap-3">
                <button id="cancelDeleteBtn" class="btn-cancel-custom">Cancelar</button>
                <button id="confirmDeleteBtn" class="btn-delete-custom" disabled>
                    <span class="normal-state"><i class="bi bi-trash"></i> Eliminar</span>
                    <span class="loading-state d-none"><span class="spinner-border spinner-border-sm me-2"></span> Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkBulkMode = document.getElementById('checkBulkMode');
    const btnBulkDelete = document.getElementById('btnBulkTicketDelete');
    const selectedCountSpan = document.getElementById('ticketSelectedCount');
    const feed = document.getElementById('ticketFeed');
    let isSelectionMode = false;
    let selectedIds = new Set();
    
    // Fix: Move Modal to Body to escape stacking context (sidebar/header blur)
    const modalEl = document.getElementById('customDeleteModal');
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }
    
    if (checkBulkMode && feed) {
        // Toggle Selection Mode via Checkbox
        checkBulkMode.addEventListener('change', function() {
            isSelectionMode = this.checked;
            const containerSelectAll = document.getElementById('containerSelectAll');
            
            if (isSelectionMode) {
                feed.classList.add('selection-active');
                if (containerSelectAll) containerSelectAll.classList.remove('d-none');
            } else {
                feed.classList.remove('selection-active');
                if (containerSelectAll) containerSelectAll.classList.add('d-none');
                selectedIds.clear(); // Clear local set
                
                // Uncheck select all if active
                const checkSelectAll = document.getElementById('checkSelectAll');
                if(checkSelectAll) checkSelectAll.checked = false;

                // Visual clear handled by class removal, but ensure checkboxes reset via UI update if needed
                // Actually usually we just remove class 'selected' from tickets
                const selectedTickets = feed.querySelectorAll('.helpdesk-ticket.selected');
                selectedTickets.forEach(t => t.classList.remove('selected'));
                
                updateUI();
            }
        });

        // Select All Logic
        const checkSelectAll = document.getElementById('checkSelectAll');
        if (checkSelectAll) {
            checkSelectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                // Only select visible tickets in current page
                const visibleTickets = feed.querySelectorAll('.helpdesk-ticket');
                visibleTickets.forEach(ticket => {
                    const id = ticket.dataset.ticketId;
                    if (isChecked) {
                        selectedIds.add(id);
                        ticket.classList.add('selected');
                    } else {
                        selectedIds.delete(id);
                        ticket.classList.remove('selected');
                    }
                });
                updateUI();
            });
        }

        // Event Delegation for Clicks (Selection)
        feed.addEventListener('click', function(e) {
            if (!isSelectionMode) return;
            
            const ticket = e.target.closest('.helpdesk-ticket');
            if (ticket) {
                e.preventDefault();
                e.stopPropagation();
                
                const id = ticket.dataset.ticketId;
                if (selectedIds.has(id)) {
                    selectedIds.delete(id);
                    ticket.classList.remove('selected');
                } else {
                    selectedIds.add(id);
                    ticket.classList.add('selected');
                }
                updateUI();
            }
        });
        
        // Helper to Close Modal
        function closeDeleteModal() {
            document.getElementById('customDeleteModal').classList.remove('active');
            document.getElementById('deleteConfirmationInput').value = '';
            document.getElementById('confirmDeleteBtn').disabled = true;
            document.getElementById('confirmDeleteBtn').classList.remove('active');
            document.getElementById('unlockIcon').classList.add('d-none');
            document.getElementById('deleteConfirmationInput').classList.remove('match');
        }

        // Close Modal Events
        document.getElementById('closeModalBtn').addEventListener('click', closeDeleteModal);
        document.getElementById('cancelDeleteBtn').addEventListener('click', closeDeleteModal);
        document.querySelector('.custom-modal-backdrop').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('customDeleteModal').classList.contains('active')) {
                closeDeleteModal();
            }
        });

        // Input Validation
        const deleteInput = document.getElementById('deleteConfirmationInput');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const unlockIcon = document.getElementById('unlockIcon');

        deleteInput.addEventListener('input', function() {
            const val = this.value.toUpperCase();
            // Force Update Value to Upper
            // this.value = val; // Optional: Force upper visual? React does it via state. simpler to just check
            
            if (val === 'ELIMINAR') {
                this.classList.add('match');
                confirmBtn.classList.add('active');
                confirmBtn.disabled = false;
                unlockIcon.classList.remove('d-none');
            } else {
                this.classList.remove('match');
                confirmBtn.classList.remove('active');
                confirmBtn.disabled = true;
                unlockIcon.classList.add('d-none');
            }
        });

        // Bulk Delete Action (Open Modal)
        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', function() {
                if (selectedIds.size === 0) return;
                
                // Update Count
                document.getElementById('deleteCountDisplay').textContent = `${selectedIds.size} tickets`;
                
                // Open Modal
                document.getElementById('customDeleteModal').classList.add('active');
                
                // Focus Input
                setTimeout(() => document.getElementById('deleteConfirmationInput').focus(), 100);
            });
        }

        // Confirm Delete Action
        confirmBtn.addEventListener('click', function() {
            if (deleteInput.value.toUpperCase() !== 'ELIMINAR') return;

            // UI Loading State
            confirmBtn.disabled = true; // Prevent double click
            confirmBtn.querySelector('.normal-state').classList.add('d-none');
            confirmBtn.querySelector('.loading-state').classList.remove('d-none');
            deleteInput.disabled = true;

            // Execute Delete
            fetch('<?= BASE_URL ?>soportes/eliminar_tickets_masivos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: Array.from(selectedIds) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate Removal
                    selectedIds.forEach(id => {
                        const el = feed.querySelector(`.helpdesk-ticket[data-ticket-id="${id}"]`);
                        if (el) {
                            el.style.opacity = '0';
                            el.style.transform = 'scale(0.9)';
                            setTimeout(() => el.remove(), 300);
                        }
                    });
                    
                    // Reset Mode
                    setTimeout(() => {
                        closeDeleteModal();
                        checkBulkMode.checked = false;
                        document.getElementById('checkSelectAll').checked = false;
                        document.getElementById('containerSelectAll').classList.add('d-none');
                        feed.classList.remove('selection-active');
                        selectedIds.clear();
                        updateUI();
                        
                        // Reset Button State
                        confirmBtn.querySelector('.normal-state').classList.remove('d-none');
                        confirmBtn.querySelector('.loading-state').classList.add('d-none');
                        deleteInput.disabled = false;

                        if (window.Toast) Toast.show('success', data.message);
                        else Swal.fire('Eliminados', data.message, 'success');
                    }, 500); // Wait for API + Animation

                } else {
                    closeDeleteModal();
                    confirmBtn.querySelector('.normal-state').classList.remove('d-none');
                    confirmBtn.querySelector('.loading-state').classList.add('d-none');
                    deleteInput.disabled = false;
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                closeDeleteModal();
                confirmBtn.querySelector('.normal-state').classList.remove('d-none');
                confirmBtn.querySelector('.loading-state').classList.add('d-none');
                deleteInput.disabled = false;
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        });
    }

    function updateUI() {
        if (selectedCountSpan) selectedCountSpan.textContent = selectedIds.size;
        if (btnBulkDelete) {
            if (selectedIds.size > 0) btnBulkDelete.classList.add('visible');
            else btnBulkDelete.classList.remove('visible');
        }
    }
});
</script>
<script src="<?= BASE_URL ?>js/soportes.js?v=<?= time() ?>"></script>

