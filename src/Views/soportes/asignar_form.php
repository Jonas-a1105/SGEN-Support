<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card shadow-sm">
            
            <div class="card-header">
                <h5 class="mb-0">
                    Asignar Técnico a Soporte #<?= htmlspecialchars($soporte->id) ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                <div class="mb-3">
                    <strong>Equipo:</strong> <?= htmlspecialchars($soporte->equipo_serial ?? 'N/A') ?><br>
                    <strong>Descripción:</strong> <?= htmlspecialchars(substr($soporte->descripcion, 0, 70)) ?>...
                </div>

                <hr>

                <form action="<?= BASE_URL ?>soportes/procesar_asignacion" method="POST">
                    
                    <input type="hidden" name="soporte_id" value="<?= htmlspecialchars($soporte->id) ?>">

                    <div class="mb-3">
                        <label for="empleado_id" class="form-label">Seleccione un Técnico</label>
                        <select id="empleado_id" name="empleado_id" class="form-select" required>
                            <option value="" disabled selected>Elegir técnico...</option>
                            <?php foreach ($tecnicos as $tecnico): ?>
                                <option value="<?= $tecnico->empleado_id ?>">
                                    <?= htmlspecialchars($tecnico->nombre_completo . ' (' . $tecnico->username . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= BASE_URL ?>soportes/ver/<?= $soporte->id ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-check-fill me-1"></i>
                            Asignar y Poner "En Proceso"
                        </button>
                    </div>

                </form>
            </div> </div> </div> </div>

