<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<!-- Include Modern CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/product-detail-modern.css?v=<?= time() ?>">

<div class="pd-wrapper">
    <!-- Header -->
    <div class="pd-container" style="margin-bottom: 2rem;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4 mb-4">
            <div>
                <a href="<?= BASE_URL ?>inventario/ver/<?= $item->id ?>" class="pd-back-link">
                    <i class="bi bi-arrow-left me-1"></i> Volver al detalle
                </a>
                <h1 class="pd-title">
                    Distribución de Stock
                    <i class="bi bi-chevron-right text-muted" style="font-size: 1.25rem;"></i>
                    <span class="pd-subtitle-highlight"><?= htmlspecialchars($item->nombre) ?></span>
                </h1>
            </div>
            
            <!-- KPI Card -->
            <div class="pd-kpi-card">
                <div class="pd-kpi-icon">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
                <div class="pd-kpi-content">
                    <span class="pd-kpi-label">Total Global</span>
                    <span class="pd-kpi-value"><?= $item->stock_actual ?></span>
                </div>
                <div class="pd-kpi-divider"></div>
                <div class="pd-kpi-meta">
                    <span class="pd-kpi-meta-label">ID Ref</span>
                    <span class="pd-kpi-meta-value"><?= htmlspecialchars($item->codigo) ?></span>
                </div>
            </div>
        </div>

        <div class="pd-grid">
            
            <!-- --- COLUMNA IZQUIERDA: ESTADO ACTUAL (2/3) --- -->
            <div class="pd-col-left">
                <div class="pd-card">
                    <div class="pd-card-header">
                        <div>
                            <h2 class="pd-card-title">
                                <i class="bi bi-geo-alt text-primary"></i>
                                Stock por Ubicación
                            </h2>
                            <p class="pd-card-subtitle">Vista global de existencias en todas las sedes</p>
                        </div>
                        
                        <!-- Search (Client-side logic or placeholder) -->
                        <div class="pd-search-wrapper">
                            <i class="bi bi-search pd-search-icon"></i>
                            <input type="text" placeholder="Filtrar sede..." class="pd-search-input" id="searchLocation">
                        </div>
                    </div>

                    <div class="pd-table-responsive">
                        <table class="pd-table">
                            <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Disponibilidad</th>
                                    <th class="text-end">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $almacenCentral = 0;
                                // Calcular Central
                                foreach ($stockDetallado as $stock) {
                                    if ($stock->departamento === null) {
                                        $almacenCentral = $stock->cantidad;
                                    }
                                }
                                ?>
                                
                                <!-- Fila Almacén Central -->
                                <tr>
                                    <td>
                                        <div class="pd-location-cell">
                                            <div class="pd-location-icon base">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <span class="pd-location-name">Almacén Central</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="color: var(--pd-slate-500);">Almacén Principal</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="pd-stock-badge-pill <?= $almacenCentral > 0 ? 'active' : 'empty' ?>">
                                            <?= $almacenCentral ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="pd-badge-outline">Sede Central</span>
                                    </td>
                                </tr>

                                <!-- Filas Departamentos -->
                                <?php foreach ($stockDetallado as $stock): ?>
                                    <?php if ($stock->departamento !== null): ?>
                                    <tr class="searchable-row">
                                        <td>
                                            <div class="pd-location-cell">
                                                <div class="pd-location-icon branch">
                                                    <i class="bi bi-shop"></i>
                                                </div>
                                                <span class="pd-location-name"><?= htmlspecialchars($stock->departamento) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="color: var(--pd-slate-500);">Sucursal / Dpto</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="pd-stock-badge-pill <?= $stock->cantidad > 0 ? 'active' : 'empty' ?>">
                                                <?= $stock->cantidad ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <!-- Placeholder status -->
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($stockDetallado) <= 1 && $almacenCentral == 0): ?>
                    <div class="pd-card-body text-center" style="background-color: var(--pd-slate-50); border-top: 1px solid var(--pd-slate-100);">
                        <p style="color: var(--pd-slate-400); margin: 0; font-size: 0.875rem;">
                            <i class="bi bi-info-circle me-2"></i>
                            No se encontraron existencias distribuidas en otras sucursales.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- --- COLUMNA DERECHA: ACCIÓN (1/3) --- -->
            <!-- Solo visible para admins -->
            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <div class="pd-col-right">
                <div class="pd-card pd-sticky-card">
                    <div class="pd-card-body">
                        <div class="pd-form-title-wrapper">
                            <div class="pd-form-icon">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--pd-slate-800); margin: 0;">Transferir Stock</h3>
                                <p style="font-size: 0.75rem; color: var(--pd-slate-500); margin: 0;">Mover items entre almacenes</p>
                            </div>
                        </div>

                        <form action="<?= BASE_URL ?>inventario/transferir" method="POST">
                            <input type="hidden" name="item_id" value="<?= $item->id ?>">
                            
                            <!-- Origen -->
                            <div class="pd-form-group">
                                <label class="pd-form-label">Origen</label>
                                <div class="pd-input-wrapper">
                                    <div class="pd-input-icon"><i class="bi bi-building"></i></div>
                                    <select name="origen_id" class="pd-form-select">
                                        <option value="">Almacén Central (Principal)</option>
                                        <?php foreach ($departamentos as $dept): ?>
                                            <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Flow Arrow -->
                            <div class="pd-flow-arrow">
                                <i class="bi bi-arrow-down pd-flow-icon"></i>
                            </div>

                            <!-- Destino -->
                            <div class="pd-form-group">
                                <label class="pd-form-label">Destino <span class="text-danger">*</span></label>
                                <div class="pd-input-wrapper">
                                    <div class="pd-input-icon"><i class="bi bi-geo-alt"></i></div>
                                    <select name="destino_id" class="pd-form-select" required>
                                        <option value="" disabled selected>Seleccione un destino...</option>
                                        <option value="">Almacén Central</option>
                                        <?php foreach ($departamentos as $dept): ?>
                                            <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Cantidad -->
                            <div class="pd-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                                <div>
                                    <label class="pd-form-label">Cantidad</label>
                                    <input type="number" name="cantidad" class="pd-form-input number" min="1" placeholder="0" required>
                                </div>
                                <div style="display: flex; align-items: flex-end; padding-bottom: 0.625rem; font-size: 0.875rem; color: var(--pd-slate-400);">
                                    Unidades
                                </div>
                            </div>

                            <!-- Motivo -->
                            <div class="pd-form-group">
                                <label class="pd-form-label">Motivo</label>
                                <textarea name="motivo" class="pd-form-textarea" rows="3" placeholder="Ej: Reabastecimiento regular..." required></textarea>
                            </div>

                            <button type="submit" class="pd-submit-btn">
                                <i class="bi bi-check-circle"></i>
                                Confirmar Transferencia
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Simple filter script for the table
document.getElementById('searchLocation').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelectorAll('.searchable-row');
    
    rows.forEach(function(row) {
        let txtValue = row.textContent || row.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
