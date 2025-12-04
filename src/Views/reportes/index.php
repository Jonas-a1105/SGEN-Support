<?php
// src/Views/reportes/index.php
?>
<div class="centered-card">
    <div class="mb-4">
        <h2 class="h4 text-dark">Panel de Reportes</h2>
        <p class="text-muted">Seleccione el tipo de reporte que desea generar.</p>
    </div>

    <div class="row g-4">
        <!-- Reporte de Soportes -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="bi bi-ticket-detailed-fill me-2"></i>Reporte de Soportes
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>reportes/soportes" method="GET" target="_blank" id="formReporteSoportes">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="resuelto">Resuelto</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Prioridad</label>
                                <select name="prioridad" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="alta">Alta</option>
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Categoría</label>
                                <select name="categoria_id" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportarExcel()">
                                    <i class="bi bi-file-earmark-excel me-2"></i>Exportar CSV
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function exportarExcel() {
            const form = document.getElementById('formReporteSoportes');
            const originalAction = form.action;
            form.action = '<?= BASE_URL ?>reportes/soportes_excel';
            form.submit();
            form.action = originalAction;
        }
        </script>

        <!-- Reporte de Inventario -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-success">
                        <i class="bi bi-box-seam-fill fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold">Reporte de Inventario</h5>
                    <p class="card-text text-muted small mb-4">
                        Estado actual del inventario, valor total, stock por ubicación y detalles de items.
                    </p>
                    <a href="<?= BASE_URL ?>reportes/inventario" target="_blank" class="btn btn-outline-success w-100 rounded-pill">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Mantenimientos -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-tools fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold">Reporte de Mantenimientos</h5>
                    <p class="card-text text-muted small mb-4">
                        Historial de mantenimientos realizados, costos y programación de futuros mantenimientos.
                    </p>
                    <a href="<?= BASE_URL ?>reportes/mantenimientos" target="_blank" class="btn btn-outline-warning w-100 rounded-pill text-dark">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                    </a>
                </div>
            </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
