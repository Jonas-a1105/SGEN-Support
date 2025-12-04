<?php
// src/Views/inventario/distribucion.php
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Distribución de Stock: <?= htmlspecialchars($item->nombre) ?></h1>
        <a href="<?= BASE_URL ?>inventario" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Inventario
        </a>
    </div>

    <div class="row">
        <!-- Tarjeta de Información del Ítem -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-left-primary py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Código: <?= htmlspecialchars($item->codigo) ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Total Global: <?= $item->stock_actual ?></div>
                            <p class="text-muted mt-2 mb-0 small"><?= htmlspecialchars($item->descripcion) ?></p>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fa-2x text-gray-300" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Transferencia -->
            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transferir Stock</h6>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>inventario/transferir" method="POST">
                        <input type="hidden" name="item_id" value="<?= $item->id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Origen</label>
                            <select name="origen_id" class="form-select">
                                <option value="">Almacén Central</option>
                                <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Dejar en blanco = Almacén Central</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destino <span class="text-danger">*</span></label>
                            <select name="destino_id" class="form-select" required>
                                <option value="" selected disabled>Seleccione un destino...</option>
                                <option value="">Almacén Central</option>
                                <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control" rows="2" required placeholder="Ej. Reabastecimiento mensual"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-left-right"></i> Realizar Transferencia
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de Distribución -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock por Ubicación</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th>Cantidad Disponible</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalDistribuido = 0;
                                $almacenCentral = 0;
                                // Buscar Almacén Central (NULL) en los resultados
                                foreach ($stockDetallado as $stock) {
                                    if ($stock->departamento === null) {
                                        $almacenCentral = $stock->cantidad;
                                    } else {
                                        $totalDistribuido += $stock->cantidad;
                                    }
                                }
                                ?>
                                
                                <!-- Fila Almacén Central -->
                                <tr class="table-active font-weight-bold">
                                    <td><i class="bi bi-building me-2"></i> Almacén Central</td>
                                    <td><?= $almacenCentral ?></td>
                                    <td><span class="badge bg-success">Base</span></td>
                                </tr>

                                <!-- Filas Departamentos -->
                                <?php foreach ($stockDetallado as $stock): ?>
                                    <?php if ($stock->departamento !== null): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stock->departamento) ?></td>
                                        <td><?= $stock->cantidad ?></td>
                                        <td>
                                            <?php if($stock->cantidad > 0): ?>
                                                <span class="badge bg-info">En Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Agotado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if (count($stockDetallado) <= 1 && $almacenCentral == 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay stock distribuido aún.</td>
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
