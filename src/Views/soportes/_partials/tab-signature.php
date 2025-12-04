<?php
/**
 * Tab: Firma de Conformidad
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Firma de Conformidad</h5>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($soporte->firma_usuario)): ?>
                    <div class="mb-4">
                        <img src="<?= $soporte->firma_usuario ?>" class="img-fluid border rounded" alt="Firma del Usuario" style="max-width: 400px;">
                    </div>
                    <p class="text-success fw-bold fs-5">
                        <i class="bi bi-check-circle-fill me-2"></i> Firmado digitalmente
                    </p>
                    <small class="text-muted">El usuario ha confirmado la recepción del servicio</small>
                <?php else: ?>
                    <div class="mb-4 text-muted py-5">
                        <i class="bi bi-pen display-1"></i>
                        <p class="mt-3 fs-5">El usuario aún no ha firmado la conformidad del servicio.</p>
                    </div>
                    <?php if (in_array($soporte->estado, ['resuelto', 'en_proceso'])): ?>
                        <button type="button" class="btn btn-dark btn-lg" onclick="abrirModalFirma()">
                            <i class="bi bi-pencil-square me-2"></i> Firmar Conformidad
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <a href="<?= BASE_URL ?>soportes/pdf/<?= $soporte->id ?>" target="_blank" class="btn btn-danger btn-lg">
                    <i class="bi bi-file-pdf me-2"></i> Descargar PDF
                </a>
            </div>
        </div>
    </div>
</div>
