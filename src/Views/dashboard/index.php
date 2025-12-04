<style>
.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    transition: all 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.25);
}

.gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.gradient-success { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
            <p class="text-muted mb-0">Resumen general del sistema</p>
        </div>
        <?php if(isset($tiempoPromedio) && $tiempoPromedio > 0): ?>
        <div class="badge bg-light text-dark p-2 border">
            <i class="bi bi-clock-history me-1"></i>
            Tiempo Promedio Resolución: <strong><?= number_format($tiempoPromedio, 1) ?> min</strong>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tarjetas de Estadísticas (KPIs) -->
<div class="row g-4 mb-4">
    <!-- Total Equipos -->
    <div class="col-md-3">
        <div class="card glass-card gradient-primary text-white border-0 overflow-hidden h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Equipos</h6>
                        <h2 class="mb-0 fw-bold"><?= $equiposStats->total ?? 0 ?></h2>
                    </div>
                    <div class="opacity-50">
                        <i class="bi bi-pc-display fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Pendientes -->
    <div class="col-md-3">
        <div class="card glass-card gradient-warning text-white border-0 overflow-hidden h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Pendientes</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats->pendiente ?? 0 ?></h2>
                    </div>
                    <div class="opacity-50">
                        <i class="bi bi-hourglass-split fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets en Proceso -->
    <div class="col-md-3">
        <div class="card glass-card gradient-info text-white border-0 overflow-hidden h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">En Proceso</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats->en_proceso ?? 0 ?></h2>
                    </div>
                    <div class="opacity-50">
                        <i class="bi bi-gear-fill fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Resueltos -->
    <div class="col-md-3">
        <div class="card glass-card gradient-success text-white border-0 overflow-hidden h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Resueltos</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats->resuelto ?? 0 ?></h2>
                    </div>
                    <div class="opacity-50">
                        <i class="bi bi-check-circle-fill fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<?php if(isset($ticketsPorMes)): ?>
<div class="row g-4 mb-4">
    <!-- Gráfico Mensual -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tickets por Mes</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Categoría -->
    <div class="col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-tag me-2"></i>Por Categoría</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Prioridad -->
    <div class="col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-flag me-2"></i>Por Prioridad</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- Equipos por Estado -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Equipos por Estado</h5>
            </div>
            <div class="card-body">
                <?php 
                $total = $equiposStats->total ?? 0;
                $disponible = $equiposStats->disponible ?? 0;
                $en_uso = $equiposStats->en_uso ?? 0;
                $en_reparacion = $equiposStats->en_reparacion ?? 0;
                $fuera_servicio = $equiposStats->fuera_de_servicio ?? 0;
                ?>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Disponibles</span>
                        <strong class="text-success"><?= $disponible ?></strong>
                    </div>
                    <div class="progress" style="height: 15px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= $total > 0 ? ($disponible / $total * 100) : 0 ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>En Uso</span>
                        <strong class="text-primary"><?= $en_uso ?></strong>
                    </div>
                    <div class="progress" style="height: 15px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: <?= $total > 0 ? ($en_uso / $total * 100) : 0 ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>En Reparación</span>
                        <strong class="text-warning"><?= $en_reparacion ?></strong>
                    </div>
                    <div class="progress" style="height: 15px;">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: <?= $total > 0 ? ($en_reparacion / $total * 100) : 0 ?>%"></div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Fuera de Servicio</span>
                        <strong class="text-danger"><?= $fuera_servicio ?></strong>
                    </div>
                    <div class="progress" style="height: 15px;">
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: <?= $total > 0 ? ($fuera_servicio / $total * 100) : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Mantenimientos -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-tools me-2"></i>Mantenimientos</h5>
                <a href="<?= BASE_URL ?>mantenimientos" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ultimosMantenimientos)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">Sin registros</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($ultimosMantenimientos as $mant): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($mant->equipo_codigo ?? 'N/A') ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($mant->fecha)) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= ($mant->tipo_mantenimiento ?? '') == 'preventivo' ? 'info' : 'warning' ?>">
                                        <?= ucfirst(substr($mant->tipo_mantenimiento ?? 'N/A', 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Técnicos -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Técnicos</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topTecnicos)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No hay datos suficientes</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Técnico</th>
                                    <th class="text-end">Resueltos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topTecnicos as $tech): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2" style="width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                                    <?= strtoupper(substr($tech->username, 0, 2)) ?>
                                                </div>
                                                <?= htmlspecialchars($tech->username) ?>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold"><?= $tech->total ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tickets Pendientes y En Proceso -->
<div class="row g-4">
    <!-- Tickets Pendientes -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Tickets Pendientes</h5>
                <a href="<?= BASE_URL ?>soportes" class="btn btn-sm btn-outline-dark">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($latestPending)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle display-4 text-success"></i>
                        <p class="text-muted mt-2">¡No hay tickets pendientes!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Equipo</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestPending as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?= $ticket->id ?></strong></td>
                                        <td><?= htmlspecialchars(substr($ticket->descripcion ?? '', 0, 30)) ?>...</td>
                                        <td><small><?= date('d/m/Y', strtotime($ticket->fecha)) ?></small></td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>soportes/ver/<?= $ticket->id ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tickets En Proceso -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i>Tickets en Proceso</h5>
                <a href="<?= BASE_URL ?>soportes" class="btn btn-sm btn-outline-light">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($latestInProcess)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">No hay tickets en proceso</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Equipo</th>
                                    <th>Técnico</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestInProcess as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?= $ticket->id ?></strong></td>
                                        <td><?= htmlspecialchars(substr($ticket->descripcion ?? '', 0, 25)) ?>...</td>
                                        <td><small><?= htmlspecialchars($ticket->tecnico_asignado ?? 'N/A') ?></small></td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>soportes/ver/<?= $ticket->id ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(isset($ticketsPorMes)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para gráfico mensual
    const monthlyData = <?= json_encode($ticketsPorMes) ?>;
    const monthlyLabels = monthlyData.map(item => item.mes);
    const monthlyValues = monthlyData.map(item => item.total);

    // Gráfico Mensual
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Tickets',
                data: monthlyValues,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Datos para gráfico de prioridad
    const priorityData = <?= json_encode($ticketsPorPrioridad ?? []) ?>;
    const priorityLabels = Object.keys(priorityData).map(k => k.charAt(0).toUpperCase() + k.slice(1));
    const priorityValues = Object.values(priorityData);
    const priorityColors = {
        'alta': '#dc3545',
        'media': '#ffc107',
        'baja': '#198754'
    };
    const bgColors = Object.keys(priorityData).map(k => priorityColors[k] || '#6c757d');

    // Gráfico Prioridad
    new Chart(document.getElementById('priorityChart'), {
        type: 'doughnut',
        data: {
            labels: priorityLabels,
            datasets: [{
                data: priorityValues,
                backgroundColor: bgColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Datos para gráfico de categoría
    const categoryData = <?= json_encode($ticketsPorCategoria ?? []) ?>;
    const categoryLabels = categoryData.map(item => item.nombre);
    const categoryValues = categoryData.map(item => item.total);
    const categoryColors = [
        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140', '#667eea', '#764ba2'
    ];

    // Gráfico Categoría
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryValues,
                backgroundColor: categoryColors.slice(0, categoryLabels.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
<?php endif; ?>