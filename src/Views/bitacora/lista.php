<?php
$totalAcciones = count($acciones);

// Agrupar acciones por fecha
$accionesPorFecha = [];
foreach ($acciones as $accion) {
    $fecha = date('Y-m-d', strtotime($accion->created_at));
    $hoy = date('Y-m-d');
    $ayer = date('Y-m-d', strtotime('-1 day'));
    
    if ($fecha === $hoy) {
        $grupo = 'Hoy, ' . date('d M');
    } elseif ($fecha === $ayer) {
        $grupo = 'Ayer, ' . date('d M', strtotime('-1 day'));
    } else {
        $grupo = date('d M Y', strtotime($accion->created_at));
    }
    
    if (!isset($accionesPorFecha[$grupo])) {
        $accionesPorFecha[$grupo] = [];
    }
    $accionesPorFecha[$grupo][] = $accion;
}

// Función para obtener configuración de ícono y clase según entidad
function getEntityConfig($tipo) {
    $config = [
        'soporte' => ['icon' => 'bi-ticket-detailed', 'class' => 'entity-soporte'],
        'equipo' => ['icon' => 'bi-pc-display', 'class' => 'entity-equipo'],
        'usuario' => ['icon' => 'bi-person', 'class' => 'entity-usuario'],
        'empleado' => ['icon' => 'bi-person-badge', 'class' => 'entity-empleado'],
        'departamento' => ['icon' => 'bi-building', 'class' => 'entity-departamento'],
        'inventario' => ['icon' => 'bi-box-seam', 'class' => 'entity-inventario'],
    ];
    return $config[$tipo] ?? ['icon' => 'bi-lightning', 'class' => 'entity-default'];
}

