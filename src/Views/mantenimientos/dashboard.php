<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0 text-dark">📊 Dashboard de Mantenimientos</h2>
            <a href="<?= BASE_URL ?>mantenimientos/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Programar Mantenimiento
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total = count($todos ?? []);
            $pendientes = count($proximos ?? []);
            $vencidos = 0;
            foreach ($proximos ?? [] as $m) {
                if ($m->proxima_fecha && strtotime($m->proxima_fecha) < time()) {
                    $vencidos++;
                }
            }
            ?>
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Registro
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-tools fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Próximos (30 días)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendientes ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Vencidos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $vencidos ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Programados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $programados = 0;
                                    foreach ($todos ?? [] as $m) {
                                        if ($m->frecuencia && $m->frecuencia != 'unica') {
                                            $programados++;
                                        }
                                    }
                                    echo $programados;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-arrow-repeat fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas: Mantenimientos Próximos y Vencidos -->
        <?php if (!empty($proximos)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Requieren Atención (próximos 30 días)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Equipo</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Programado Para</th>
                                    <th>Días Restantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximos as $m): ?>
                                    <?php
                                    $dias = floor((strtotime($m->proxima_fecha) - time()) / (60*60*24));
                                    $alertClass = $dias < 0 ? 'table-danger' : ($dias <= 7 ? 'table-warning' : '');
                                    ?>
                                    <tr class="<?= $alertClass ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($m->equipo_codigo) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($m->equipo_tipo) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $m->tipo_mantenimiento == 'preventivo' ? 'primary' : 'danger' ?>">
                                                <?= ucfirst($m->tipo_mantenimiento) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars(substr($m->descripcion, 0, 40)) ?>...</td>
                                        <td><?= date('d/m/Y', strtotime($m->proxima_fecha)) ?></td>
                                        <td>
                                            <?php if ($dias < 0): ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-exclamation-circle"></i> Vencido hace <?= abs($dias) ?> días
                                                </span>
                                            <?php elseif ($dias == 0): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-exclamation-triangle"></i> ¡HOY!
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info">
                                                    <?= $dias ?> días
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>mantenimientos/editar/<?= $m->id ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-circle"></i> Realizar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Acciones Rápidas -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">🔗 Acciones Rápidas</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= BASE_URL ?>mantenimientos" class="list-group-item list-group-item-action">
                            <i class="bi bi-list-ul"></i> Ver todos los mantenimientos
                        </a>
                        <a href="<?= BASE_URL ?>mantenimientos/crear" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Programar nuevo mantenimiento
                        </a>
                        <a href="<?= BASE_URL ?>equipos" class="list-group-item list-group-item-action">
                            <i class="bi bi-pc-display"></i> Gestionar equipos
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">💡 Tips de Mantenimiento Preventivo</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Programa mantenimientos ANTES de que fallen
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Usa frecuencia "Trimestral" para limpieza de equipos
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Revisa servidores y equipos críticos mensualmente
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-check-circle text-success"></i>
                                Documenta todo en "Observaciones"
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/Views/layout/footer.php'; ?>
