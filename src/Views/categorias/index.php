<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-tags me-2"></i>Gestión de Categorías</h1>
    <a href="<?= BASE_URL ?>categorias/crear" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nueva Categoría
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Icono</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Color</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay categorías registradas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="text-center">
                                    <i class="bi <?= $cat->icono ?> fs-4" style="color: <?= $cat->color ?>"></i>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($cat->nombre) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($cat->descripcion) ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= $cat->color ?>">
                                        <?= $cat->color ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>categorias/editar/<?= $cat->id ?>" 
                                       class="btn btn-sm btn-outline-primary me-1" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>categorias/eliminar/<?= $cat->id ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('¿Estás seguro de eliminar esta categoría?')"
                                       title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
