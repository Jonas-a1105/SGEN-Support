<?php
/**
 * Vista de Mantenimientos - Modern Hub
 */

// Calcular Estadísticas
$stats = [
    'healthScore' => 95, // Placeholder o calcular real si hay data
    'overdue' => 0,
    'upcoming' => 0,
    'completed' => 0
];

$now = new DateTime();
$upcomingLimit = (new DateTime())->modify('+7 days');

foreach ($mantenimientos as $m) {
    $fecha = new DateTime($m->fecha);
    $estado = strtolower($m->estado);
    
    // Check overdue
    if ($fecha < $now && $estado !== 'realizado' && $estado !== 'completado') {
        $stats['overdue']++;
    }
    // Check upcoming
    elseif ($fecha >= $now && $fecha <= $upcomingLimit && $estado !== 'realizado') {
        $stats['upcoming']++;
    }
    // Check completed
    if ($estado === 'realizado' || $estado === 'completado') {
        $stats['completed']++;
    }
}
?>

<div class="mh-container">
    
    <!-- New Header/Nav Structure matching Tickets -->
    <nav class="mh-nav">
        <div class="mh-header-container">
            <div class="mh-title-group">
                <div class="mh-title-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="mh-title">
                    <h1>Gestión de Mantenimientos</h1>
                    <p>Planificación y seguimiento de la salud de los activos.</p>
                </div>
            </div>
            
            <a href="<?= BASE_URL ?>mantenimientos/crear" class="mh-btn-create">
                <i class="bi bi-plus-lg"></i>
                Programar Nuevo
            </a>
        </div>
    </nav>

    <main class="mh-main-container">

        <!-- Main Content Card Container -->
        <div class="mh-content-card">

        <!-- KPI CARDS -->
        <div class="mh-stats-grid">
            <!-- Health Score -->
            <div class="mh-stat-card">
                <div class="mh-stat-header">
                    <span class="mh-stat-label">Salud de Flota</span>
                    <div class="mh-stat-icon emerald"><i class="bi bi-check-circle"></i></div>
                </div>
                <div>
                    <span class="mh-stat-value"><?= $stats['healthScore'] ?>%</span>
                    <div class="mh-stat-subtext">
                        <i class="bi bi-arrow-up-right"></i> Operatividad Alta
                    </div>
                </div>
            </div>

            <!-- Completed (Now 2nd) -->
            <div class="mh-stat-card">
                <div class="mh-stat-header">
                    <span class="mh-stat-label">Mantenimientos Realizados</span>
                    <div class="mh-stat-icon emerald"><i class="bi bi-check-circle-fill"></i></div>
                </div>
                <div>
                    <span class="mh-stat-value"><?= $stats['completed'] ?></span>
                    <div class="mh-stat-subtext text-emerald-700">
                        Total acumulado
                    </div>
                </div>
            </div>

            <!-- Upcoming (3rd) -->
            <div class="mh-stat-card">
                <div class="mh-stat-header">
                    <span class="mh-stat-label">Próximos 7 días</span>
                    <div class="mh-stat-icon blue"><i class="bi bi-clock"></i></div>
                </div>
                <div>
                    <span class="mh-stat-value"><?= $stats['upcoming'] ?></span>
                </div>
            </div>
        </div>


            
            <!-- Toolbar -->
            <div class="mh-toolbar">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
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
                    <?php endif; ?>
                    
                    <div class="mh-tabs">
                        <button class="mh-tab-btn active" data-filter="all">Todos</button>
                        <button class="mh-tab-btn" data-filter="pending">Pendientes</button>
                        <button class="mh-tab-btn" data-filter="scheduled">Programados</button>
                        <button class="mh-tab-btn" data-filter="completed">Historial</button>
                    </div>
                </div>

                <div class="mh-actions">
                    <div class="mh-search-wrapper">
                        <i class="bi bi-search mh-search-icon"></i>
                        <input type="text" id="mhSearchInput" class="mh-search-input" placeholder="Buscar equipo...">
                    </div>
                    <div class="pos-relative">
                        <button class="mh-btn-filter" id="mhFilterBtn">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <div class="mh-filter-dropdown" id="mhFilterDropdown">
                            <div class="mh-filter-option">
                                <input type="checkbox" id="filterPreventivo" checked>
                                <label for="filterPreventivo">Preventivos</label>
                            </div>
                            <div class="mh-filter-option">
                                <input type="checkbox" id="filterCorrectivo" checked>
                                <label for="filterCorrectivo">Correctivos</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="mh-list" id="mhListContainer" data-module="mantenimientos">
                <?php if (empty($mantenimientos)): ?>
                    <div class="mh-empty-state">
                        <div class="mh-empty-icon"><i class="bi bi-calendar-event"></i></div>
                        <h3 class="mh-empty-title">Sin mantenimientos</h3>
                        <p class="mh-empty-text">No hay registros de mantenimientos en el sistema actualmente.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mantenimientos as $m): 
                        $fechaObj = new DateTime($m->fecha);
                        $isOverdue = $fechaObj < $now && $m->estado !== 'realizado';
                        
                        // Determine status for filtering/styling
                        if ($m->estado === 'realizado' || $m->estado === 'completado') {
                            $displayStatus = 'completed';
                            $statusLabel = 'Realizado';
                            $badgeClass = 'bg-emerald-100 text-emerald-700';
                            $badgeIcon = 'bi-check-circle-fill';
                        } elseif ($m->estado === 'en_proceso') {
                            $displayStatus = 'process';
                            $statusLabel = 'En Proceso';
                            $badgeClass = 'bg-blue-100 text-blue-700';
                            $badgeIcon = 'bi-gear-wide-connected';
                        } elseif ($m->estado === 'pendiente') {
                             $displayStatus = 'pending';
                             $statusLabel = 'Pendiente';
                             $badgeClass = 'bg-amber-100 text-amber-700';
                             $badgeIcon = 'bi-clock';
                        } else {
                            $displayStatus = 'scheduled';
                            $statusLabel = ucfirst($m->estado);
                            $badgeClass = 'bg-slate-100 text-slate-700';
                            $badgeIcon = 'bi-calendar';
                        }

                        // Icon based on type (simple heuristic)

                        // Icon based on type (simple heuristic)
                        $iconClass = 'bi-pc-display';
                        if (stripos($m->tipo_mantenimiento, 'impresora') !== false) $iconClass = 'bi-printer';
                        if (stripos($m->tipo_mantenimiento, 'servidor') !== false) $iconClass = 'bi-hdd-rack';
                    ?>
                        <div class="mh-list-item cursor-pointer" 
                             data-status="<?= $displayStatus ?>"
                             data-type="<?= strtolower($m->tipo_mantenimiento) ?>"
                             data-search="<?= strtolower($m->equipo_nombre . ' ' . $m->equipo_codigo . ' ' . $m->tecnico_nombre ?? '') ?>"
                             data-bulk-item
                             data-mantenimiento-id="<?= $m->id ?>"
                             onclick="if(!document.getElementById('mhListContainer').classList.contains('selection-active')) window.location.href='<?= BASE_URL ?>mantenimientos/ver/<?= $m->id ?>'">                            
                            <!-- Bulk Checkbox -->
                            <div class="bulk-checkbox">
                                <i class="bi bi-check-circle-fill icon-checked"></i>
                                <i class="bi bi-circle icon-unchecked"></i>
                            </div>
                            
                            <div class="mh-item-main">
                                <!-- Status Badge at Start -->
                                <?php 
                                    // Check if timer expired (before rendering badge)
                                    $startTimeCheck = strtotime($m->fecha);
                                    $durationSecondsCheck = ($m->duracion ?? 60) * 60;
                                    $endTimeCheck = $startTimeCheck + $durationSecondsCheck;
                                    $nowCheck = time();
                                    $timerExpiredForBadge = ($nowCheck >= $endTimeCheck && ($m->duracion ?? 0) > 0 && $displayStatus !== 'completed');
                                    
                                    // Override badge if timer expired
                                    if ($timerExpiredForBadge) {
                                        $badgeClass = 'bg-emerald-100 text-emerald-700';
                                        $badgeIcon = 'bi-check-circle-fill';
                                        $statusLabel = 'Realizado';
                                        $displayStatus = 'completed';
                                    }
                                ?>
                                <div class="me-3">
                                     <span class="mh-status-badge rounded-pill <?= $badgeClass ?> border-0 d-inline-flex align-items-center gap-2 px-3 py-2">
                                        <i class="bi <?= $badgeIcon ?>"></i>
                                        <?= $statusLabel ?>
                                    </span>
                                </div>

                                <!-- Timer (If active) -->
                                <?php 
                                    $startTime = strtotime($m->fecha);
                                    $durationSeconds = ($m->duracion ?? 60) * 60;
                                    $endTime = $startTime + $durationSeconds;
                                    $nowTimestamp = time();
                                    $showTimer = false;
                                    
                                    if ($m->estado == 'pendiente' && $startTime > $nowTimestamp) {
                                        $showTimer = true;
                                    } elseif ($m->estado == 'en_proceso' && $endTime > $nowTimestamp) {
                                        $showTimer = true;
                                    }
                                ?>
                                <?php if($showTimer): ?>
                                    <div class="me-3 d-none d-md-block">
                                        <span class="badge rounded-pill bg-white border shadow-sm text-dark px-2 py-1 d-flex align-items-center gap-2" style="font-size: 0.7rem;">
                                             <i class="bi bi-stopwatch text-primary"></i>
                                             <span class="maintenance-timer-list" 
                                                   data-start="<?= date('Y-m-d H:i:s', $startTime) ?>" 
                                                   data-end="<?= date('Y-m-d H:i:s', $endTime) ?>" 
                                                   data-status="<?= $m->estado ?>"
                                                   class="font-monospace fw-bold">...</span>
                                        </span>
                                    </div>
                                <?php endif; ?>


                                <div class="mh-item-icon position-relative">
                                    <i class="bi <?= $iconClass ?>"></i>
                                    <?php 
                                        // Show checkmark if: 1) Already completed, OR 2) Timer has expired
                                        $timerExpired = ($nowTimestamp >= $endTime && ($m->duracion ?? 0) > 0);
                                        $showCheckmark = ($displayStatus === 'completed') || $timerExpired;
                                    ?>
                                    <?php if($showCheckmark): ?>
                                        <span class="position-absolute resolved-check" style="bottom: -2px; right: -2px; background: #10b981; border-radius: 50%; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-check text-white" style="font-size: 10px;"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="mh-item-info">
                                    <div class="d-flex align-items-center gap-2">
                                        <h4 class="mb-0"><?= htmlspecialchars($m->equipo_nombre ?? 'Equipo Desconocido') ?></h4>
                                        <span class="text-muted small">(<?= htmlspecialchars($m->equipo_codigo ?? 'S/N') ?>)</span>
                                    </div>
                                    <p class="mb-0 mt-1">
                                        <span class="mh-item-type"><?= htmlspecialchars($m->tipo_mantenimiento) ?></span>
                                        <span>•</span>
                                        <span>Asignado a: <?= htmlspecialchars($m->tecnico_nombre ?? 'Sin asignar') ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="mh-item-meta">
                                <div class="mh-date">
                                    <i class="bi bi-calendar4"></i>
                                    <?= $fechaObj->format('d M, Y') ?>
                                </div>
                                


                                <div class="mh-actions-group">
                                    <a href="<?= BASE_URL ?>mantenimientos/ver/<?= $m->id ?>" 
                                       onclick="event.stopPropagation();"
                                       class="mh-action-btn view" 
                                       title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($m->estado !== 'completado' && $m->estado !== 'cancelado'): ?>
                                    <a href="<?= BASE_URL ?>mantenimientos/editar/<?= $m->id ?>" 
                                       onclick="event.stopPropagation();"
                                       class="mh-action-btn edit" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <a href="#" 
                                       onclick="event.preventDefault(); event.stopPropagation(); DeleteModal.open('<?= BASE_URL ?>mantenimientos/eliminar/<?= $m->id ?>', { 
                                           id: '#<?= $m->id ?>', 
                                           title: 'Mantenimiento: <?= addslashes($m->equipo_nombre) ?>', 
                                           author: '<?= addslashes($m->tecnico_nombre ?? 'Sin asignar') ?>' 
                                       })" 
                                       class="mh-action-btn delete" 
                                       title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- JS Empty State (Hidden) -->
                <div id="mhEmptyState" class="mh-empty-state d-none">
                    <div class="mh-empty-icon"><i class="bi bi-search"></i></div>
                    <h3 class="mh-empty-title">No se encontraron resultados</h3>
                    <p class="mh-empty-text">Intenta ajustar tus filtros de búsqueda.</p>
                </div>
            </div>

        <!-- KPI CARDS removed end div here to include footer -->
        
        <!-- Footer Pagination -->
        <div class="mh-footer">
            
            <!-- Info y selector de items por página -->
            <div class="mh-footer-row">
                <span class="mh-footer-text">
                    Mostrando <strong class="mh-text-dark" id="mhVisibleCount"><?= min(10, count($mantenimientos)) ?></strong> de <strong class="mh-text-dark"><?= count($mantenimientos) ?></strong> registros
                </span>
                <div class="mh-footer-select-wrapper">
                    <span class="mh-footer-label">Mostrar:</span>
                    <select id="mhItemsPerPage" onchange="mhChangeItemsPerPage()" class="mh-footer-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            
            <!-- Paginación -->
            <div class="mh-pagination">
                <button onclick="mhPreviousPage()" id="mhBtnPrev" class="mh-page-btn" disabled>
                    <i class="bi bi-arrow-left"></i>
                    Anterior
                </button>

                <div class="mh-current-page-info">
                    <span class="mh-text-dark">Página <span id="mhCurrentPage">1</span></span>
                    <span class="mh-text-slate-300">/</span>
                    <span id="mhTotalPages">1</span>
                </div>

                <button onclick="mhNextPage()" id="mhBtnNext" class="mh-page-btn">
                    Siguiente
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
        </div><!-- End mh-content-card -->
        
    </main>
    
    <!-- Scripts -->
    <script src="<?= BASE_URL ?>js/maintenance-hub.js?v=<?= time() ?>"></script>
    
    <!-- Bulk Delete Assets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/bulk-delete.css?v=<?= time() ?>">
    <script src="<?= BASE_URL ?>js/bulk-delete.js?v=<?= time() ?>"></script>
    
    <?php if ($_SESSION['rol'] === 'admin'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BulkDelete !== 'undefined') {
                BulkDelete.init({
                    containerId: 'mhListContainer',
                    itemSelector: '[data-bulk-item]',
                    itemIdAttribute: 'data-mantenimiento-id',
                    deleteUrl: BASE_URL + 'mantenimientos/eliminar_masivo',
                    entityName: 'mantenimientos',
                    entityNameSingular: 'mantenimiento',
                    toggleId: 'bulkModeToggle',
                    selectAllId: 'bulkSelectAll',
                    selectAllContainerId: 'bulkSelectAllContainer',
                    deleteButtonId: 'bulkDeleteBtn',
                    countSpanId: 'bulkSelectedCount'
                });
            }
        });
    </script>
    <?php endif; ?>
</div>
