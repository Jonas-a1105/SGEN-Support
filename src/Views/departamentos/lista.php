<?php
// src/Views/departamentos/lista.php
?>
<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">Listado de Departamentos</h2>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a class="btn btn-primary shadow-sm" href="<?= BASE_URL ?>departamentos/crear" title="Agregar Nuevo Departamento">
                <i class="bi bi-plus-circle me-1"></i>
                Agregar Nuevo Departamento
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table id="departamentos-table" class="table table-striped table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th data-priority="0">ID</th>
                    <th data-priority="1">Nombre</th>
                    <th data-priority="2" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departamentos as $d): ?>
                    <tr>
                        <td><?= $d->id ?></td>
                        <td class="text-uppercase"><?= htmlspecialchars($d->nombre) ?></td>
                        <td class="text-end">
                            <a title="Ver Detalles" href="<?= BASE_URL ?>departamentos/ver/<?= $d->id ?>" class="text-info me-2">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <a title="Editar" href="<?= BASE_URL ?>departamentos/editar/<?= $d->id ?>" class="text-warning me-2">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a title="Eliminar" href="<?= BASE_URL ?>departamentos/eliminar/<?= $d->id ?>" class="text-danger"
                                   onclick="return confirm('¿Está seguro de eliminar el departamento: <?= htmlspecialchars($d->nombre) ?>?')">
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