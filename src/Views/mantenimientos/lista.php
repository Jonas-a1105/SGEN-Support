<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="centered-card">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0 text-dark">🔧 Gestión de Mantenimientos</h2>
                <a href="index.php?url=mantenimientos/crear" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Programar Mantenimiento
                </a>
            </div>

            <?php
            // Filtrar mantenimientos para las pestañas
            $pendientes = array_filter($mantenimientos, fn($m) => in_array($m->estado, ['pendiente', 'en_proceso', 'pospuesto']));
            $programados = array_filter($mantenimientos, fn($m) => $m->frecuencia != 'unica');
            $completados = array_filter($mantenimientos, fn($m) => $m->estado == 'completado');
            ?>

            <!-- Tabs de filtrado -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#todos">
                        Todos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pendientes">
                        <span class="badge bg-warning text-dark">Pendientes</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#programados">
                        <i class="bi bi-calendar"></i> Programados
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completados">
                        <span class="badge bg-success">Completados</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- TODOS -->
                <div class="tab-pane fade show active" id="todos">
                    <?php renderTable($mantenimientos); ?>
                </div>

                <!-- PENDIENTES -->
                <div class="tab-pane fade" id="pendientes">
                    <?php renderTable($pendientes); ?>
                </div>

                <!-- PROGRAMADOS -->
                <div class="tab-pane fade" id="programados">
                    <?php renderTable($programados); ?>
                </div>

                <!-- COMPLETADOS -->
                <div class="tab-pane fade" id="completados">
                    <?php renderTable($completados); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Función auxiliar para renderizar la tabla (para evitar duplicación de código)
function renderTable($lista) {
    ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Equipo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Descripción</th>
                    <th>Última Fecha</th>
                    <th>Próxima Fecha</th>
                    <th>Frecuencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No hay mantenimientos en esta categoría
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista as $m): ?>
                        <?php
                        $estadoClass = match($m->estado) {
                            'pendiente' => 'bg-warning text-dark',
                            'en_proceso' => 'bg-info text-dark',
                            'completado' => 'bg-success',
                            'pospuesto' => 'bg-secondary',
                            'cancelado' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        
                        $tipoClass = match($m->tipo_mantenimiento) {
                            'preventivo' => 'bg-primary',
                            'correctivo' => 'bg-danger',
                            'predictivo' => 'bg-info',
                            default => 'bg-secondary'
                        };
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($m->equipo_codigo ?? 'N/A') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($m->equipo_tipo ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $tipoClass ?>">
                                    <?= ucfirst($m->tipo_mantenimiento) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $estadoClass ?>">
                                    <?= ucfirst($m->estado) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(substr($m->descripcion, 0, 50)) ?><?= strlen($m->descripcion) > 50 ? '...' : '' ?></td>
                            <td><?= date('d/m/Y', strtotime($m->fecha)) ?></td>
                            <td>
                                <?php if ($m->proxima_fecha): ?>
                                    <?= date('d/m/Y', strtotime($m->proxima_fecha)) ?>
                                    <?php
                                    $dias_restantes = (strtotime($m->proxima_fecha) - time()) / (60*60*24);
                                    if ($dias_restantes <= 7 && $dias_restantes >= 0):
                                    ?>
                                        <span class="badge bg-warning text-dark ms-1">
                                            <i class="bi bi-exclamation-triangle"></i> Próximo
                                        </span>
                                    <?php elseif ($dias_restantes < 0): ?>
                                        <span class="badge bg-danger ms-1">
                                            <i class="bi bi-exclamation-circle"></i> Vencido
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($m->frecuencia && $m->frecuencia != 'unica'): ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-arrow-repeat"></i> <?= ucfirst($m->frecuencia) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Única vez</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>mantenimientos/ver/<?= $m->id ?>" 
                                       class="btn btn-sm btn-info text-white" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($m->estado != 'completado'): ?>
                                        <a href="<?= BASE_URL ?>mantenimientos/editar/<?= $m->id ?>" 
                                           class="btn btn-sm btn-warning text-dark" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>

<?php require_once '../src/Views/layout/footer.php'; ?>
