<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-building me-2"></i>Seleccionar Departamento</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Seleccione un departamento para ver su inventario asignado.</p>
                
                <div class="list-group">
                    <?php foreach ($departamentos as $depto): ?>
                        <a href="<?= BASE_URL ?>inventario/departamento/<?= $depto->id ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-door-open me-2"></i>
                                <strong><?= htmlspecialchars($depto->nombre) ?></strong>
                                <br>
                                <small class="text-muted ms-4"><?= htmlspecialchars($depto->ubicacion ?? 'N/A') ?></small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 text-center">
                    <a href="<?= BASE_URL ?>inventario" class="btn btn-link text-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Inventario General
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
