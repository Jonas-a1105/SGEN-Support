<?php
// Leer preferencia de paginación desde cookie
$paginationPerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;

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

<div style="background: #f8fafc; padding: 2rem; width: 100vw; position: relative; left: 50%; margin-left: -50vw;">
    <div style="max-width: 1150px; min-width: 1150px; margin: 0 auto;">

        <!-- HEADER -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-shield-check" style="color: #6366f1;"></i>
                    Auditoría de Accesos
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0 0;">Monitoreo de seguridad y registro de sesiones.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <div style="position: relative;">
                    <button onclick="toggleDateDropdown()" id="dateFilterBtn" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 500; border-radius: 0.75rem; cursor: pointer; font-size: 0.875rem;">
                        <i class="bi bi-calendar3"></i>
                        <span id="dateFilterLabel">Últimos 30 días</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>
                    <div id="dateDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.25rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; z-index: 100; min-width: 160px;">
                        <button onclick="setDateFilter('7', 'Últimos 7 días')" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Últimos 7 días</button>
                        <button onclick="setDateFilter('30', 'Últimos 30 días')" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Últimos 30 días</button>
                        <button onclick="setDateFilter('90', 'Últimos 90 días')" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Últimos 90 días</button>
                        <button onclick="setDateFilter('all', 'Todo el historial')" style="width: 100%; padding: 0.5rem 1rem; text-align: left; border: none; background: white; cursor: pointer; font-size: 0.875rem; color: #475569;">Todo el historial</button>
                    </div>
                </div>
                <button onclick="exportLogs()" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #6366f1; color: white; font-weight: 500; border: none; border-radius: 0.75rem; cursor: pointer; font-size: 0.875rem; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);">
                    <i class="bi bi-download"></i>
                    Exportar Log
                </button>
            </div>
        </div>

        <!-- STATS SUMMARY -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: white; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="padding: 0.75rem; background: #d1fae5; color: #059669; border-radius: 0.5rem;">
                    <i class="bi bi-pc-display" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <p style="font-size: 0.625rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin: 0;">Sesiones Activas</p>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $sesionesActivas ?> Usuario<?= $sesionesActivas != 1 ? 's' : '' ?></h3>
                </div>
            </div>
            <div style="background: white; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="padding: 0.75rem; background: #dbeafe; color: #2563eb; border-radius: 0.5rem;">
                    <i class="bi bi-clock" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <p style="font-size: 0.625rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin: 0;">Promedio Duración</p>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $promedioDuracion ?> min</h3>
                </div>
            </div>
            <div style="background: white; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="padding: 0.75rem; background: #fef3c7; color: #d97706; border-radius: 0.5rem;">
                    <i class="bi bi-list-ul" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <p style="font-size: 0.625rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin: 0;">Total Registros</p>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $totalLogs ?> Sesiones</h3>
                </div>
            </div>
        </div>

        <!-- MAIN CARD -->
        <div style="background: white; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden;">
            
            <!-- Toolbar -->
            <div style="padding: 1rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fafbfc; flex-wrap: wrap; gap: 1rem;">
                <div style="position: relative; width: 350px;">
                    <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por usuario..." oninput="filterLogs()" 
                           style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.25rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.875rem; outline: none;">
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.75rem; font-weight: 500; color: #64748b;">Filtrar:</span>
                    <div style="display: flex; background: white; border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.25rem;">
                        <button onclick="setFilter('all')" id="btn-all" style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; border: none; cursor: pointer; background: #f1f5f9; color: #0f172a;">
                            Todos
                        </button>
                        <button onclick="setFilter('active')" id="btn-active" style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; border: none; cursor: pointer; background: transparent; color: #64748b;">
                            Activos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Listado -->
            <div id="logsList">
                <?php foreach ($logs as $log): ?>
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
                <div class="log-row <?= $esActiva ? 'active-session' : 'closed-session' ?>" data-fecha="<?= $log->fecha_inicio ?>" style="padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 1.5rem; transition: background 0.2s;">
                    
                    <!-- User Info -->
                    <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 200px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: <?= $esActiva ? '#eef2ff' : '#f1f5f9' ?>; color: <?= $esActiva ? '#6366f1' : '#64748b' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                            <?= $iniciales ?>
                        </div>
                        <div>
                            <h4 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0;"><?= htmlspecialchars($log->username) ?></h4>
                            <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">ID: <?= $log->usuario_id ?></p>
                        </div>
                    </div>

                    <!-- Session Info -->
                    <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #475569; font-size: 0.75rem;">
                            <i class="bi bi-calendar3" style="color: #94a3b8;"></i>
                            <span><?= $fechaInicio ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #475569; font-size: 0.75rem;">
                            <i class="bi bi-clock" style="color: #94a3b8;"></i>
                            <?php if ($esActiva): ?>
                                <span style="color: #059669; font-weight: 600;">● En curso</span>
                            <?php else: ?>
                                <span>Duración: <?= $duracion ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <?php if ($esActiva): ?>
                        <div style="padding: 0.5rem; background: #d1fae5; color: #059669; border-radius: 50%; border: 1px solid #a7f3d0;" title="Sesión Activa">
                            <i class="bi bi-check-circle-fill" style="font-size: 1rem;"></i>
                        </div>
                        <?php else: ?>
                        <div style="padding: 0.5rem; background: #f8fafc; color: #94a3b8; border-radius: 50%; border: 1px solid #e2e8f0;" title="Sesión Finalizada">
                            <i class="bi bi-box-arrow-right" style="font-size: 1rem;"></i>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Botón Ver -->
                        <button onclick="verDetalle('<?= htmlspecialchars($log->username) ?>', '<?= $log->usuario_id ?>', '<?= $log->fecha_inicio ?>', '<?= $log->fecha_fin ?? '' ?>', '<?= $duracion ?>', '<?= $esActiva ? 'Activa' : 'Cerrada' ?>')" 
                                style="padding: 0.5rem; background: white; color: #6366f1; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; font-weight: 500; transition: all 0.2s;"
                                onmouseover="this.style.background='#eef2ff'; this.style.borderColor='#c7d2fe';"
                                onmouseout="this.style.background='white'; this.style.borderColor='#e2e8f0';">
                            <i class="bi bi-eye"></i>
                            Ver
                        </button>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Footer con Paginación -->
            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                
                <!-- Izquierda: Info y selector de items por página -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.875rem; color: #64748b;">
                        Mostrando <strong style="color: #0f172a;" id="visibleCount"><?= min($paginationPerPage, $totalLogs) ?></strong> de <strong style="color: #0f172a;"><?= $totalLogs ?></strong> registros
                    </span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                        <select id="itemsPerPage" onchange="changeItemsPerPage()" style="padding: 0.375rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.75rem; color: #475569; background: white; cursor: pointer;">
                            <option value="5" <?= $paginationPerPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $paginationPerPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $paginationPerPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $paginationPerPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                </div>
                
                <!-- Derecha: Paginación Minimal Ghost -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1.5rem;">
                    <button onclick="previousPage()" id="btnPrev" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #64748b; background: transparent; border: none; cursor: pointer; transition: color 0.2s;" disabled>
                        <i class="bi bi-arrow-left" style="transition: transform 0.2s;"></i>
                        Anterior
                    </button>

                    <div style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #475569;">
                        <span style="color: #0f172a;">Página <span id="currentPage">1</span></span>
                        <span style="color: #cbd5e1;">/</span>
                        <span id="totalPages">1</span>
                    </div>

                    <button onclick="nextPage()" id="btnNext" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #64748b; background: transparent; border: none; cursor: pointer; transition: color 0.2s;">
                        Siguiente
                        <i class="bi bi-arrow-right" style="transition: transform 0.2s;"></i>
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
.log-row:hover {
    background: #f8fafc !important;
}
#btn-all:hover, #btn-active:hover {
    background: #f1f5f9 !important;
}
#dateDropdown button:hover {
    background: #f1f5f9 !important;
}
#btnPrev:not(:disabled):hover,
#btnNext:not(:disabled):hover {
    color: #6366f1 !important;
}
#btnPrev:not(:disabled):hover i {
    transform: translateX(-2px);
}
#btnNext:not(:disabled):hover i {
    transform: translateX(2px);
}
#btnPrev:disabled,
#btnNext:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>

