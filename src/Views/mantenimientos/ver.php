<?php
/**
 * View: mantenimientos/ver.php
 * Premium maintenance detail view – each logical section in its own card.
 */
?>
<div class="container mt-5">
    <h3 class="text-primary mb-4" style="font-weight:600;">
        <?= htmlspecialchars($titulo ?? 'Detalle de Mantenimiento') ?>
    </h3>

    <?php if (isset($mantenimiento) && $mantenimiento): ?>
        <!-- Identifiers card -->
        <div class="card glass-opaque shadow-sm mb-4">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>ID</strong><span><?= htmlspecialchars($mantenimiento->id) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Equipo</strong><span><?= htmlspecialchars($mantenimiento->equipo_id) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Tipo</strong><span><?= htmlspecialchars($mantenimiento->tipo_mantenimiento) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Estado</strong><span><?= htmlspecialchars($mantenimiento->estado) ?></span></li>
                </ul>
            </div>
        </div>

        <!-- Dates & Frequency card -->
        <div class="card glass-opaque shadow-sm mb-4">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Fecha</strong><span><?= htmlspecialchars($mantenimiento->fecha) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Próxima Fecha</strong><span><?= htmlspecialchars($mantenimiento->proxima_fecha ?? 'N/A') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Frecuencia</strong><span><?= htmlspecialchars($mantenimiento->frecuencia) ?></span></li>
                </ul>
            </div>
        </div>

        <!-- Description and Observaciones side by side -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card glass-opaque shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Descripción</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(htmlspecialchars($mantenimiento->descripcion)) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card glass-opaque shadow-sm h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(htmlspecialchars($mantenimiento->observaciones)) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="<?= BASE_URL ?>mantenimientos" class="btn btn-outline-secondary btn-sm">Volver a la lista</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger" role="alert">Mantenimiento no encontrado.</div>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>mantenimientos" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>
    <?php endif; ?>
</div>
