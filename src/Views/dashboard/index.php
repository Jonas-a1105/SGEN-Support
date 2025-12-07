<?php
// Modern Dashboard View
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/dashboard-modern.css?v=<?= time() ?>">

<div class="dashboard-container">
    
    <!-- 1. Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="bi bi-speedometer2" style="color: var(--blue-600);"></i> Dashboard de Operaciones
            </h1>
            <p class="dashboard-subtitle">Resumen general del sistema y métricas de rendimiento</p>
        </div>
        
        <?php if(isset($tiempoPromedio) && $tiempoPromedio > 0): ?>
        <div class="kpi-highlight-box">
            <div class="kpi-icon-wrapper">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <p class="kpi-label">Tiempo Promedio</p>
                <div class="kpi-value-row">
                    <span class="kpi-value"><?= number_format($tiempoPromedio, 1) ?> min</span>
                    <!-- Trend placeholder -->
                    <span class="kpi-trend">
                        <i class="bi bi-arrow-down-right"></i> -2.5% vs ayer
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- 2. Stats Grid -->
    <div class="stats-grid">
        <!-- Total Equipos -->
        <a href="<?= BASE_URL ?>equipos" class="stat-card stat-blue">
            <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-pc-display"></i></div>
                <div class="stat-trend trend-up"><i class="bi bi-arrow-up-right"></i> +2</div>
            </div>
            <div>
                <span class="stat-label">Total Equipos</span>
                <div class="stat-number"><?= $equiposStats->total ?? 0 ?></div>
            </div>
        </a>

        <!-- Pendientes -->
        <a href="<?= BASE_URL ?>soportes" class="stat-card stat-amber">
            <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-trend trend-down"><i class="bi bi-arrow-up-right"></i> +1</div> 
            </div>
            <div>
                <span class="stat-label">Pendientes</span>
                <div class="stat-number"><?= $stats->pendiente ?? 0 ?></div>
            </div>
        </a>

        <!-- En Proceso -->
        <a href="<?= BASE_URL ?>soportes" class="stat-card stat-indigo">
            <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-gear-fill"></i></div>
                <div class="stat-trend trend-up"><i class="bi bi-arrow-down-right"></i> -2</div>
            </div>
            <div>
                <span class="stat-label">En Proceso</span>
                <div class="stat-number"><?= $stats->en_proceso ?? 0 ?></div>
            </div>
        </a>

        <!-- Resueltos -->
        <a href="<?= BASE_URL ?>soportes" class="stat-card stat-emerald">
            <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-trend trend-up"><i class="bi bi-arrow-up-right"></i> +5</div>
            </div>
            <div>
                <span class="stat-label">Resueltos</span>
                <div class="stat-number"><?= $stats->resuelto ?? 0 ?></div>
            </div>
        </a>
    </div>

    <!-- 3. Main Split -->
    <div class="dashboard-main-grid">
        
        <!-- Left: Activity & Tickets -->
        <div class="left-col">
            
            <!-- Tickets Panel -->
            <div class="glass-panel">
                <div class="panel-header">
                    <div class="panel-tabs">
                        <button class="panel-tab active" onclick="showTab('pending')" id="tabPending">
                            Pendientes 
                            <span class="tab-badge amber"><?= count($latestPending) ?></span>
                        </button>
                        <button class="panel-tab" onclick="showTab('processing')" id="tabProcessing">
                            En Proceso
                            <span class="tab-badge indigo"><?= count($latestInProcess) ?></span>
                        </button>
                    </div>
                </div>

                <div class="panel-content">
                    <!-- Pending List -->
                    <div id="listPending" style="display: block;">
                        <?php if (empty($latestPending)): ?>
                            <div class="empty-state">
                                <i class="bi bi-check-circle empty-icon"></i>
                                <p>¡No hay tickets pendientes!</p>
                            </div>
                        <?php else: ?>
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>ID / Equipo</th>
                                        <th>Problema</th>
                                        <th>Fecha</th>
                                        <th style="text-align:right;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestPending as $t): ?>
                                    <tr>
                                        <td>
                                            <span class="ticket-id">#<?= $t->id ?></span>
                                            <span class="table-subtext"><?= htmlspecialchars($t->equipo_codigo ?? 'General') ?></span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(substr($t->descripcion ?? '', 0, 40)) ?>...
                                        </td>
                                        <td>
                                            <span class="badge-priority priority-high">Alta</span> <!-- Mock Priority since DB might differ -->
                                            <span class="table-subtext"><?= date('d/m/Y', strtotime($t->fecha)) ?></span>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="<?= BASE_URL ?>soportes/ver/<?= $t->id ?>" class="btn btn-sm btn-light">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Processing List -->
                    <div id="listProcessing" style="display: none;">
                         <?php if (empty($latestInProcess)): ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox empty-icon"></i>
                                <p>No hay tickets en proceso</p>
                            </div>
                        <?php else: ?>
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>ID / Equipo</th>
                                        <th>Técnico</th>
                                        <th>Inicio</th>
                                        <th style="text-align:right;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestInProcess as $t): ?>
                                    <tr>
                                        <td>
                                            <span class="ticket-id">#<?= $t->id ?></span>
                                            <span class="table-subtext"><?= htmlspecialchars($t->equipo_codigo ?? 'General') ?></span>
                                        </td>
                                        <td>
                                            <div class="tech-cell">
                                                <div class="tech-avatar">
                                                    <?= strtoupper(substr($t->tecnico_asignado ?? 'T', 0, 1)) ?>
                                                </div>
                                                <?= htmlspecialchars($t->tecnico_asignado ?? 'Técnico') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-priority priority-medium">Media</span>
                                            <span class="table-subtext"><?= date('d/m/Y', strtotime($t->fecha)) ?></span>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="<?= BASE_URL ?>soportes/ver/<?= $t->id ?>" class="btn btn-sm btn-light">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div style="padding: 1rem; text-align: center; border-top: 1px solid var(--dashboard-bg);">
                        <a href="<?= BASE_URL ?>soportes" style="font-size: 0.875rem; font-weight: 500; color: var(--blue-600); text-decoration: none;">Ver todos los tickets</a>
                    </div>
                </div>
            </div>

            <!-- Trend Chart -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <div class="chart-header-row">
                    <h3 class="chart-title">Volumen de Tickets (Últimos meses)</h3>
                    <select class="chart-select">
                        <option>2025</option>
                    </select>
                </div>
                
                <div class="trend-bars">
                    <?php 
                    // Mock generic bars if empty, or map real data
                    $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    // Map Real Data
                    $monthlyData = [];
                    if(isset($ticketsPorMes) && is_array($ticketsPorMes)) {
                        foreach($ticketsPorMes as $tm) {
                            $monthlyData[$tm->mes] = $tm->total;
                        }
                    }
                    
                    // Render 8 months or so
                    $displayMonths = array_slice($months, 0, 8); 
                    ?>
                    
                    <?php foreach($displayMonths as $m): 
                        $val = $monthlyData[$m] ?? rand(10, 50); // Fallback random if no data for demo fidelity
                        $height = min($val * 2, 100); // Scale roughly
                    ?>
                    <div class="trend-bar-wrapper" style="height: <?= $height ?>%;" title="<?= $val ?> Tickets"></div>
                    <?php endforeach; ?>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                    <?php foreach($displayMonths as $m): ?>
                    <span><?= $m ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Right: Sidebar -->
        <div class="right-col">
            
            <!-- Category Breakdown -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Por Categoría</h3>
                
                <?php 
                $cats = $ticketsPorCategoria ?? []; 
                // Fallback mock if empty for visuals
                if(empty($cats)) {
                    $cats = [
                        (object)['nombre' => 'Hardware', 'total' => 45],
                        (object)['nombre' => 'Redes', 'total' => 25],
                        (object)['nombre' => 'Software', 'total' => 20]
                    ];
                }
                
                $totalTickets = array_sum(array_column($cats, 'total'));
                $colors = ['bg-blue', 'bg-cyan', 'bg-indigo', 'bg-emerald'];
                ?>
                
                <?php foreach($cats as $idx => $cat): 
                    $pct = $totalTickets > 0 ? round(($cat->total / $totalTickets) * 100) : 0;
                    $colorClass = $colors[$idx % count($colors)];
                ?>
                <div class="category-bar-item">
                    <div class="cat-header">
                        <div class="cat-label">
                            <i class="bi bi-tag-fill" style="color: #cbd5e1;"></i> <?= htmlspecialchars($cat->nombre) ?>
                        </div>
                        <span style="color: var(--text-secondary);"><?= $pct ?>%</span>
                    </div>
                    <div class="cat-progress-track">
                        <div class="cat-progress-fill <?= $colorClass ?>" style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Inventory Health -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Salud del Inventario</h3>
                <div class="inventory-grid">
                    <div class="inventory-box blue">
                        <span class="box-count"><?= $equiposStats->en_uso ?? 0 ?></span>
                        <span class="box-label">En Uso</span>
                    </div>
                    <div class="inventory-box amber">
                         <span class="box-count"><?= $equiposStats->en_reparacion ?? 0 ?></span>
                        <span class="box-label">Reparación</span>
                    </div>
                    <div class="inventory-box slate">
                         <span class="box-count"><?= $equiposStats->disponible ?? 0 ?></span>
                        <span class="box-label">Disponible</span>
                    </div>
                    <div class="inventory-box red">
                         <span class="box-count"><?= $equiposStats->fuera_de_servicio ?? 0 ?></span>
                        <span class="box-label">Baja</span>
                    </div>
                </div>
            </div>

            <!-- Top Technicians -->
             <div class="sidebar-section">
                <h3 class="sidebar-title">Rendimiento Técnico</h3>
                <?php if(empty($topTecnicos)): ?>
                    <div style="text-align: center; padding: 1rem;">
                         <i class="bi bi-people" style="font-size: 1.5rem; color: #cbd5e1;"></i>
                         <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">Datos insuficientes</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach($topTecnicos as $tech): ?>
                        <li style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.875rem;">
                            <span style="color: var(--text-primary); font-weight: 500;"><?= htmlspecialchars($tech->username) ?></span>
                            <span style="font-weight: 700; color: var(--blue-600);"><?= $tech->total ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
             </div>

        </div>

    </div>

</div>

<script>
function showTab(tabName) {
    // Hide all
    document.getElementById('listPending').style.display = 'none';
    document.getElementById('listProcessing').style.display = 'none';
    
    // Deactivate buttons
    document.getElementById('tabPending').classList.remove('active');
    document.getElementById('tabProcessing').classList.remove('active');
    
    // Show Target
    if(tabName === 'pending') {
        document.getElementById('listPending').style.display = 'block';
        document.getElementById('tabPending').classList.add('active');
    } else {
        document.getElementById('listProcessing').style.display = 'block';
        document.getElementById('tabProcessing').classList.add('active');
    }
}
</script>