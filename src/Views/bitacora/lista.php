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

// Función para obtener configuración de ícono y color según entidad
function getEntityConfig($tipo) {
    $config = [
        'soporte' => ['icon' => 'bi-ticket-detailed', 'color' => '#d97706', 'bg' => '#fef3c7'],
        'equipo' => ['icon' => 'bi-pc-display', 'color' => '#2563eb', 'bg' => '#dbeafe'],
        'usuario' => ['icon' => 'bi-person', 'color' => '#6366f1', 'bg' => '#e0e7ff'],
        'empleado' => ['icon' => 'bi-person-badge', 'color' => '#059669', 'bg' => '#d1fae5'],
        'departamento' => ['icon' => 'bi-building', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
        'inventario' => ['icon' => 'bi-box-seam', 'color' => '#059669', 'bg' => '#d1fae5'],
    ];
    return $config[$tipo] ?? ['icon' => 'bi-lightning', 'color' => '#64748b', 'bg' => '#f1f5f9'];
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

<div style="background: #f8fafc; padding: 2rem; width: 100vw; position: relative; left: 50%; margin-left: -50vw;">
    <div style="max-width: 1150px; min-width: 1150px; margin: 0 auto;">

        <!-- HEADER -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-clock-history" style="color: #6366f1;"></i>
                    Bitácora de Acciones
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0 0;">Historial detallado de cambios y movimientos en el sistema.</p>
            </div>
            
            <div style="display: flex; gap: 0.75rem;">
                <div style="position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="searchInput" placeholder="Buscar en bitácora..." oninput="filterActions()" 
                           style="width: 250px; padding: 0.5rem 1rem 0.5rem 2.25rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; outline: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                </div>
                <div style="position: relative;">
                    <button onclick="toggleFilterDropdown()" id="filterBtn" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 500; border-radius: 0.75rem; cursor: pointer; font-size: 0.875rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <i class="bi bi-funnel"></i>
                        Filtros
                    </button>
                    <div id="filterDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.25rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; z-index: 100; min-width: 160px;">
                        <button onclick="setEntityFilter('all')" class="filter-opt" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Todas las acciones</button>
                        <button onclick="setEntityFilter('soporte')" class="filter-opt" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Tickets</button>
                        <button onclick="setEntityFilter('equipo')" class="filter-opt" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Equipos</button>
                        <button onclick="setEntityFilter('empleado')" class="filter-opt" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Empleados</button>
                        <button onclick="setEntityFilter('inventario')" class="filter-opt" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Inventario</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TIMELINE CARD -->
        <div style="background: white; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden; padding: 2rem;">
            
            <?php if (empty($acciones)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                <h3 style="margin: 1rem 0 0.5rem; color: #0f172a; font-weight: 600;">Sin acciones registradas</h3>
                <p style="font-size: 0.875rem;">No hay acciones en la bitácora aún.</p>
            </div>
            <?php else: ?>
            
            <div style="position: relative;">
                <!-- Línea vertical conectora -->
                <div style="position: absolute; left: 84px; top: 40px; bottom: 40px; width: 2px; background: #f1f5f9;"></div>

                <div id="timelineContainer">
                    <?php $fechaIndex = 0; ?>
                    <?php foreach ($accionesPorFecha as $fecha => $accionesDelDia): ?>
                    
                    <!-- Separador de Fecha -->
                    <div class="fecha-grupo" data-fecha="<?= $fecha ?>" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; <?= $fechaIndex > 0 ? 'margin-top: 2rem;' : '' ?>">
                        <div style="width: 64px; text-align: right; font-size: 0.625rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Fecha</div>
                        <div style="padding: 0.375rem 0.75rem; border-radius: 9999px; background: <?= $fechaIndex === 0 ? '#f1f5f9' : '#fafbfc' ?>; color: <?= $fechaIndex === 0 ? '#475569' : '#94a3b8' ?>; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; position: relative; z-index: 10;">
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
                    <div class="accion-item" data-entity="<?= $accion->enlace_tipo ?>" style="position: relative; display: flex; margin-bottom: 1.5rem;">
                        
                        <!-- Time Column -->
                        <div style="width: 64px; display: flex; flex-direction: column; align-items: flex-end; padding-top: 0.25rem; padding-right: 1rem; text-align: right;">
                            <span style="font-size: 0.875rem; font-weight: 700; color: #475569;"><?= $hora ?></span>
                        </div>

                        <!-- Icon Node -->
                        <div style="position: relative; z-index: 10; width: 40px; height: 40px; border-radius: 50%; border: 4px solid white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: <?= $config['bg'] ?>; color: <?= $config['color'] ?>;">
                            <i class="<?= $config['icon'] ?>" style="font-size: 1rem;"></i>
                        </div>

                        <!-- Content Card -->
                        <div class="accion-card" style="flex: 1; margin-left: 1rem; background: #fafbfc; border-radius: 0.75rem; padding: 1rem; border: 1px solid transparent; transition: all 0.2s; cursor: pointer;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <p style="font-size: 0.875rem; color: #475569; margin: 0;">
                                        <span style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($accion->username) ?></span>
                                        <?= htmlspecialchars($accion->accion) ?>
                                    </p>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                                        <span style="font-size: 0.75rem; font-weight: 500; color: #94a3b8; text-transform: capitalize;"><?= $accion->enlace_tipo ?: 'Sistema' ?></span>
                                        <span style="color: #e2e8f0;">•</span>
                                        <span style="font-size: 0.75rem; color: #94a3b8;">Hace un momento</span>
                                    </div>
                                </div>
                                
                                <?php if ($tieneEnlace && $url): ?>
                                <a href="<?= $url ?>" class="ver-btn" style="padding: 0.5rem; color: #6366f1; background: #eef2ff; border-radius: 0.5rem; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s; border: 1px solid #c7d2fe;" title="Ver detalles">
                                    <i class="bi bi-arrow-right" style="font-size: 1rem;"></i>
                                </a>
                                <?php elseif (!empty($accion->enlace_tipo)): ?>
                                <span style="padding: 0.5rem; color: #cbd5e1; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;" title="Sin enlace disponible">
                                    <i class="bi bi-arrow-right" style="font-size: 1rem;"></i>
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
            <div style="margin-top: 2rem; padding: 1rem 0; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                
                <!-- Izquierda: Info y selector de items por página -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.875rem; color: #64748b;">
                        Mostrando <strong style="color: #0f172a;"><?= count($acciones) ?></strong> de <strong style="color: #0f172a;"><?= $totalCount ?? $totalAcciones ?></strong> eventos
                    </span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                        <select id="itemsPerPage" onchange="changeItemsPerPage()" style="padding: 0.375rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.75rem; color: #475569; background: white; cursor: pointer;">
                            <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                </div>
                
                <!-- Derecha: Paginación Minimal Ghost -->
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <a href="<?= $currentPage > 1 ? BASE_URL . 'bitacora?page=' . ($currentPage - 1) . '&per_page=' . $perPage : '#' ?>" 
                       style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: <?= $currentPage > 1 ? '#64748b' : '#cbd5e1' ?>; background: transparent; text-decoration: none; transition: color 0.2s; <?= $currentPage <= 1 ? 'pointer-events: none;' : '' ?>"
                       class="pagination-btn">
                        <i class="bi bi-arrow-left"></i>
                        Anterior
                    </a>

                    <div style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #475569;">
                        <span style="color: #0f172a;">Página <?= $currentPage ?></span>
                        <span style="color: #cbd5e1;">/</span>
                        <span><?= $totalPages ?></span>
                    </div>

                    <a href="<?= $currentPage < $totalPages ? BASE_URL . 'bitacora?page=' . ($currentPage + 1) . '&per_page=' . $perPage : '#' ?>" 
                       style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: <?= $currentPage < $totalPages ? '#64748b' : '#cbd5e1' ?>; background: transparent; text-decoration: none; transition: color 0.2s; <?= $currentPage >= $totalPages ? 'pointer-events: none;' : '' ?>"
                       class="pagination-btn">
                        Siguiente
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
.filter-opt:hover {
    background: #f1f5f9 !important;
}
.accion-card:hover {
    background: white !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.ver-btn:hover {
    color: #6366f1 !important;
    background: #eef2ff;
}
.pagination-btn:hover {
    color: #6366f1 !important;
}
.pagination-btn:hover i {
    transform: translateX(2px);
}
.pagination-btn:first-of-type:hover i {
    transform: translateX(-2px);
}
</style>

<script>
let currentEntityFilter = 'all';

function filterActions() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.accion-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const entity = item.dataset.entity;
        
        let showBySearch = text.includes(searchTerm);
        let showByFilter = currentEntityFilter === 'all' || entity === currentEntityFilter;
        
        if (showBySearch && showByFilter) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Ocultar grupos de fecha sin items visibles
    document.querySelectorAll('.fecha-grupo').forEach(grupo => {
        let nextSibling = grupo.nextElementSibling;
        let hasVisibleItems = false;
        
        while (nextSibling && !nextSibling.classList.contains('fecha-grupo')) {
            if (nextSibling.classList.contains('accion-item') && nextSibling.style.display !== 'none') {
                hasVisibleItems = true;
                break;
            }
            nextSibling = nextSibling.nextElementSibling;
        }
        
        grupo.style.display = hasVisibleItems ? 'flex' : 'none';
    });
}

function toggleFilterDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function setEntityFilter(entity) {
    currentEntityFilter = entity;
    document.getElementById('filterDropdown').style.display = 'none';
    filterActions();
}

function changeItemsPerPage() {
    const perPage = document.getElementById('itemsPerPage').value;
    // Guardar preferencia globalmente
    if (window.PaginationPrefs) {
        PaginationPrefs.set(perPage);
    }
    window.location.href = '<?= BASE_URL ?>bitacora?page=1&per_page=' + perPage;
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('filterDropdown');
    const btn = document.getElementById('filterBtn');
    if (dropdown && btn && !btn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});
</script>