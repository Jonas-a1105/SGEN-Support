<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Historial de Movimientos de Inventario</h1>
            <a href="<?= BASE_URL ?>inventario" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card border-0 shadow-sm glass-opaque">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="tablaHistorial">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Fecha</th>
                                <th>Ítem</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movimientos)): ?>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small">
                                            <?= date('d/m/Y H:i', strtotime($mov->fecha)) ?>
                                        </td>
                                        <td class="fw-medium"><?= htmlspecialchars($mov->item_nombre) ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($mov->item_codigo) ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match($mov->tipo_movimiento) {
                                                'ENTRADA' => 'bg-success',
                                                'SALIDA' => 'bg-danger',
                                                'TRANSFERENCIA' => 'bg-info',
                                                'CONSUMO' => 'bg-warning text-dark',
                                                'BAJA' => 'bg-dark',
                                                'AJUSTE' => 'bg-primary',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?> bg-opacity-75">
                                                <?= $mov->tipo_movimiento ?>
                                            </span>
                                        </td>
                                            <?php
                                            $isPositive = in_array($mov->tipo_movimiento, ['ENTRADA']);
                                            $isNegative = in_array($mov->tipo_movimiento, ['SALIDA', 'BAJA', 'CONSUMO']);
                                            $colorClass = $isPositive ? 'text-success' : ($isNegative ? 'text-danger' : 'text-secondary');
                                            $sign = $isPositive ? '+' : ($isNegative ? '-' : '');
                                            ?>
                                            <td class="fw-bold <?= $colorClass ?>">
                                                <?= $sign ?><?= $mov->cantidad ?>
                                            </td>
                                        <td class="text-muted small"><?= htmlspecialchars($mov->motivo) ?></td>
                                        <td class="text-muted small">
                                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($mov->username ?? 'Sistema') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No hay movimientos registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('turbo:load', function() {
        $('#tablaHistorial').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json',
            },
            order: [[0, 'desc']]
        });
    });
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