// Función para obtener URL según tipo
function getEntityUrl($tipo, $id) {
    $urls = [
        'soporte' => BASE_URL . 'soportes/ver/' . $id,
        'equipo' => BASE_URL . 'equipos/ver/' . $id,
        'usuario' => BASE_URL . 'usuarios/editar/' . $id,
        'empleado' => BASE_URL . 'empleados/editar/' . $id,
        'departamento' => BASE_URL . 'departamentos/editar/' . $id,
        'inventario' => BASE_URL . 'inventario/ver/' . $id,
    ];
    return $urls[$tipo] ?? '#';
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/bitacora-moderna.css?v=<?= time() ?>">

</div>
<div class="bitacora-layout-container">

    <!-- HEADER -->
    <div class="bitacora-header">
        <div>
            <h1 class="bitacora-title">
                <i class="bi bi-clock-history text-brand"></i>
                Bitácora del Sistema
            </h1>
            <p class="bitacora-subtitle">Historial detallado de cambios y movimientos en el sistema.</p>
        </div>
            
        <div class="bitacora-toolbar">
            <div class="bitacora-search-wrapper">
                <i class="bi bi-search bitacora-search-icon"></i>
                <input type="text" id="searchInput" class="bitacora-search-input" placeholder="Buscar en bitácora..." oninput="filterActions()">
            </div>
            <div class="bitacora-filter-wrapper">
                <button onclick="toggleFilterDropdown()" id="filterBtn" class="bitacora-filter-btn">
                    <i class="bi bi-funnel"></i>
                    Filtros
                </button>
                <div id="filterDropdown" class="bitacora-filter-dropdown">
                    <button onclick="setEntityFilter('all')" class="filter-opt">Todas las acciones</button>
                    <button onclick="setEntityFilter('soporte')" class="filter-opt">Tickets</button>
                    <button onclick="setEntityFilter('equipo')" class="filter-opt">Equipos</button>
                    <button onclick="setEntityFilter('empleado')" class="filter-opt">Empleados</button>
                    <button onclick="setEntityFilter('inventario')" class="filter-opt">Inventario</button>
                </div>
            </div>
        </div>
    </div>

    <!-- TIMELINE CARD -->
    <div class="bitacora-timeline-card">
        
        <?php if (empty($acciones)): ?>
        <div class="bitacora-empty-state">
            <i class="bi bi-inbox bitacora-empty-icon-lg"></i>
            <h3 class="mh-text-dark fw-600 my-1-05">Sin acciones registradas</h3>
            <p class="fs-sm">No hay acciones en la bitácora aún.</p>
        </div>
        <?php else: ?>
        
        <div class="bitacora-timeline-wrapper">
            <!-- Línea vertical conectora -->
            <div class="bitacora-timeline-line"></div>

            <div id="timelineContainer">
                <?php $fechaIndex = 0; ?>
                <?php foreach ($accionesPorFecha as $fecha => $accionesDelDia): ?>
                
                <!-- Separador de Fecha -->
                <div class="fecha-grupo" data-fecha="<?= $fecha ?>">
                    <div class="fecha-label">Fecha</div>
                    <div class="fecha-badge <?= $fechaIndex === 0 ? 'today' : 'past' ?>">
                        <?= $fecha ?>
                    </div>
                </div>

                <?php foreach ($accionesDelDia as $accion): ?>
                <?php 
                $config = getEntityConfig($accion->enlace_tipo);
                $hora = date('h:i A', strtotime($accion->created_at));
                $url = $accion->enlace_id ? getEntityUrl($accion->enlace_tipo, $accion->enlace_id) : null;
                $tieneEnlace = !empty($accion->enlace_tipo) && !empty($accion->enlace_id);
                ?>
                <div class="accion-item" data-entity="<?= $accion->enlace_tipo ?>">
                    
                    <!-- Time Column -->
                    <div class="accion-time-col">
                        <span class="accion-time-text"><?= $hora ?></span>
                    </div>

                    <!-- Icon Node -->
                    <div class="accion-icon-node <?= $config['class'] ?>">
                        <i class="<?= $config['icon'] ?> fs-1"></i>
                    </div>

                    <!-- Content Card -->
                    <div class="accion-card">
                        <div class="accion-card-content">
                            <div>
                                <p class="accion-text">
                                    <span class="accion-username"><?= htmlspecialchars($accion->username) ?></span>
                                    <?= htmlspecialchars($accion->accion) ?>
                                </p>
                                <div class="accion-meta">
                                    <span class="accion-meta-type"><?= $accion->enlace_tipo ?: 'Sistema' ?></span>
                                    <span class="text-slate-300">•</span>
                                    <span class="fs-xs text-slate-400">Hace un momento</span>
                                </div>
                            </div>
                            
                            <?php if ($tieneEnlace && $url): ?>
                            <a href="<?= $url ?>" class="ver-btn" title="Ver detalles">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <?php elseif (!empty($accion->enlace_tipo)): ?>
                            <span class="no-link-badge" title="Sin enlace disponible">
                                <i class="bi bi-arrow-right"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
                
                <?php $fechaIndex++; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php endif; ?>
        
        <!-- Footer con Paginación Minimal Ghost -->
        <div class="bitacora-footer">
            
            <!-- Izquierda: Info y selector de items por página -->
            <div class="footer-info">
                <span class="footer-text">
                    Mostrando <strong class="text-slate-900"><?= count($acciones) ?></strong> de <strong class="text-slate-900"><?= $totalCount ?? $totalAcciones ?></strong> eventos
                </span>
                <div class="footer-per-page">
                    <span class="per-page-label">Mostrar:</span>
                    <select id="itemsPerPage" onchange="changeItemsPerPage()" class="per-page-select">
                        <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                        <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20</option>
                        <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </div>
            </div>
            
            <!-- Derecha: Paginación Minimal Ghost -->
            <div class="footer-pagination">
                <a href="<?= $currentPage > 1 ? BASE_URL . 'bitacora?page=' . ($currentPage - 1) . '&per_page=' . $perPage : '#' ?>" 
                   class="pagination-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <i class="bi bi-arrow-left"></i>
                    Anterior
                </a>

                <div class="pagination-info">
                    <span class="text-slate-900">Página <?= $currentPage ?></span>
                    <span class="text-slate-300">/</span>
                    <span><?= $totalPages ?></span>
                </div>

                <a href="<?= $currentPage < $totalPages ? BASE_URL . 'bitacora?page=' . ($currentPage + 1) . '&per_page=' . $perPage : '#' ?>" 
                   class="pagination-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    Siguiente
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

</div>
<div class="container-fluid">

<script src="<?= BASE_URL ?>js/bitacora.js?v=<?= time() ?>"></script>