<div class="row justify-content-center py-3">
    <div class="col-12 col-lg-11 col-xl-10">
        
        <!-- Card Principal - Más ancho -->
        <div class="card shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            
            <!-- Header con gradiente -->
            <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                            <i class="bi bi-person-plus-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Asignar Técnico a Soporte #<?= htmlspecialchars($soporte->id) ?></h5>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="bi bi-clock me-1"></i>Pendiente
                    </span>
                </div>
            </div>
            
            <div class="card-body p-4 p-lg-5">
                
                <!-- Fila superior: Info del Ticket -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted d-block mb-2"><i class="bi bi-pc-display me-1"></i>Equipo</small>
                            <span class="fw-bold fs-5"><?= htmlspecialchars($soporte->equipo_serial ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted d-block mb-2"><i class="bi bi-chat-left-text me-1"></i>Descripción</small>
                            <p class="mb-0"><?= htmlspecialchars(substr($soporte->descripcion, 0, 200)) ?><?= strlen($soporte->descripcion) > 200 ? '...' : '' ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Fila inferior: Selector y Botones -->
                <form action="<?= BASE_URL ?>soportes/procesar_asignacion" method="POST">
                    <input type="hidden" name="soporte_id" value="<?= htmlspecialchars($soporte->id) ?>">
                    
                    <div class="row g-4 align-items-end">
                        <div class="col-md-8">
                            <label for="empleado_id" class="form-label fw-semibold mb-2">
                                <i class="bi bi-person-badge me-1"></i>Seleccionar Técnico
                            </label>
                            <select id="empleado_id" name="empleado_id" class="form-select" required 
                                    style="border-radius: 8px; border: 1px solid #e2e8f0;">
                                <option value="" disabled selected>-- Elegir técnico disponible --</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?= $tecnico->empleado_id ?>">
                                        <?= htmlspecialchars($tecnico->nombre_completo . ' (' . $tecnico->username . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                El técnico seleccionado recibirá una notificación y el ticket pasará a "En Proceso"
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="<?= BASE_URL ?>soportes/ver/<?= $soporte->id ?>" 
                                   class="btn btn-outline-secondary" style="border-radius: 8px;">
                                    <i class="bi bi-arrow-left me-1"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-success px-3" 
                                        style="border-radius: 8px; white-space: nowrap;">
                                    <i class="bi bi-check-lg me-1"></i>Asignar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
            </div>
            
        </div>
        
    </div>
</div>
