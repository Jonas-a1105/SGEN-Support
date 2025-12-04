<?php
/**
 * Tab: Archivos Adjuntos
 */
?>

<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-paperclip me-2"></i>Archivos Adjuntos</h5>
        <?php if (in_array($soporte->estado, ['pendiente', 'en_proceso'])): ?>
            <button class="btn btn-sm btn-light" onclick="document.getElementById('inputArchivo').click()">
                <i class="bi bi-plus-lg me-1"></i> Adjuntar
            </button>
            <input type="file" id="inputArchivo" style="display: none;" onchange="subirArchivo(this)">
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div id="listaArchivos" class="list-group list-group-flush">
            <?php if (empty($archivos)): ?>
                <p class="text-muted text-center my-3" id="noArchivosMsg">No hay archivos adjuntos.</p>
            <?php else: ?>
                <?php foreach ($archivos as $archivo): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <?php
                                $icono = 'bi-file-earmark';
                                if (strpos($archivo->tipo_mime, 'image') !== false) $icono = 'bi-file-earmark-image text-primary';
                                elseif (strpos($archivo->tipo_mime, 'pdf') !== false) $icono = 'bi-file-earmark-pdf text-danger';
                                elseif (strpos($archivo->tipo_mime, 'spreadsheet') !== false || strpos($archivo->tipo_mime, 'excel') !== false) $icono = 'bi-file-earmark-excel text-success';
                                elseif (strpos($archivo->tipo_mime, 'word') !== false) $icono = 'bi-file-earmark-word text-primary';
                            ?>
                            <i class="bi <?= $icono ?> fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-0">
                                    <a href="<?= BASE_URL ?>soportes/descargar_archivo/<?= $archivo->id ?>" target="_blank" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($archivo->nombre_original) ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?= number_format($archivo->tamaño_bytes / 1024, 2) ?> KB - 
                                    <?= date('d/m/Y H:i', strtotime($archivo->fecha_subida)) ?> - 
                                    Por: <?= htmlspecialchars($archivo->nombre_usuario ?? 'Desconocido') ?>
                                </small>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>soportes/descargar_archivo/<?= $archivo->id ?>" class="btn btn-sm btn-outline-primary" title="Descargar">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Progress Bar -->
        <div class="progress mt-3 d-none" id="uploadProgressContainer">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="uploadProgressBar"></div>
        </div>
    </div>
</div>