<script>
let currentFilter = 'all';
let currentPage = 1;
let itemsPerPage = <?= $paginationPerPage ?>;
const allRows = document.querySelectorAll('.log-row');
const totalItems = allRows.length;

// Inicializar paginación
document.addEventListener('DOMContentLoaded', function() {
    updatePagination();
});

function updatePagination() {
    const visibleRows = Array.from(allRows).filter(row => row.style.display !== 'none' || row.style.display === '');
    const filteredRows = getFilteredRows();
    const totalFiltered = filteredRows.length;
    const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;
    
    if (currentPage > totalPages) currentPage = totalPages;
    
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    
    // Enable/disable buttons
    document.getElementById('btnPrev').disabled = currentPage === 1;
    document.getElementById('btnNext').disabled = currentPage === totalPages;
    
    // Show/hide rows based on pagination
    let visibleCount = 0;
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    
    let filteredIndex = 0;
    allRows.forEach(row => {
        const isActive = row.classList.contains('active-session');
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const text = row.textContent.toLowerCase();
        
        let showByFilter = currentFilter === 'all' || (currentFilter === 'active' && isActive);
        let showBySearch = text.includes(searchTerm);
        
        if (showByFilter && showBySearch) {
            if (filteredIndex >= start && filteredIndex < end) {
                row.style.display = 'flex';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
            filteredIndex++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
}

function getFilteredRows() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    return Array.from(allRows).filter(row => {
        const isActive = row.classList.contains('active-session');
        const text = row.textContent.toLowerCase();
        let showByFilter = currentFilter === 'all' || (currentFilter === 'active' && isActive);
        let showBySearch = text.includes(searchTerm);
        return showByFilter && showBySearch;
    });
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
    }
}

function nextPage() {
    const filteredRows = getFilteredRows();
    const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updatePagination();
    }
}

function changeItemsPerPage() {
    itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
    // Guardar preferencia globalmente
    if (window.PaginationPrefs) {
        PaginationPrefs.set(itemsPerPage);
    }
    currentPage = 1;
    updatePagination();
}

function filterLogs() {
    currentPage = 1;
    updatePagination();
}

function setFilter(filter) {
    currentFilter = filter;
    currentPage = 1;
    
    // Update button styles
    document.getElementById('btn-all').style.background = filter === 'all' ? '#f1f5f9' : 'transparent';
    document.getElementById('btn-all').style.color = filter === 'all' ? '#0f172a' : '#64748b';
    document.getElementById('btn-active').style.background = filter === 'active' ? '#d1fae5' : 'transparent';
    document.getElementById('btn-active').style.color = filter === 'active' ? '#059669' : '#64748b';
    
    updatePagination();
}

// Date filter dropdown
function toggleDateDropdown() {
    const dropdown = document.getElementById('dateDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function setDateFilter(days, label) {
    document.getElementById('dateFilterLabel').textContent = label;
    document.getElementById('dateDropdown').style.display = 'none';
    
    // Filter by date (client-side simulation - for real filtering, would need server-side)
    const now = new Date();
    allRows.forEach(row => {
        if (days === 'all') {
            row.dataset.dateFiltered = 'false';
        } else {
            const rowDate = new Date(row.dataset.fecha);
            const diffDays = (now - rowDate) / (1000 * 60 * 60 * 24);
            row.dataset.dateFiltered = diffDays > parseInt(days) ? 'true' : 'false';
        }
    });
    
    currentPage = 1;
    updatePagination();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('dateDropdown');
    const btn = document.getElementById('dateFilterBtn');
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// Export function
function exportLogs() {
    let csvContent = "Usuario,ID,Fecha Inicio,Fecha Fin,Duración,Estado\n";
    
    allRows.forEach(row => {
        const username = row.querySelector('h4').textContent;
        const id = row.querySelector('p').textContent.replace('ID: ', '');
        const fecha = row.querySelectorAll('span')[0]?.textContent || '';
        const duracion = row.querySelectorAll('span')[1]?.textContent || '';
        const estado = row.classList.contains('active-session') ? 'Activa' : 'Cerrada';
        
        csvContent += `"${username}","${id}","${fecha}","","${duracion}","${estado}"\n`;
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'auditoria_accesos_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

// Ver detalle de sesión
function verDetalle(username, userId, fechaInicio, fechaFin, duracion, estado) {
    const modal = document.getElementById('detalleModal');
    
    document.getElementById('modal-username').textContent = username;
    document.getElementById('modal-userId').textContent = userId;
    document.getElementById('modal-fechaInicio').textContent = fechaInicio;
    document.getElementById('modal-fechaFin').textContent = fechaFin || 'Sesión activa';
    document.getElementById('modal-duracion').textContent = duracion;
    document.getElementById('modal-estado').textContent = estado;
    document.getElementById('modal-estado').style.color = estado === 'Activa' ? '#059669' : '#64748b';
    document.getElementById('modal-estado').style.background = estado === 'Activa' ? '#d1fae5' : '#f1f5f9';
    
    // Iniciales
    document.getElementById('modal-avatar').textContent = username.substring(0, 2).toUpperCase();
    
    modal.style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('detalleModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
document.getElementById('detalleModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>

<!-- Modal de Detalle -->
<div id="detalleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 2rem;">
    <div style="background: white; border-radius: 1rem; max-width: 450px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
        
        <!-- Header -->
        <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-info-circle" style="color: #6366f1;"></i>
                Detalle de Sesión
            </h3>
            <button onclick="cerrarModal()" style="background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 1.25rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div style="padding: 1.5rem;">
            
            <!-- Usuario -->
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <div id="modal-avatar" style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                    AD
                </div>
                <div>
                    <h4 id="modal-username" style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">Username</h4>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">ID de Usuario: <span id="modal-userId" style="font-weight: 600; color: #0f172a;">1</span></p>
                </div>
            </div>
            
            <!-- Detalles -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.875rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-calendar3"></i> Inicio de Sesión
                    </span>
                    <span id="modal-fechaInicio" style="font-size: 0.875rem; font-weight: 600; color: #0f172a;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.875rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-calendar3"></i> Fin de Sesión
                    </span>
                    <span id="modal-fechaFin" style="font-size: 0.875rem; font-weight: 600; color: #0f172a;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.875rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-clock"></i> Duración
                    </span>
                    <span id="modal-duracion" style="font-size: 0.875rem; font-weight: 600; color: #0f172a;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.875rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-shield-check"></i> Estado
                    </span>
                    <span id="modal-estado" style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 9999px;">-</span>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button onclick="cerrarModal()" style="padding: 0.5rem 1.5rem; background: #0f172a; color: white; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer;">
                Cerrar
            </button>
        </div>
    </div>
</div>