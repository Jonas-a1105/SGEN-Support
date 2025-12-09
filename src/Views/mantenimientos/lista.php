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

    <div class="mh-max-w-6xl">
        
        <!-- HEADER & KPIs -->
        <div class="mh-header-row">
            <div class="mh-title-group">
                <div class="mh-title-icon">
                    <i class="bi bi-tools fs-4"></i>
                </div>
                <div class="mh-title">
                    <h1>Gestión de Mantenimientos</h1>
                    <p>Planificación y seguimiento de la salud de los activos.</p>
                </div>
            </div>
            
            <a href="<?= BASE_URL ?>mantenimientos/crear" class="mh-btn-create">
                <i class="bi bi-plus-lg"></i> Programar Nuevo
            </a>
        </div>

        <!-- Main Content Container -->
        <div class="mh-main-content-card">

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

            <!-- Overdue -->
            <div class="mh-stat-card <?= $stats['overdue'] > 0 ? 'alert' : '' ?>">
                <div class="mh-stat-header">
                    <span class="mh-stat-label">Vencidos</span>
                    <div class="mh-stat-icon rose"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
                <div>
                    <span class="mh-stat-value <?= $stats['overdue'] > 0 ? 'alert' : '' ?>"><?= $stats['overdue'] ?></span>
                    <?php if($stats['overdue'] > 0): ?>
                    <div class="mh-stat-subtext text-rose-600">
                        Atención Requerida
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming -->
            <div class="mh-stat-card">
                <div class="mh-stat-header">
                    <span class="mh-stat-label">Próximos 7 días</span>
                    <div class="mh-stat-icon blue"><i class="bi bi-clock"></i></div>
                </div>
                <div>
                    <span class="mh-stat-value"><?= $stats['upcoming'] ?></span>
                </div>
            </div>

            <!-- Completed -->
            <div class="mh-stat-card">
                <div class="mh-stat-header">
                    <span class="mh-stat-label">Completados (Total)</span>
                    <div class="mh-stat-icon slate"><i class="bi bi-calendar-check"></i></div>
                </div>
                <div>
                    <span class="mh-stat-value"><?= $stats['completed'] ?></span>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="mh-content-card">
            
            <!-- Toolbar -->
            <div class="mh-toolbar">
                <div class="mh-tabs">
                    <button class="mh-tab-btn active" data-filter="all">Todos</button>
                    <button class="mh-tab-btn" data-filter="pending">Pendientes</button>
                    <button class="mh-tab-btn" data-filter="scheduled">Programados</button>
                    <button class="mh-tab-btn" data-filter="completed">Historial</button>
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
            <div class="mh-list" id="mhListContainer">
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
                        } elseif ($isOverdue) {
                            $displayStatus = 'overdue';
                            $statusLabel = 'Vencido';
                        } elseif ($m->estado === 'pendiente') {
                            $displayStatus = 'pending'; // or scheduled technically
                            $statusLabel = 'Pendiente';
                        } else {
                            $displayStatus = 'scheduled';
                            $statusLabel = ucfirst($m->estado);
                        }

                        // Icon based on type (simple heuristic)
                        $iconClass = 'bi-pc-display';
                        if (stripos($m->tipo_mantenimiento, 'impresora') !== false) $iconClass = 'bi-printer';
                        if (stripos($m->tipo_mantenimiento, 'servidor') !== false) $iconClass = 'bi-hdd-rack';
                    ?>
                        <div class="mh-list-item cursor-pointer" 
                             data-status="<?= $displayStatus ?>"
                             data-type="<?= strtolower($m->tipo_mantenimiento) ?>"
                             data-search="<?= strtolower($m->equipo_nombre . ' ' . $m->equipo_codigo . ' ' . $m->tecnico_nombre ?? '') ?>"
                             onclick="window.location.href='<?= BASE_URL ?>mantenimientos/ver/<?= $m->id ?>'">
                            
                            <div class="mh-item-main">
                                <div class="mh-item-icon">
                                    <i class="bi <?= $iconClass ?>"></i>
                                </div>
                                <div class="mh-item-info">
                                    <h4><?= htmlspecialchars($m->equipo_nombre ?? 'Equipo Desconocido') ?> (<?= htmlspecialchars($m->equipo_codigo ?? 'S/N') ?>)</h4>
                                    <p>
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
                                
                                <span class="mh-status-badge <?= $displayStatus ?>">
                                    <?= $statusLabel ?>
                                </span>

                                <button class="mh-btn-more">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
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

        </div><!-- End mh-content-card -->
        
        <!-- Footer Pagination -->
        <div class="mh-footer transparent">
            
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
        
        </div><!-- End mh-main-content-card -->
    </div>


    <!-- Scripts -->
    <script src="<?= BASE_URL ?>js/maintenance-hub.js?v=<?= time() ?>"></script>
</div>
