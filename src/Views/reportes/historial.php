<?php
// src/Views/reportes/historial.php

// Leer preferencia de paginación desde cookie
$paginationPerPage = isset($_COOKIE['sgen_pagination_per_page']) ? (int)$_COOKIE['sgen_pagination_per_page'] : 10;

// Calcular reportes de hoy
$reportesHoy = count(array_filter($historial, fn($h) => date('Y-m-d', strtotime($h->fecha)) === date('Y-m-d')));
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/reportes-moderno.css?v=<?= time() ?>">

<div class="reportes-full-width-wrapper">
    <div style="max-width: 1150px; width: 100%; display: flex; flex-direction: column; gap: 1rem;">

        <!-- HEADER (Outside Container) -->
        <div class="reportes-header">
            <div class="reportes-title-group">
                <div class="reportes-icon">
                    <i class="bi bi-clock-history" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h1 class="reportes-title">Historial de Reportes</h1>
                    <p class="reportes-subtitle">Registro de todos los reportes generados.</p>
                </div>
            </div>
            <div>
                <a href="<?= BASE_URL ?>reportes" class="reportes-btn-history">
                    <i class="bi bi-arrow-left"></i>
                    Volver a Reportes
                </a>
            </div>
        </div>

        <div class="reportes-white-container">
            
            <!-- STATS GRID -->
            <div class="reportes-stats-grid">
                <div class="reportes-stat-item">
                    <div class="reportes-stat-icon-wrapper" style="background: #e0e7ff; color: #6366f1;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="reportes-stat-info">
                        <p>Total Reportes</p>
                        <h3><?= count($historial) ?></h3>
                    </div>
                </div>
                <div class="reportes-stat-item">
                    <div class="reportes-stat-icon-wrapper" style="background: #fee2e2; color: #dc2626;">
                        <i class="bi bi-file-pdf"></i>
                    </div>
                    <div class="reportes-stat-info">
                        <p>PDFs</p>
                        <h3><?= count(array_filter($historial, fn($h) => $h->formato === 'PDF')) ?></h3>
                    </div>
                </div>
                <div class="reportes-stat-item">
                    <div class="reportes-stat-icon-wrapper" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </div>
                    <div class="reportes-stat-info">
                        <p>Excel/CSV</p>
                        <h3><?= count(array_filter($historial, fn($h) => $h->formato === 'Excel')) ?></h3>
                    </div>
                </div>
                <div class="reportes-stat-item">
                    <div class="reportes-stat-icon-wrapper" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div class="reportes-stat-info">
                        <p>Hoy</p>
                        <h3><?= $reportesHoy ?></h3>
                    </div>
                </div>
            </div>

            <!-- TOOLBAR -->
            <div class="reportes-toolbar">
                <h2 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">Descargas Recientes</h2>
                <div class="reportes-search">
                    <i class="bi bi-search reportes-input-icon"></i>
                    <input type="text" id="searchInput" placeholder="Buscar..." onkeyup="filterTable()" class="reportes-input">
                </div>
            </div>

            <!-- TABLE -->
            <div class="reportes-table-wrapper">
                <table class="reportes-table" id="historialTable">
                    <thead>
                        <tr>
                            <th>Reporte</th>
                            <th>Formato</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Filtros</th>
                            <th style="text-align: right;">Acciones</th>
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
                            
                            $reporteUrl = match($item->tipo) {
                                'Soportes' => BASE_URL . 'reportes/soportes',
                                'Inventario' => BASE_URL . 'reportes/inventario',
                                'Mantenimientos' => BASE_URL . 'reportes/mantenimientos',
                                default => BASE_URL . 'reportes'
                            };
                        ?>
                        <tr class="table-row">
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 36px; height: 36px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: <?= $iconBg ?>; color: <?= $iconColor ?>;">
                                        <i class="bi <?= $icon ?>"></i>
                                    </div>
                                    <span style="font-weight: 600; color: #0f172a;">Reporte de <?= $item->tipo ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="reportes-badge" style="background: <?= $formatBg ?>; color: <?= $formatColor ?>;">
                                    <i class="bi <?= $item->formato === 'PDF' ? 'bi-file-pdf' : 'bi-file-spreadsheet' ?>"></i>
                                    <?= $item->formato ?>
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 0.875rem;">
                                <div style="display: flex; align-items: center; gap: 0.375rem;">
                                    <i class="bi bi-clock" style="font-size: 0.75rem;"></i>
                                    <?= date('d/m/Y H:i', strtotime($item->fecha)) ?>
                                </div>
                            </td>
                            <td>
                                <span class="reportes-badge" style="background: #f1f5f9; color: #475569;">
                                    <i class="bi bi-person"></i>
                                    <?= htmlspecialchars($item->usuario) ?>
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 0.75rem; max-width: 200px;">
                                <span style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($item->filtros) ?>">
                                    <?= htmlspecialchars($item->filtros) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="<?= $reporteUrl ?>" target="_blank" class="reportes-action-btn" style="background: #e0e7ff; color: #6366f1;" title="Regenerar">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="<?= $reporteUrl ?>" target="_blank" class="reportes-action-btn" style="background: #f1f5f9; color: #64748b;" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- FOOTER -->
            <div class="reportes-footer">
                <!-- Left: Info & Per Page -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.875rem; color: #64748b;">
                        Mostrando <strong style="color: #0f172a;" id="visibleCount"><?= min($paginationPerPage, count($historial)) ?></strong> de <strong style="color: #0f172a;"><?= count($historial) ?></strong> registros
                    </span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                        <select id="itemsPerPage" onchange="changeItemsPerPage()" class="reportes-select" style="width: auto; padding: 0.375rem 0.5rem 0.375rem 0.5rem;">
                            <option value="5" <?= $paginationPerPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $paginationPerPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $paginationPerPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $paginationPerPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                </div>
                
                <!-- Right: Pagination -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1.5rem;">
                    <button onclick="previousPage()" id="btnPrev" class="reportes-pagination-btn" disabled>
                        <i class="bi bi-arrow-left"></i>
                        Anterior
                    </button>

                    <div style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #475569;">
                        <span style="color: #0f172a;">Página <span id="currentPage">1</span></span>
                        <span style="color: #cbd5e1;">/</span>
                        <span id="totalPages">1</span>
                    </div>

                    <button onclick="nextPage()" id="btnNext" class="reportes-pagination-btn">
                        Siguiente
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

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
            row.style.display = 'table-row'; // Fixed display property
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
            row.style.display = 'table-row';
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

