<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<!-- Include Modern CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/product-detail-modern.css?v=<?= time() ?>">

<div class="pd-wrapper">
    <!-- Header -->
    <div class="pd-container pd-header">
        <div>
            <a href="<?= BASE_URL ?>inventario/ver/<?= $item->id ?>" class="pd-back-link">
                <i class="bi bi-arrow-left me-1"></i> Volver al Artículo
            </a>
            <div class="pd-title-wrapper">
                <h1 class="pd-title">
                    <i class="bi bi-clock-history text-primary"></i>
                    Historial: <?= htmlspecialchars($item->nombre) ?>
                </h1>
                <span class="pd-id-badge">ID: <?= htmlspecialchars($item->codigo ?? $item->id) ?></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pd-container">
        <div class="pd-card">
            <div class="pd-card-header">
                <h2 class="pd-card-title">Movimientos Registrados</h2>
                <div class="pd-card-subtitle">
                    Listado completo de entradas, salidas y ajustes de stock.
                </div>
            </div>

            <?php if (empty($movimientos)): ?>
                <div class="history-empty">
                    <div class="history-empty-icon">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <h3 style="font-size: 1rem; font-weight: 500; color: var(--pd-slate-900); margin-bottom: 0.25rem;">Sin movimientos</h3>
                    <p style="font-size: 0.875rem; color: var(--pd-slate-500); max-width: 250px;">
                        No se ha registrado actividad para este artículo.
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): ?>
                                <?php
                                    // Inferir tipo si está vacío
                                    $tipo = strtoupper(trim($mov->tipo_movimiento ?? ''));
                                    if (empty($tipo)) {
                                        $motivo_lower = strtolower($mov->motivo ?? '');
                                        if (strpos($motivo_lower, 'transferencia') !== false) {
                                            $tipo = 'TRANSFERENCIA';
                                        } elseif ($mov->cantidad > 0) {
                                            $tipo = 'ENTRADA';
                                        } else {
                                            $tipo = 'SALIDA';
                                        }
                                    }

                                    $isPositive = in_array($tipo, ['ENTRADA']);
                                    $isNegative = in_array($tipo, ['SALIDA', 'BAJA', 'CONSUMO']);
                                    $sign = $isPositive ? '+' : ($isNegative ? '-' : '');
                                    
                                    $badgeColor = 'bg-secondary';
                                    if ($isPositive) $badgeColor = 'bg-success';
                                    if ($isNegative) $badgeColor = 'bg-danger';
                                    if ($tipo === 'TRANSFERENCIA' || $tipo === 'AJUSTE') $badgeColor = 'bg-warning text-dark';
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($mov->fecha)) ?></td>
                                    <td><span class="badge <?= $badgeColor ?>"><?= $tipo ?></span></td>
                                    <td class="fw-bold <?= $isPositive ? 'text-success' : ($isNegative ? 'text-danger' : '') ?>">
                                        <?= $sign . abs($mov->cantidad) ?>
                                    </td>
                                    <td><?= htmlspecialchars($mov->motivo) ?></td>
                                    <td><i class="bi bi-person-circle text-muted me-1"></i> <?= htmlspecialchars($mov->username ?? 'Sistema') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination controls -->
                <?php if ($totalPages > 1 || !empty($movimientos)): ?>
                <div style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0;">
                    <!-- Left: Counts & Selector -->
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <span style="font-size: 0.875rem; color: #64748b;">
                             Mostrando <strong style="color: #0f172a;"><?= count($movimientos) ?></strong> de <strong style="color: #0f172a;"><?= $totalItems ?></strong> movimientos
                        </span>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                            <select onchange="changeItemsPerPage(this.value)" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.75rem; color: #475569; background: white; cursor: pointer;">
                                <option value="5" <?= isset($perPage) && $perPage == 5 ? 'selected' : '' ?>>5</option>
                                <option value="10" <?= (!isset($perPage) || $perPage == 10) ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= isset($perPage) && $perPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= isset($perPage) && $perPage == 50 ? 'selected' : '' ?>>50</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right: Navigation -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <a href="<?= BASE_URL ?>inventario/historial_item/<?= $item->id ?>?page=<?= max(1, $currentPage - 1) ?>&per_page=<?= $perPage ?>" 
                           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; <?= $currentPage <= 1 ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                           onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';"
                           onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </a>
                        
                        <span style="font-size: 0.875rem; color: #475569; font-weight: 500; padding: 0 0.5rem;">
                            Página <?= $currentPage ?> / <?= $totalPages ?>
                        </span>
                        
                        <a href="<?= BASE_URL ?>inventario/historial_item/<?= $item->id ?>?page=<?= min($totalPages, $currentPage + 1) ?>&per_page=<?= $perPage ?>" 
                           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; <?= $currentPage >= $totalPages ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                           onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';"
                           onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>

<script>
    function changeItemsPerPage(val) {
        // Set cookie for persistence (optional but good ux)
        document.cookie = "sgen_pagination_per_page=" + val + "; path=/; max-age=31536000"; // 1 year
        
        // Reload with new per_page param, keeping page 1 to avoid offset errors
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
