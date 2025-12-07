<?php
// src/Views/reportes/historial.php

// Leer preferencia de paginación desde cookie
$paginationPerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;

// Calcular reportes de hoy
$reportesHoy = count(array_filter($historial, fn($h) => date('Y-m-d', strtotime($h->fecha)) === date('Y-m-d')));
?>

<div style="background: #f8fafc; min-height: 100vh; padding: 2rem;">
    <div style="max-width: 1150px; min-width: 1150px; margin: 0 auto;">

        <!-- HEADER -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="padding: 0.5rem; background: #e0e7ff; border-radius: 0.5rem; color: #6366f1;">
                    <i class="bi bi-clock-history" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Historial de Reportes</h1>
                    <p style="color: #64748b; margin: 0; font-size: 0.875rem;">Registro de todos los reportes generados.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>reportes" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 500; border-radius: 0.75rem; text-decoration: none; font-size: 0.875rem;">
                <i class="bi bi-arrow-left"></i>
                Volver a Reportes
            </a>
        </div>

        <!-- STATS CARDS -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #e0e7ff; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #6366f1;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Total Reportes</p>
                        <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= count($historial) ?></h3>
                    </div>
                </div>
            </div>
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #fee2e2; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                        <i class="bi bi-file-pdf"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;">PDFs</p>
                        <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= count(array_filter($historial, fn($h) => $h->formato === 'PDF')) ?></h3>
                    </div>
                </div>
            </div>
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #d1fae5; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #10b981;">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Excel/CSV</p>
                        <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= count(array_filter($historial, fn($h) => $h->formato === 'Excel')) ?></h3>
                    </div>
                </div>
            </div>
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Hoy</p>
                        <h3 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $reportesHoy ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div style="background: white; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden;">
            
            <!-- Table Header -->
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-weight: 700; color: #0f172a; margin: 0; font-size: 1rem;">Descargas Recientes</h2>
                <div style="position: relative; max-width: 250px;">
                    <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="searchInput" placeholder="Buscar..." onkeyup="filterTable()" style="width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: white; color: #1e293b; font-size: 0.875rem;">
                </div>
            </div>

            <!-- Table -->
            <table style="width: 100%; border-collapse: collapse;" id="historialTable">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Reporte</th>
                        <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Formato</th>
                        <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Fecha</th>
                        <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Usuario</th>
                        <th style="padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Filtros</th>
                        <th style="padding: 0.875rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $item): ?>
                    <?php
                        $iconBg = match($item->tipo) {
                            'Soportes' => '#e0e7ff',
                            'Inventario' => '#d1fae5',
                            'Mantenimientos' => '#fef3c7',
                            default => '#f1f5f9'
                        };
                        $iconColor = match($item->tipo) {
                            'Soportes' => '#6366f1',
                            'Inventario' => '#10b981',
                            'Mantenimientos' => '#f59e0b',
                            default => '#64748b'
                        };
                        $icon = match($item->tipo) {
                            'Soportes' => 'bi-ticket-detailed',
                            'Inventario' => 'bi-box-seam',
                            'Mantenimientos' => 'bi-tools',
                            default => 'bi-file-text'
                        };
                        $formatBg = $item->formato === 'PDF' ? '#fee2e2' : '#d1fae5';
                        $formatColor = $item->formato === 'PDF' ? '#dc2626' : '#10b981';
                        
                        // Determinar URL de reporte
                        $reporteUrl = match($item->tipo) {
                            'Soportes' => BASE_URL . 'reportes/soportes',
                            'Inventario' => BASE_URL . 'reportes/inventario',
                            'Mantenimientos' => BASE_URL . 'reportes/mantenimientos',
                            default => BASE_URL . 'reportes'
                        };
                        $excelUrl = $item->tipo === 'Soportes' ? BASE_URL . 'reportes/soportes_excel' : $reporteUrl;
                    ?>
                    <tr class="table-row" style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem; color: #1e293b;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: <?= $iconBg ?>; color: <?= $iconColor ?>;">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <span style="font-weight: 600; color: #0f172a;">Reporte de <?= $item->tipo ?></span>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 0.25rem; background: <?= $formatBg ?>; color: <?= $formatColor ?>;">
                                <i class="bi <?= $item->formato === 'PDF' ? 'bi-file-pdf' : 'bi-file-spreadsheet' ?>"></i>
                                <?= $item->formato ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; color: #64748b; font-size: 0.875rem;">
                            <div style="display: flex; align-items: center; gap: 0.375rem;">
                                <i class="bi bi-clock" style="font-size: 0.75rem;"></i>
                                <?= date('d/m/Y H:i', strtotime($item->fecha)) ?>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 500; border-radius: 9999px; background: #f1f5f9; color: #475569;">
                                <i class="bi bi-person"></i>
                                <?= htmlspecialchars($item->usuario) ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; color: #64748b; font-size: 0.75rem; max-width: 200px;">
                            <span style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($item->filtros) ?>">
                                <?= htmlspecialchars($item->filtros) ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="<?= $reporteUrl ?>" target="_blank" style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #e0e7ff; color: #6366f1; text-decoration: none;" title="Regenerar">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                                <a href="<?= $reporteUrl ?>" target="_blank" style="width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #64748b; text-decoration: none;" title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Footer con Paginación -->
            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                
                <!-- Izquierda: Info y selector de items por página -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.875rem; color: #64748b;">
                        Mostrando <strong style="color: #0f172a;" id="visibleCount"><?= min($paginationPerPage, count($historial)) ?></strong> de <strong style="color: #0f172a;"><?= count($historial) ?></strong> registros
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
let currentPage = 1;
let itemsPerPage = <?= $paginationPerPage ?>;
const allRows = document.querySelectorAll('.table-row');
const totalItems = allRows.length;

// Inicializar con preferencia de PHP
document.addEventListener('DOMContentLoaded', function() {
    updatePagination();
});

function updatePagination() {
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    
    // Enable/disable buttons
    document.getElementById('btnPrev').disabled = currentPage === 1;
    document.getElementById('btnNext').disabled = currentPage === totalPages;
    
    // Show/hide rows
    let visibleCount = 0;
    allRows.forEach((row, index) => {
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        
        if (index >= start && index < end) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
    }
}

function nextPage() {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
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

function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    let visibleCount = 0;
    
    allRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
    
    // Reset pagination when searching
    document.getElementById('currentPage').textContent = '1';
    document.getElementById('totalPages').textContent = Math.ceil(visibleCount / itemsPerPage) || 1;
}

// Initialize pagination on load
document.addEventListener('DOMContentLoaded', updatePagination);
</script>

