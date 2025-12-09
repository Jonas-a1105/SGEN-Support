<?php
$paginationPerPage = $perPage ?? 10;

// Calcular estadísticas
$sesionesActivas = 0;
$duracionTotal = 0;
$conteoSesiones = 0;

foreach ($logs as $log) {
    if (!$log->fecha_fin) {
        $sesionesActivas++;
    } else {
        try {
            $inicio = new \DateTime($log->fecha_inicio);
            $fin = new \DateTime($log->fecha_fin);
            $intervalo = $inicio->diff($fin);
            $duracionTotal += ($intervalo->h * 60) + $intervalo->i;
            $conteoSesiones++;
        } catch (Exception $e) {}
    }
}
$promedioDuracion = $conteoSesiones > 0 ? round($duracionTotal / $conteoSesiones) : 0;
$totalLogs = count($logs);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/logs-moderno.css?v=<?= time() ?>">

</div>
<div class="logs-layout-container">

    <!-- HEADER -->
    <div class="logs-header">
        <div>
            <h1 class="logs-title">
                <i class="bi bi-shield-check" style="color: #6366f1;"></i>
                Auditoría del Sistema
            </h1>
            <p class="logs-subtitle">Monitoreo de seguridad y registro de sesiones.</p>
        </div>
            <div class="logs-header-actions">
                <div style="position: relative;">
                    <button onclick="toggleDateDropdown()" id="dateFilterBtn" class="logs-btn-filter">
                        <i class="bi bi-calendar3"></i>
                        <span id="dateFilterLabel">Últimos 30 días</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>
                    <div id="dateDropdown" class="logs-dropdown">
                        <button onclick="setDateFilter('7', 'Últimos 7 días')" class="logs-dropdown-item">Últimos 7 días</button>
                        <button onclick="setDateFilter('30', 'Últimos 30 días')" class="logs-dropdown-item">Últimos 30 días</button>
                        <button onclick="setDateFilter('90', 'Últimos 90 días')" class="logs-dropdown-item">Últimos 90 días</button>
                        <button onclick="setDateFilter('all', 'Todo el historial')" class="logs-dropdown-item">Todo el historial</button>
                    </div>
                </div>
                <button onclick="exportLogs()" class="logs-btn-export">
                    <i class="bi bi-download"></i>
                    Exportar Log
                </button>
            </div>
        </div>

        <!-- Main Content Card Container -->
        <div class="logs-content-card">

        <!-- STATS SUMMARY -->
        <div class="logs-stats-grid">
            <div class="logs-stat-card">
                <div class="logs-stat-icon green">
                    <i class="bi bi-pc-display"></i>
                </div>
                <div class="logs-stat-info">
                    <p>Sesiones Activas</p>
                    <h3><?= $sesionesActivas ?> Usuario<?= $sesionesActivas != 1 ? 's' : '' ?></h3>
                </div>
            </div>
            <div class="logs-stat-card">
                <div class="logs-stat-icon blue">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="logs-stat-info">
                    <p>Promedio Duración</p>
                    <h3><?= $promedioDuracion ?> min</h3>
                </div>
            </div>
            <div class="logs-stat-card">
                <div class="logs-stat-icon amber">
                    <i class="bi bi-list-ul"></i>
                </div>
                <div class="logs-stat-info">
                    <p>Total Registros</p>
                    <h3><?= $totalLogs ?> Sesiones</h3>
                </div>
            </div>
        </div>

        <!-- MAIN CARD -->
        <div class="logs-main-card">
            
            <!-- Toolbar -->
            <div class="logs-list-toolbar">
                <div class="logs-search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" class="logs-search-input" placeholder="Buscar por usuario..." oninput="filterLogs()">
                </div>
                <div class="logs-filter-group">
                    <span class="logs-filter-label">Filtrar:</span>
                    <div class="logs-filter-pills">
                        <button onclick="setFilter('all')" id="btn-all" class="logs-pill" style="background: #f1f5f9; color: #0f172a;">
                            Todos
                        </button>
                        <button onclick="setFilter('active')" id="btn-active" class="logs-pill">
                            Activos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Listado -->
            <div id="logsList">
                <?php
                $renderIndex = 0;
                foreach ($logs as $log): 
                    $shouldHide = $renderIndex >= $paginationPerPage;
                    $renderIndex++;
                ?>
                <?php
                // Calcular duración
                $duracion = 'N/A';
                $esActiva = !$log->fecha_fin;
                if ($log->fecha_fin) {
                    try {
                        $inicio = new \DateTime($log->fecha_inicio);
                        $fin = new \DateTime($log->fecha_fin);
                        $intervalo = $inicio->diff($fin);
                        $horas = $intervalo->h;
                        $minutos = $intervalo->i;
                        if ($horas > 0) {
                            $duracion = $horas . 'h ' . $minutos . 'm';
                        } else {
                            $duracion = $minutos . ' min';
                        }
                    } catch (Exception $e) {
                        $duracion = 'Error';
                    }
                }
                
                // Formatear fecha
                $fechaInicio = date('d/m/Y H:i', strtotime($log->fecha_inicio));
                
                // Iniciales del avatar
                $iniciales = strtoupper(substr($log->username, 0, 2));
                ?>
                <div class="log-row <?= $esActiva ? 'active-session' : 'closed-session' ?>" data-fecha="<?= $log->fecha_inicio ?>" style="<?= $shouldHide ? 'display: none;' : '' ?>">
                    
                    <!-- User Info -->
                    <div class="log-user-col">
                        <div class="log-avatar <?= $esActiva ? 'active' : 'inactive' ?>">
                            <?= $iniciales ?>
                        </div>
                        <div>
                            <h4 class="log-username"><?= htmlspecialchars($log->username) ?></h4>
                            <p class="log-userid">ID: <?= $log->usuario_id ?></p>
                        </div>
                    </div>

                    <!-- Session Info -->
                    <div class="log-details-grid">
                        <div class="log-detail-item">
                            <i class="bi bi-calendar3" style="color: #94a3b8;"></i>
                            <span><?= $fechaInicio ?></span>
                        </div>
                        <div class="log-detail-item">
                            <i class="bi bi-clock" style="color: #94a3b8;"></i>
                            <?php if ($esActiva): ?>
                                <span style="color: #059669; font-weight: 600;">● En curso</span>
                            <?php else: ?>
                                <span>Duración: <?= $duracion ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="log-actions">
                        <?php if ($esActiva): ?>
                        <div class="log-status-icon active" title="Sesión Activa">
                            <i class="bi bi-check-circle-fill" style="font-size: 1rem;"></i>
                        </div>
                        <?php else: ?>
                        <div class="log-status-icon closed" title="Sesión Finalizada">
                            <i class="bi bi-box-arrow-right" style="font-size: 1rem;"></i>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Botón Ver -->
                        <button onclick="verDetalle('<?= htmlspecialchars($log->username) ?>', '<?= $log->usuario_id ?>', '<?= $log->fecha_inicio ?>', '<?= $log->fecha_fin ?? '' ?>', '<?= $duracion ?>', '<?= $esActiva ? 'Activa' : 'Cerrada' ?>')" 
                                class="btn-view-detail">
                            <i class="bi bi-eye"></i>
                            Ver
                        </button>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Footer con Paginación -->
            <div class="logs-footer">
                
                <!-- Izquierda: Info y selector de items por página -->
                <div class="logs-footer-info">
                    <span class="logs-footer-text">
                        Mostrando <strong style="color: #0f172a;" id="visibleCount"><?= min($paginationPerPage, $totalLogs) ?></strong> de <strong style="color: #0f172a;"><?= $totalLogs ?></strong> registros
                    </span>
                    <div class="logs-per-page">
                        <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                        <select id="itemsPerPage" onchange="changeItemsPerPage()" class="logs-per-page-select">
                            <option value="5" <?= $paginationPerPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $paginationPerPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $paginationPerPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $paginationPerPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                </div>
                
                <!-- Derecha: Paginación Minimal Ghost -->
                <div class="logs-pagination">
                    <button onclick="previousPage()" id="btnPrev" class="logs-page-btn" disabled>
                        <i class="bi bi-arrow-left"></i>
                        Anterior
                    </button>

                    <div style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #475569;">
                        <span style="color: #0f172a;">Página <span id="currentPage">1</span></span>
                        <span style="color: #cbd5e1;">/</span>
                        <span id="totalPages">1</span>
                    </div>

                    <button onclick="nextPage()" id="btnNext" class="logs-page-btn">
                        Siguiente
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

