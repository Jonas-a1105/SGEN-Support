<!-- Modal Firma -->
<div class="modal fade" id="modalFirma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-pen me-2"></i>Firma de Conformidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Por favor, firme en el recuadro de abajo para confirmar la recepciÃ³n del servicio.</p>
                <div class="border rounded bg-light d-flex justify-content-center">
                    <canvas id="signature-pad" width="400" height="200" style="touch-action: none;"></canvas>
                </div>
                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFirma()">
                        <i class="bi bi-eraser me-1"></i> Limpiar
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= BASE_URL ?>soportes/guardar_firma" method="POST" id="formFirma">
                    <input type="hidden" name="ticket_id" value="<?= $soporte->id ?>">
                    <input type="hidden" name="firma_base64" id="firma_base64">
                    <button type="button" class="btn btn-primary" onclick="guardarFirma()">
                        <i class="bi bi-check-circle me-1"></i> Guardar Firma
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


    </div>
</div>

<!-- Modal para Editar Fecha de Cierre -->
<div class="modal fade" id="modalEditarFechaCierre" tabindex="-1" aria-labelledby="modalEditarFechaCierreLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarFechaCierreLabel">
                    <i class="bi bi-calendar-check me-2"></i>Editar Fecha de Cierre
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>soportes/actualizar_fecha_cierre" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Esta acciÃ³n actualizarÃ¡ la fecha de cierre del ticket. Use con precauciÃ³n.
                    </div>
                    
                    <div class="mb-3">
                        <label for="nueva_fecha_cierre" class="form-label">
                            <strong>Nueva Fecha de Cierre</strong>
                        </label>
                        <input type="datetime-local" 
                               class="form-control" 
                               id="nueva_fecha_cierre" 
                               name="nueva_fecha_cierre" 
                               value="<?= $soporte->fecha_cierre ? date('Y-m-d\TH:i', strtotime($soporte->fecha_cierre)) : '' ?>" 
                               required>
                        <div class="form-text">
                            Seleccione la nueva fecha y hora de cierre del ticket.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

