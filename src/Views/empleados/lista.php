<?php
// src/Views/empleados/lista.php
?>
<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">Listado de Empleados</h2>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a class="btn btn-primary shadow-sm" href="<?= BASE_URL ?>empleados/crear" title="Registrar Nuevo Empleado">
                <i class="bi bi-person-plus-fill me-1"></i>
                Registrar Nuevo Empleado
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table id="empleados-table" class="table table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                    <th data-priority="1">Nombre Completo</th>
                    <th data-priority="2">Cédula</th>
                    <th data-priority="3" class="d-none d-md-table-cell">Email</th>
                    <th data-priority="4" class="d-none d-lg-table-cell">Departamento</th>
                    <th data-priority="5" class="d-none d-md-table-cell">Usuario Vinculado</th>
                    <th data-priority="6" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $e): ?>
                    <tr>
                        <td class="text-uppercase"><?= htmlspecialchars("{$e->nombre} {$e->apellido}") ?></td>
                        <td><?= htmlspecialchars($e->cedula) ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($e->email) ?></td>
                        <td class="d-none d-lg-table-cell">
                            <?php if ($e->departamento_nombre): ?>
                                <span class="badge bg-info"><?= htmlspecialchars($e->departamento_nombre) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php if ($e->usuario_username): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= htmlspecialchars($e->usuario_username) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">NO VINCULADO</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <a title="Editar" href="<?= BASE_URL ?>empleados/editar/<?= $e->id ?>" class="text-warning me-2">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a title="Eliminar" href="<?= BASE_URL ?>empleados/eliminar/<?= $e->id ?>" class="text-danger" onclick="return confirm('¿Está seguro de eliminar a: <?= htmlspecialchars($e->nombre) ?>?')">
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