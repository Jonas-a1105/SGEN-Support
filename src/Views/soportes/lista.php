<?php
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

<!-- KPI Dashboard - 4 tarjetas -->
<div class="kpi-cards">
    <!-- Pendientes - Naranja -->
    <div class="kpi-card">
        <div class="kpi-icon orange">
            <i class="bi bi-clipboard"></i>
        </div>
        <div class="kpi-content">
            <h3><?= $kpis['pendientes'] ?></h3>
            <p>Pendientes</p>
        </div>
    </div>
    
    <!-- En Proceso - Azul -->
    <div class="kpi-card">
        <div class="kpi-icon blue">
            <i class="bi bi-gear-fill"></i>
        </div>
        <div class="kpi-content">
            <h3><?= $kpis['en_proceso'] ?></h3>
            <p>En Proceso</p>
        </div>
    </div>
    
    <!-- Resueltos - Verde -->
    <div class="kpi-card">
        <div class="kpi-icon green">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="kpi-content">
            <h3><?= $kpis['resueltos'] ?></h3>
            <p>Resueltos</p>
        </div>
    </div>
    
    <!-- Alta Prioridad - Rojo -->
    <div class="kpi-card">
        <div class="kpi-icon red">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="kpi-content">
            <h3><?= $kpis['alta_prioridad'] ?></h3>
            <p>Alta Prioridad</p>
        </div>
    </div>
</div>

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
                    <?php
                        // Clases para estado
                        $estadoClass = match($soporte->estado) {
                            'pendiente' => 'bg-warning text-dark',
                            'en_proceso' => 'bg-info text-dark',
                            'resuelto' => 'bg-success',
                            default => 'bg-secondary'
                        };
                        
                        // Clases para prioridad
                        $prioridadClass = match($soporte->prioridad ?? 'media') {
                            'alta' => 'bg-danger',
                            'media' => 'bg-warning text-dark',
                            'baja' => 'bg-success',
                            default => 'bg-secondary'
                        };
                    ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($soporte->fecha)) ?></td>
                        <td>
                            <span class="status-badge <?= $estadoClass ?>">
                                <?= strtoupper(str_replace('_', ' ', $soporte->estado)) ?>
                            </span>
                        </td>
                        <td>
                            <span class="priority-badge <?= $prioridadClass ?>">
                                <?= strtoupper($soporte->prioridad ?? 'MEDIA') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($soporte->categoria_nombre)): ?>
                                <?php
                                // Convertir el color a RGB y crear un fondo claro (15% opacidad)
                                $color = $soporte->categoria_color ?? '#6c757d';
                                $rgb = sscanf($color, "#%02x%02x%02x");
                                if ($rgb) {
                                    $lightBg = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 0.15)";
                                    $borderColor = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 0.3)";
                                } else {
                                    $lightBg = '#f3f4f6';
                                    $borderColor = '#e5e7eb';
                                }
                                ?>
                                <span class="category-badge-light" style="background-color: <?= $lightBg ?>; border: 1px solid <?= $borderColor ?>;">
                                    <i class="<?= $soporte->categoria_icono ?? 'bi bi-tag' ?>" style="color: <?= $color ?>"></i>
                                    <?= htmlspecialchars($soporte->categoria_nombre) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">--</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-uppercase"><?= htmlspecialchars($soporte->departamento_nombre) ?></td>
                        <td>
                            <?= htmlspecialchars($soporte->tecnico_asignado ?? 'Sin asignar') ?>
                        </td>
                        <td><?= htmlspecialchars($soporte->equipo_serial) ?> (<?= $soporte->equipo_tipo ?>)</td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <button class="action-btn btn-view" title="Ver" onclick="window.location.href='<?= BASE_URL ?>soportes/ver/<?= $soporte->id ?>'">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                
                                <?php
                                $canEdit = false;
                                if (isset($_SESSION['rol'])) {
                                    if ($_SESSION['rol'] === 'admin') {
                                        $canEdit = true;
                                    } elseif (in_array($_SESSION['rol'], ['tecnico', 'consultor'])) {
                                        if (isset($soporte->usuario_creacion_id) && $soporte->usuario_creacion_id == $_SESSION['user_id']) {
                                            $canEdit = true;
                                        }
                                    }
                                }
                                
                                if ($canEdit):
                                ?>
                                    <button class="action-btn btn-edit" title="Editar" onclick="window.location.href='<?= BASE_URL ?>soportes/editar/<?= $soporte->id ?>'">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    
                                    <button class="action-btn btn-assign" title="Asignar" onclick="window.location.href='<?= BASE_URL ?>soportes/asignar/<?= $soporte->id ?>'">
                                        <i class="bi bi-person-fill"></i>
                                    </button>
                                    
                                    <button class="action-btn btn-delete" title="Eliminar" onclick="if(confirm('¿Está seguro de eliminar el Ticket: <?= $soporte->id ?>?')) window.location.href='<?= BASE_URL ?>soportes/eliminar/<?= $soporte->id ?>'">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
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
