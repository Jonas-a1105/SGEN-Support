<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Detalle del Artículo</h1>
            <a href="<?= BASE_URL ?>inventario" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="row g-4">
            <!-- Main Info Card -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100 glass-opaque">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary fw-bold">
                            <i class="bi bi-box-seam me-2"></i>Información General
                        </h5>
                        <span class="badge bg-secondary fs-6"><?= htmlspecialchars($item->codigo ?? '') ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h2 class="fw-bold mb-1"><?= htmlspecialchars($item->nombre ?? '') ?></h2>
                                <p class="text-muted mb-0"><?= htmlspecialchars($item->descripcion ?? 'Sin descripción') ?></p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light-subtle border">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Categoría</small>
                                    <span class="fs-5 text-dark"><?= htmlspecialchars($item->categoria ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light-subtle border">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Ubicación Actual</small>
                                    <span class="fs-5 text-dark"><?= htmlspecialchars($item->ubicacion ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded bg-light-subtle border">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Marca</small>
                                    <span class="fw-medium"><?= htmlspecialchars($item->marca ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded bg-light-subtle border">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Modelo</small>
                                    <span class="fw-medium"><?= htmlspecialchars($item->modelo ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded bg-light-subtle border">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Unidad</small>
                                    <span class="fw-medium"><?= htmlspecialchars($item->unidad_medida ?? 'Unidad') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Status Card -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 glass-opaque">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-success fw-bold">
                            <i class="bi bi-graph-up-arrow me-2"></i>Estado del Stock
                        </h5>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <div class="mb-4">
                            <span class="display-4 fw-bold <?= ($item->stock_actual <= $item->stock_minimo) ? 'text-danger' : 'text-success' ?>">
                                <?= $item->stock_actual ?? 0 ?>
                            </span>
                            <span class="text-muted d-block">Unidades Disponibles</span>
                        </div>
                        
                        <div class="d-flex justify-content-between px-4 mb-3">
                            <div class="text-start">
                                <small class="text-muted d-block">Mínimo Requerido</small>
                                <span class="fw-bold"><?= $item->stock_minimo ?? 0 ?></span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Estado</small>
                                <?php if(($item->stock_actual ?? 0) <= ($item->stock_minimo ?? 0)): ?>
                                    <span class="badge bg-danger">Stock Bajo</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Óptimo</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-auto">
                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMovimiento<?= $item->id ?>">
                                <i class="bi bi-arrow-left-right me-2"></i>Ajustar Stock
                            </button>
                            <a href="<?= BASE_URL ?>inventario/distribucion/<?= $item->id ?>" class="btn btn-outline-info">
                                <i class="bi bi-diagram-3 me-2"></i>Ver Distribución
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Details Card -->
            <div class="col-md-12">
                <div class="card border-0 shadow-sm glass-opaque">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-info fw-bold">
                            <i class="bi bi-receipt me-2"></i>Detalles de Compra y Garantía
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-calendar-event text-info fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Fecha de Compra</small>
                                        <span class="fw-medium"><?= !empty($item->fecha_compra) ? date('d/m/Y', strtotime($item->fecha_compra)) : 'No registrada' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-shop text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Proveedor</small>
                                        <span class="fw-medium"><?= htmlspecialchars($item->proveedor ?? 'No registrado') ?></span>
                                        <?php if(!empty($item->proveedor_rif)): ?>
                                            <br><small class="text-muted text-xs"><?= htmlspecialchars($item->proveedor_rif) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-currency-dollar text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Valor de Compra</small>
                                        <span class="fw-medium"><?= !empty($item->valor_compra) ? '$' . number_format($item->valor_compra, 2) : 'No registrado' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-shield-check text-danger fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Garantía Vence</small>
                                        <span class="fw-medium <?= (!empty($item->garantia_fin) && strtotime($item->garantia_fin) < time()) ? 'text-danger' : '' ?>">
                                            <?= !empty($item->garantia_fin) ? date('d/m/Y', strtotime($item->garantia_fin)) : 'No aplica' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Movimientos -->
            <div class="col-md-12">
                <div class="card border-0 shadow-sm glass-opaque">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-secondary fw-bold">
                            <i class="bi bi-clock-history me-2"></i>Historial de Movimientos
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Fecha</th>
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
                                                <td>
                                                    <?php
                                                    $tipo = strtoupper(trim($mov->tipo_movimiento ?? ''));
                                                    
                                                    // Si tipo_movimiento está vacío, inferir del contexto
                                                    if (empty($tipo)) {
                                                        $motivo_lower = strtolower($mov->motivo ?? '');
                                                        if (strpos($motivo_lower, 'transferencia') !== false) {
                                                            $tipo = 'TRANSFERENCIA';
                                                        } elseif (strpos($motivo_lower, 'abastecimiento') !== false || strpos($motivo_lower, 'stock inicial') !== false) {
                                                            $tipo = 'ENTRADA';
                                                        } elseif ($mov->cantidad > 0) {
                                                            $tipo = 'ENTRADA';
                                                        } else {
                                                            $tipo = 'SALIDA';
                                                        }
                                                    }
                                                    
                                                    if ($tipo === 'ENTRADA') {
                                                        $badgeClass = 'bg-success';
                                                    } elseif ($tipo === 'SALIDA') {
                                                        $badgeClass = 'bg-danger';
                                                    } elseif ($tipo === 'TRANSFERENCIA') {
                                                        $badgeClass = 'bg-warning text-dark';
                                                    } elseif ($tipo === 'CONSUMO') {
                                                        $badgeClass = 'bg-warning text-dark';
                                                    } elseif ($tipo === 'BAJA') {
                                                        $badgeClass = 'bg-dark';
                                                    } elseif ($tipo === 'AJUSTE') {
                                                        $badgeClass = 'bg-primary';
                                                    } else {
                                                        $badgeClass = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>">
                                                        <?= htmlspecialchars($tipo) ?>
                                                    </span>
                                                </td>
                                                    <?php
                                                    $isPositive = in_array($tipo, ['ENTRADA']);
                                                    $isNegative = in_array($tipo, ['SALIDA', 'BAJA', 'CONSUMO']);
                                                    $colorClass = $isPositive ? 'text-success' : ($isNegative ? 'text-danger' : 'text-warning');
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
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No hay movimientos registrados para este ítem.
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
    </div>
</div>

<!-- Modal for Stock Adjustment (Reused logic) -->
<div class="modal fade" id="modalMovimiento<?= $item->id ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>inventario/movimiento" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Stock: <?= $item->nombre ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" value="<?= $item->id ?>">
                    <div class="mb-3">
                        <label>Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="ENTRADA">Entrada (Compra/Devolución)</option>
                            <option value="SALIDA">Salida (Asignación/Uso)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label>Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej: Entrega a RRHH" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../src/Views/layout/footer.php'; ?>
