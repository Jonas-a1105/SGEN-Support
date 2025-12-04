<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0"><i class="bi bi-building me-2"></i><?= htmlspecialchars($titulo) ?></h2>
            <p class="text-muted">Ubicación: <?= htmlspecialchars($departamento->ubicacion ?? 'N/A') ?></p>
        </div>
        <a href="<?= BASE_URL ?>inventario" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> Volver al Inventario General
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($items)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>
                Este departamento no tiene stock asignado actualmente.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Ítem</th>
                            <th>Categoría</th>
                            <th class="text-center">Stock Disponible</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($item->codigo) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($item->nombre) ?></td>
                                <td><?= htmlspecialchars($item->categoria) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6">
                                        <?= $item->stock_departamento ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>inventario/distribucion/<?= $item->id ?>" class="btn btn-sm btn-outline-info" title="Ver Distribución Global">
                                        <i class="bi bi-geo-alt"></i>
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
