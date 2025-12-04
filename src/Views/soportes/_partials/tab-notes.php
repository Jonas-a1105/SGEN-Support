<?php
/**
 * Tab: Observaciones del Técnico
 */
?>

<?php if (in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Observaciones del Técnico</h5>
    </div>
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Documente lo realizado</h6>
        <p class="text-muted small">
            Registre aquí las acciones realizadas, piezas reemplazadas, configuraciones modificadas y cualquier otra información relevante sobre el servicio prestado.
        </p>
        
        <form action="<?= BASE_URL ?>soportes/guardar_observaciones" method="POST">
            <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
            
            <div class="mb-3">
                <textarea name="observaciones" 
                          class="form-control" 
                          rows="8" 
                          placeholder="Ejemplo: Se verificó fuente de poder defectuosa. Se reemplazó por fuente nueva. Equipo funciona correctamente. Se recomendó al usuario usar regulador de voltaje."
                          <?= ($soporte->estado == 'resuelto' && $_SESSION['rol'] != 'admin') ? 'readonly' : ''?>><?= htmlspecialchars($soporte->observaciones ?? '') ?></textarea>
                <div class="form-text">
                    <?php if ($soporte->estado == 'resuelto' && $_SESSION['rol'] == 'admin'): ?>
                        <i class="bi bi-info-circle"></i> Ticket cerrado. Solo administradores pueden editar.
                    <?php elseif ($soporte->estado == 'resuelto'): ?>
                        <i class="bi bi-lock"></i> Ticket cerrado. No se pueden modificar las observaciones.
                    <?php else: ?>
                        <i class="bi bi-info-circle"></i> Las observaciones se pueden actualizar hasta que el ticket se marque como resuelto.
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($soporte->estado != 'resuelto' || $_SESSION['rol'] == 'admin'): ?>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Guardar Observaciones
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php elseif (!empty($soporte->observaciones)): ?>
    <!-- Vista solo lectura para otros roles -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Observaciones del Técnico</h5>
        </div>
        <div class="card-body">
            <div class="bg-light p-3 rounded border">
                <p class="mb-0" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($soporte->observaciones)) ?></p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        El técnico aún no ha registrado observaciones para este ticket.
    </div>
<?php endif; ?>
