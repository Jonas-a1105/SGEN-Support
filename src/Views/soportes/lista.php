<?php
// Required Helpers
require_once __DIR__ . '/../../Helpers/ColorHelper.php';
require_once __DIR__ . '/../../Helpers/BadgeHelper.php';
require_once __DIR__ . '/../../Helpers/PermissionHelper.php';
require_once __DIR__ . '/../../Helpers/ViewHelper.php';

use App\Helpers\{ColorHelper, BadgeHelper, PermissionHelper, ViewHelper};

// Obtener datos
$kpis = $kpis ?? ['pendientes' => 0, 'en_proceso' => 0, 'resueltos' => 0, 'alta_prioridad' => 0];
$categorias = $categorias ?? [];
?>

<!-- Header Moderno con fondo oscuro -->
<div class="support-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-headset"></i>
        Sistema de Gestión de Soportes
    </h1>
    <div class="d-flex gap-2">
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>reportes/soportes" target="_blank" class="btn btn-outline-light">
            <i class="bi bi-download me-1"></i> Exportar
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>soportes/crear" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Soporte
        </a>
    </div>
</div>

<?php
// KPI Dashboard - Usando ViewHelper para evitar redundancia
$kpiData = [
    ['value' => $kpis['pendientes'], 'label' => 'Pendientes', 'icon' => 'bi-clipboard', 'color' => 'orange'],
    ['value' => $kpis['en_proceso'], 'label' => 'En Proceso', 'icon' => 'bi-gear-fill', 'color' => 'blue'],
    ['value' => $kpis['resueltos'], 'label' => 'Resueltos', 'icon' => 'bi-check-circle-fill', 'color' => 'green'],
    ['value' => $kpis['alta_prioridad'], 'label' => 'Alta Prioridad', 'icon' => 'bi-exclamation-triangle-fill', 'color' => 'red']
];

echo ViewHelper::renderKpiCards($kpiData);
?>

<!-- Barra de Búsqueda automática -->
<div class="mb-3">
    <input type="text" class="form-control form-control-lg" id="globalSearch" placeholder="🔍 Buscar por serial, descripción, técnico...">
</div>

<!-- Filtros en una fila -->
<div class="filters-row">
    <div class="filter-group">
        <label><i class="bi bi-filter"></i> Estado</label>
        <select class="filter-select" id="filterEstado">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En Proceso</option>
            <option value="resuelto">Resuelto</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="bi bi-star-fill"></i> Prioridad</label>
        <select class="filter-select" id="filterPrioridad">
            <option value="">Todas las prioridades</option>
            <option value="alta">Alta</option>
            <option value="media">Media</option>
            <option value="baja">Baja</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="bi bi-tag-fill"></i> Categoría</label>
        <select class="filter-select" id="filterCategoria">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="bi bi-calendar3"></i> Fecha</label>
        <input type="date" class="filter-select" id="filterFecha">
    </div>
</div>

<!-- Tabla de Soportes -->
<div class="support-table-container">
    <div class="support-table-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-ul"></i>
            <h5 class="mb-0">Listado de Soportes</h5>
        </div>
        <span class="text-white-50 small">Mostrando <span id="recordCount"><?= count($soportes) ?></span> registros</span>
    </div>
    
    <div class="table-responsive">
        <table id="ticketsTable" class="support-table table table-hover mb-0">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>ESTADO</th>
                    <th>PRIORIDAD</th>
                    <th>CATEGORÍA</th>
                    <th>DEPARTAMENTO</th>
                    <th>TÉCNICO</th>
                    <th>EQUIPO (SERIAL)</th>
                    <th class="text-end">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($soportes as $soporte): ?>
                    <tr>
                        <td><?= ViewHelper::formatDate($soporte->fecha) ?></td>
                        <td><?= BadgeHelper::renderEstado($soporte->estado) ?></td>
                        <td><?= BadgeHelper::renderPrioridad($soporte->prioridad) ?></td>
                        <td><?= BadgeHelper::renderCategoria($soporte) ?></td>
                        <td class="text-uppercase"><?= ViewHelper::escape($soporte->departamento_nombre) ?></td>
                        <td><?= ViewHelper::escape($soporte->tecnico_asignado, 'Sin asignar') ?></td>
                        <td><?= ViewHelper::escape($soporte->equipo_serial) ?> (<?= ViewHelper::escape($soporte->equipo_tipo) ?>)</td>
                        <td class="text-end"><?= ViewHelper::renderActionButtons($soporte, $_SESSION) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    const table = $('#ticketsTable').DataTable({
        language: {
            decimal: "",
            emptyTable: "No hay datos disponibles en la tabla",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            infoPostFix: "",
            thousands: ",",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron registros coincidentes",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        order: [[0, 'desc']], // Ordenar por fecha descendente
        pageLength: 25,
        dom: 'rtip' // Remover búsqueda predeterminada
    });
    
    // Búsqueda global en tiempo real
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Filtro por estado
    $('#filterEstado').on('change', function() {
        table.column(1).search(this.value).draw();
    });
    
    // Filtro por prioridad
    $('#filterPrioridad').on('change', function() {
        table.column(2).search(this.value).draw();
    });
    
    // Filtro por categoría
    $('#filterCategoria').on('change', function() {
        const selectedText = this.options[this.selectedIndex].text;
        table.column(3).search(selectedText === 'Todas las categorías' ? '' : selectedText).draw();
    });
    
    // Filtro por fecha
    $('#filterFecha').on('change', function() {
        const fecha = this.value;
        if (fecha) {
            const [year, month, day] = fecha.split('-');
            const fechaFormateada = `${day}/${month}/${year}`;
            table.column(0).search(fechaFormateada).draw();
        } else {
            table.column(0).search('').draw();
        }
    });
});
</script>

<style>
/* Override DataTables pagination alignment */
#ticketsTable_paginate {
    float: right !important;
    text-align: right !important;
}

#ticketsTable_info {
    float: left !important;
    text-align: left !important;
}

#ticketsTable_wrapper::after {
    content: "";
    display: table;
    clear: both;
}
</style>
