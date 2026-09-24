
<!-- Include Modern CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/product-detail-modern.css?v=<?= time() ?>">

<div class="pd-wrapper">
    <!-- Header -->
    <div class="pd-container pd-header">
        <div>
            <a href="<?= BASE_URL ?>inventario" class="pd-back-link">
                <i class="bi bi-arrow-left me-1"></i> Volver al inventario
            </a>
            <div class="pd-title-wrapper">
                <h1 class="pd-title"><?= htmlspecialchars($item->nombre ?? 'Sin Nombre') ?></h1>
                <span class="pd-id-badge">ID: <?= htmlspecialchars($item->codigo ?? 'N/A') ?></span>
            </div>
        </div>
        <div class="pd-actions">
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>inventario/editar/<?= $item->id ?>" class="pd-btn pd-btn-white">
                    <i class="bi bi-pencil me-2"></i> Editar
                </a>
                <a href="<?= BASE_URL ?>inventario/historial_item/<?= $item->id ?>" class="pd-btn pd-btn-white">
                    <i class="bi bi-clock-history me-2"></i> Ver Historial
                </a>
                <button type="button" class="pd-btn pd-btn-primary" 
                        onclick="StockAdjustmentModal.open({
                            id: <?= $item->id ?>,
                            name: '<?= addslashes($item->nombre) ?>',
                            code: '<?= addslashes($item->codigo ?? $item->id) ?>',
                            stock: <?= $item->stock_actual ?? 0 ?>,
                            unit: '<?= addslashes($item->unidad_medida ?? 'Unidades') ?>'
                        })">
                    <i class="bi bi-box-seam me-2"></i> Ajustar Stock
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Card with Border -->
    <div class="pd-main-content-card">

    <div class="pd-container pd-grid">
        
        <!-- --- COLUMNA IZQUIERDA: INFORMACIÓN PRINCIPAL (2/3) --- -->
        <div class="pd-col-left">
            
            <!-- Tarjeta de Información General -->
            <div class="pd-card">
                <div class="pd-card-header">
                    <h2 class="pd-card-title">
                        <i class="bi bi-box text-primary"></i>
                        Información General
                    </h2>
                </div>
                
                <div class="pd-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label"><i class="bi bi-tag"></i> Categoría</span>
                            <span class="detail-value"><?= htmlspecialchars($item->categoria ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><i class="bi bi-geo-alt"></i> Ubicación Actual</span>
                            <span class="detail-value"><?= htmlspecialchars($item->ubicacion ?? 'N/A') ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Marca</span>
                            <?php if(!empty($item->marca)): ?>
                                <span class="detail-value"><?= htmlspecialchars($item->marca) ?></span>
                            <?php else: ?>
                                <span class="detail-value empty-value">No especificada</span>
                            <?php endif; ?>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Modelo</span>
                            <?php if(!empty($item->modelo)): ?>
                                <span class="detail-value"><?= htmlspecialchars($item->modelo) ?></span>
                            <?php else: ?>
                                <span class="detail-value empty-value">No especificado</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="grid-column: 1 / -1; padding-top: 1rem; margin-top: 0.5rem; border-top: 1px solid var(--pd-slate-100); display: flex; justify-content: space-between; align-items: center;">
                             <div class="detail-item">
                                <span class="detail-label">Unidad de Medida</span>
                                <span class="detail-value"><?= htmlspecialchars($item->unidad_medida ?? 'Unidad') ?></span>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Compra y Garantía -->
            <div class="pd-card">
                <div class="pd-card-body">
                    <h2 class="pd-card-title" style="margin-bottom: 1.5rem;">
                       <i class="bi bi-currency-dollar text-primary"></i>
                       Detalles de Compra y Garantía
                    </h2>
                    
                    <div class="metrics-grid">
                       <div class="metric-item">
                           <div class="metric-icon blue"><i class="bi bi-calendar-event"></i></div>
                           <div class="metric-content">
                               <span class="metric-label">Fecha Compra</span>
                               <span class="metric-value"><?= !empty($item->fecha_compra) ? date('d/m/Y', strtotime($item->fecha_compra)) : "N/A" ?></span>
                           </div>
                       </div>
                       
                       <div class="metric-item">
                           <div class="metric-icon amber"><i class="bi bi-shop"></i></div>
                           <div class="metric-content">
                               <span class="metric-label">Proveedor</span>
                               <span class="metric-value"><?= htmlspecialchars($item->proveedor ?? 'N/A') ?></span>
                           </div>
                       </div>
                       
                       <div class="metric-item">
                           <div class="metric-icon emerald"><i class="bi bi-cash"></i></div>
                           <div class="metric-content">
                               <span class="metric-label">Costo</span>
                               <span class="metric-value"><?= !empty($item->valor_compra) ? '$' . number_format($item->valor_compra, 2) : '$0.00' ?></span>
                           </div>
                       </div>
                       
                       <?php
                            $garantiaVencida = false;
                            $garantiaTexto = "No aplica";
                            if (!empty($item->garantia_fin)) {
                                if (strtotime($item->garantia_fin) < time()) {
                                    $garantiaVencida = true;
                                    $garantiaTexto = date('d/m/Y', strtotime($item->garantia_fin));
                                } else {
                                    $garantiaTexto = date('d/m/Y', strtotime($item->garantia_fin));
                                }
                            }
                       ?>

                       <div class="metric-item">
                           <div class="metric-icon rose"><i class="bi bi-shield-exclamation"></i></div>
                           <div class="metric-content">
                               <span class="metric-label">Garantía</span>
                               <span class="metric-value"><?= $garantiaTexto ?></span>
                               <?php if($garantiaVencida): ?>
                                   <span class="metric-sub alert">Vencida</span>
                               <?php endif; ?>
                           </div>
                       </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Movimientos -->
            <!-- Historial de Movimientos (Moved to separate view) -->

        </div>

        <!-- --- COLUMNA DERECHA: STOCK (1/3) --- -->
        <div class="pd-col-right">
          <?php
            $stock = $item->stock_actual ?? 0;
            $minStock = $item->stock_minimo ?? 0;
            
            // Logic for status
            if ($stock <= 0) {
                $statusLabel = 'Agotado';
                $statusClass = 'stock-badge-red';
                $glowClass = 'bg-glow-red';
                $icon = 'bi-exclamation-circle';
            } elseif ($stock <= $minStock) {
                $statusLabel = 'Stock Bajo';
                $statusClass = 'stock-badge-orange';
                $glowClass = 'bg-glow-orange';
                $icon = 'bi-exclamation-triangle';
            } else {
                $statusLabel = 'Disponible';
                $statusClass = 'stock-badge-green';
                $glowClass = 'bg-glow-green';
                $icon = 'bi-check-circle';
            }
          ?>
          <div class="pd-card stock-card">
            <div class="pd-card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 style="font-size: 1rem; font-weight: 600; color: var(--pd-slate-700); margin: 0;">Estado del Stock</h3>
                    <div class="stock-status-badge <?= $statusClass ?>">
                        <i class="bi <?= $icon ?>"></i>
                        <?= $statusLabel ?>
                    </div>
                </div>

                <div class="big-stock-indicator">
                    <div class="stock-glow <?= $glowClass ?>"></div>
                    <span class="stock-number"><?= $stock ?></span>
                    <span class="stock-label">Unidades Disponibles</span>
                </div>

                <div class="d-flex flex-column gap-0 mb-4">
                    <div class="stock-meta-row">
                        <span class="stock-meta-label">Mínimo Requerido</span>
                        <span class="stock-meta-val"><?= $minStock ?> u.</span>
                    </div>
                    <div class="stock-meta-row">
                        <span class="stock-meta-label">Valor de Inventario</span>
                        <?php 
                            $valorTotal = ($item->stock_actual ?? 0) * ($item->valor_compra ?? 0);
                        ?>
                        <span class="stock-meta-val">$<?= number_format($valorTotal, 2) ?></span>
                    </div>
                </div>

                <?php if ($_SESSION['rol'] === 'admin'): ?>
                <div class="stock-actions">
                    <button type="button" class="pd-btn pd-btn-primary justify-content-center" 
                            onclick="StockAdjustmentModal.open({
                                id: <?= $item->id ?>,
                                name: '<?= addslashes($item->nombre) ?>',
                                code: '<?= addslashes($item->codigo ?? $item->id) ?>',
                                stock: <?= $item->stock_actual ?? 0 ?>,
                                unit: '<?= addslashes($item->unidad_medida ?? 'Unidades') ?>'
                            })">
                        <i class="bi bi-graph-up-arrow me-2"></i> Solicitar Reposición
                    </button>
                    <a href="<?= BASE_URL ?>inventario/distribucion/<?= $item->id ?>" class="pd-btn pd-btn-white justify-content-center">
                        Ver Distribución
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($stock <= $minStock): ?>
                <div class="stock-alert-msg">
                    <i class="bi bi-info-circle flex-shrink-0 mt-1"></i>
                    <p class="m-0">El stock está por debajo del mínimo (<?= $minStock ?>). Se recomienda iniciar una orden de compra inmediatamente.</p>
                </div>
                <?php endif; ?>

            </div>
          </div>
        </div>

    </div>

    </div><!-- End pd-main-content-card -->
</div>

<!-- Modal for Stock Adjustment (Maintained existing logic) -->


