<?php
// src/Views/equipos/lista.php
?>
<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">Listado de Equipos</h2>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a class="btn btn-primary shadow-sm" href="<?= BASE_URL ?>equipos/crear" title="Registrar nuevo equipo">
                <i class="bi bi-plus-circle me-1"></i>
                Registrar Nuevo Equipo
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table id="equipos" class="table table-striped table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th data-priority="1">Código</th>
                    <th data-priority="2">Serial</th>
                    <th data-priority="3" class="d-none d-md-table-cell">Tipo</th>
                    <th data-priority="4" class="d-none d-md-table-cell">Marca/Modelo</th>
                    <th data-priority="5" class="d-none d-md-table-cell">Departamento</th>
                    <th data-priority="6">Estado</th>
                    <th data-priority="7" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipos as $e): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($e->codigo_inventario) ?></td>
                        <td class="text-uppercase"><?= htmlspecialchars($e->numero_serie) ?></td>
                        <td class="d-none d-md-table-cell"><?= ucfirst($e->tipo) ?></td>
                        <td class="d-none d-md-table-cell">
                            <?= htmlspecialchars($e->marca . ' ' . $e->modelo) ?>
                        </td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($e->departamento_nombre ?? 'Sin Asignar') ?></td>
                        <td>
                            <?php
                            $badgeClass = 'bg-secondary';
                            if ($e->estado == 'disponible') $badgeClass = 'bg-success';
                            elseif ($e->estado == 'en_uso') $badgeClass = 'bg-primary';
                            elseif ($e->estado == 'en_reparacion') $badgeClass = 'bg-warning text-dark';
                            elseif ($e->estado == 'fuera_de_servicio') $badgeClass = 'bg-danger';
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $e->estado)) ?></span>
                        </td>
                        <td class="text-end">
                            <a title="Ver Detalles" href="<?= BASE_URL ?>equipos/ver/<?= $e->id ?>" class="text-info me-2">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <a title="Editar" href="<?= BASE_URL ?>equipos/editar/<?= $e->id ?>" class="text-warning me-2">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a title="Eliminar" href="<?= BASE_URL ?>equipos/eliminar/<?= $e->id ?>" class="text-danger" onclick="return confirm('¿Está seguro de eliminar el equipo <?= htmlspecialchars($e->codigo_inventario) ?>?')">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>