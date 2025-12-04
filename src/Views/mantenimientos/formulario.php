<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-tools"></i> 
                            <?= isset($mantenimiento) && $mantenimiento ? 'Editar Mantenimiento' : 'Programar Nuevo Mantenimiento' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= BASE_URL ?>mantenimientos/guardar" method="POST">
                            <?php if (isset($mantenimiento) && $mantenimiento): ?>
                                <input type="hidden" name="id" value="<?= $mantenimiento->id ?>">
                            <?php endif; ?>

                            <div class="row g-3">
                                <!-- Equipo -->
                                <div class="col-md-6">
                                    <label class="form-label">Equipo <span class="text-danger">*</span></label>
                                    <select name="equipo_id" class="form-select" required>
                                        <option value="">Seleccionar equipo...</option>
                                        <?php foreach ($equipos as $equipo): ?>
                                            <option value="<?= $equipo->id ?>" 
                                                <?= (isset($equipo_preseleccionado) && $equipo_preseleccionado && $equipo_preseleccionado->id == $equipo->id) || 
                                                    (isset($mantenimiento) && $mantenimiento && $mantenimiento->equipo_id == $equipo->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($equipo->codigo_inventario ?? $equipo->numero_serie) ?> - 
                                                <?= htmlspecialchars($equipo->tipo) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tipo de Mantenimiento -->
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Mantenimiento <span class="text-danger">*</span></label>
                                    <select name="tipo_mantenimiento" class="form-select" required>
                                        <option value="preventivo" <?= isset($mantenimiento) && $mantenimiento->tipo_mantenimiento == 'preventivo' ? 'selected' : '' ?>>
                                            🛡️ Preventivo
                                        </option>
                                        <option value="correctivo" <?= isset($mantenimiento) && $mantenimiento->tipo_mantenimiento == 'correctivo' ? 'selected' : '' ?>>
                                            🔧 Correctivo
                                        </option>
                                        <option value="predictivo" <?= isset($mantenimiento) && $mantenimiento->tipo_mantenimiento == 'predictivo' ? 'selected' : '' ?>>
                                            📊 Predictivo
                                        </option>
                                    </select>
                                    <div class="form-text">
                                        Preventivo: Programado regularmente | Correctivo: Reparación | Predictivo: Basado en análisis
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="col-md-6">
                                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select name="estado" class="form-select" required>
                                        <option value="pendiente" <?= (!isset($mantenimiento) || $mantenimiento->estado == 'pendiente') ? 'selected' : '' ?>>
                                            ⏳ Pendiente
                                        </option>
                                        <option value="en_proceso" <?= isset($mantenimiento) && $mantenimiento->estado == 'en_proceso' ? 'selected' : '' ?>>
                                            🔄 En Proceso
                                        </option>
                                        <option value="completado" <?= isset($mantenimiento) && $mantenimiento->estado == 'completado' ? 'selected' : '' ?>>
                                            ✅ Completado
                                        </option>
                                        <option value="pospuesto" <?= isset($mantenimiento) && $mantenimiento->estado == 'pospuesto' ? 'selected' : '' ?>>
                                            ⏸️ Pospuesto
                                        </option>
                                        <option value="cancelado" <?= isset($mantenimiento) && $mantenimiento->estado == 'cancelado' ? 'selected' : '' ?>>
                                            ❌ Cancelado
                                        </option>
                                    </select>
                                </div>

                                <!-- Frecuencia -->
                                <div class="col-md-6">
                                    <label class="form-label">Frecuencia</label>
                                    <select name="frecuencia" class="form-select" id="frecuencia">
                                        <option value="unica" <?= (!isset($mantenimiento) || $mantenimiento->frecuencia == 'unica') ? 'selected' : '' ?>>
                                            Una sola vez
                                        </option>
                                        <option value="mensual" <?= isset($mantenimiento) && $mantenimiento->frecuencia == 'mensual' ? 'selected' : '' ?>>
                                            🔁 Mensual
                                        </option>
                                        <option value="trimestral" <?= isset($mantenimiento) && $mantenimiento->frecuencia == 'trimestral' ? 'selected' : '' ?>>
                                            🔁 Trimestral (cada 3 meses)
                                        </option>
                                        <option value="semestral" <?= isset($mantenimiento) && $mantenimiento->frecuencia == 'semestral' ? 'selected' : '' ?>>
                                            🔁 Semestral (cada 6 meses)
                                        </option>
                                        <option value="anual" <?= isset($mantenimiento) && $mantenimiento->frecuencia == 'anual' ? 'selected' : '' ?>>
                                            🔁 Anual
                                        </option>
                                    </select>
                                    <div class="form-text">
                                        Si es recurrente, se programará automáticamente la próxima fecha al completar
                                    </div>
                                </div>

                                <!-- Fecha de Realización -->
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Realización <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           name="fecha" 
                                           class="form-control" 
                                           value="<?= isset($mantenimiento) ? date('Y-m-d\TH:i', strtotime($mantenimiento->fecha)) : date('Y-m-d\TH:i') ?>"
                                           required>
                                </div>

                                <!-- Próxima Fecha (solo si es recurrente) -->
                                <div class="col-md-6" id="proxima-fecha-group">
                                    <label class="form-label">Próxima Fecha Programada</label>
                                    <input type="date" 
                                           name="proxima_fecha" 
                                           class="form-control"
                                           value="<?= isset($mantenimiento) && $mantenimiento->proxima_fecha ? date('Y-m-d', strtotime($mantenimiento->proxima_fecha)) : '' ?>">
                                    <div class="form-text">
                                        Para mantenimientos recurrentes
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="col-12">
                                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                                    <textarea name="descripcion" 
                                              class="form-control" 
                                              rows="4" 
                                              placeholder="Detalle del mantenimiento a realizar..."
                                              required><?= isset($mantenimiento) ? htmlspecialchars($mantenimiento->descripcion) : '' ?></textarea>
                                </div>

                                <!-- Costo -->
                                <div class="col-md-6">
                                    <label class="form-label">Costo Estimado/Real ($)</label>
                                    <input type="number" 
                                           name="costo" 
                                           class="form-control" 
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00"
                                           value="<?= isset($mantenimiento) ? $mantenimiento->costo : '' ?>">
                                </div>

                                <!-- Realizado Por -->
                                <div class="col-md-6">
                                    <label class="form-label">Realizado Por</label>
                                    <input type="text" 
                                           name="realizado_por" 
                                           class="form-control" 
                                           placeholder="Nombre del técnico o empresa"
                                           value="<?= isset($mantenimiento) ? htmlspecialchars($mantenimiento->realizado_por) : ($_SESSION['username'] ?? '') ?>">
                                </div>

                                <!-- Observaciones -->
                                <div class="col-12">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" 
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="Notas adicionales, hallazgos, recomendaciones..."><?= isset($mantenimiento) ? htmlspecialchars($mantenimiento->observaciones) : '' ?></textarea>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="mt-4 d-flex gap-2 justify-content-end">
                                <a href="<?= BASE_URL ?>mantenimientos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar Mantenimiento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campo de próxima fecha según frecuencia
document.getElementById('frecuencia').addEventListener('change', function() {
    const proximaFechaGroup = document.getElementById('proxima-fecha-group');
    if (this.value === 'unica') {
        proximaFechaGroup.style.display = 'none';
    } else {
        proximaFechaGroup.style.display = 'block';
    }
});

// Ejecutar al cargar
window.addEventListener('DOMContentLoaded', function() {
    const frecuencia = document.getElementById('frecuencia');
    if (frecuencia.value === 'unica') {
        document.getElementById('proxima-fecha-group').style.display = 'none';
    }
});
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