</div>

</div><!-- End logs-content-card -->

<div class="container-fluid">

<script src="<?= BASE_URL ?>js/logs.js?v=<?= time() ?>"></script>

<!-- Modal de Detalle -->
<div id="detalleModal" class="logs-modal-overlay">
    <div class="logs-modal-card">
        
        <!-- Header -->
        <div class="logs-modal-header">
            <h3 class="logs-modal-title">
                <i class="bi bi-info-circle" style="color: #6366f1;"></i>
                Detalle de Sesión
            </h3>
            <button onclick="cerrarModal()" class="logs-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="logs-modal-body">
            
            <!-- Usuario -->
            <div class="logs-modal-user">
                <div id="modal-avatar" class="logs-modal-avatar">
                    AD
                </div>
                <div>
                    <h4 id="modal-username" class="log-username" style="font-size: 1.125rem;">Username</h4>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">ID de Usuario: <span id="modal-userId" style="font-weight: 600; color: #0f172a;">1</span></p>
                </div>
            </div>
            
            <!-- Detalles -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="logs-modal-detail-row">
                    <span class="logs-modal-label">
                        <i class="bi bi-calendar3"></i> Inicio de Sesión
                    </span>
                    <span id="modal-fechaInicio" class="logs-modal-value">-</span>
                </div>
                <div class="logs-modal-detail-row">
                    <span class="logs-modal-label">
                        <i class="bi bi-calendar3"></i> Fin de Sesión
                    </span>
                    <span id="modal-fechaFin" class="logs-modal-value">-</span>
                </div>
                <div class="logs-modal-detail-row">
                    <span class="logs-modal-label">
                        <i class="bi bi-clock"></i> Duración
                    </span>
                    <span id="modal-duracion" class="logs-modal-value">-</span>
                </div>
                <div class="logs-modal-detail-row">
                    <span class="logs-modal-label">
                        <i class="bi bi-shield-check"></i> Estado
                    </span>
                    <span id="modal-estado" style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 9999px;">-</span>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="logs-modal-footer">
            <button onclick="cerrarModal()" class="logs-modal-btn-close">
                Cerrar
            </button>
        </div>
    </div>
</div>