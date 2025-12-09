<?php
/**
 * Tab: Archivos Adjuntos - V2 Ultra Moderna
 * Dropzone y galería de archivos
 */

use App\Helpers\ViewHelper;

// Obtener archivos del ticket
$archivos = $archivos ?? [];
?>

<div>
    <div class="td-card-header">
        <div class="td-card-header-left">
            <div class="td-card-icon slate">
                <i class="bi bi-paperclip"></i>
            </div>
            <h3 class="td-card-title">Evidencias Adjuntas</h3>
        </div>
        
        <?php if ($soporte->estado !== 'cerrado'): ?>
        <button type="button" class="btn btn-dark btn-sm d-flex align-items-center gap-2" 
                data-bs-toggle="modal" data-bs-target="#modalSubirArchivo"
                style="border-radius: 8px; font-weight: 700; font-size: 0.75rem; padding: 0.5rem 1rem;">
            <i class="bi bi-plus"></i> Subir Archivo
        </button>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($archivos)): ?>
    <!-- Grid de archivos -->
    <div class="td-files-grid">
        <?php foreach ($archivos as $archivo): ?>
        <?php
            $ext = strtolower(pathinfo($archivo->nombre_original ?? $archivo->nombre, PATHINFO_EXTENSION));
            $iconClass = 'bi-file-earmark';
            $iconType = '';
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $iconClass = 'bi-image';
                $iconType = 'image';
            } elseif ($ext === 'pdf') {
                $iconClass = 'bi-file-pdf';
                $iconType = 'pdf';
            } elseif (in_array($ext, ['doc', 'docx'])) {
                $iconClass = 'bi-file-word';
                $iconType = 'doc';
            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                $iconClass = 'bi-file-excel';
                $iconType = 'doc';
            }
            
            $fileSize = $archivo->tamano ?? 0;
            $fileSizeText = $fileSize > 1048576 
                ? round($fileSize / 1048576, 1) . ' MB' 
                : round($fileSize / 1024) . ' KB';
        ?>
        <a href="<?= BASE_URL ?>soportes/descargar_archivo/<?= $archivo->id ?>" 
           class="td-file-item" title="Descargar archivo">
            <div class="td-file-icon <?= $iconType ?>">
                <i class="bi <?= $iconClass ?>"></i>
            </div>
            <p class="td-file-name"><?= htmlspecialchars($archivo->nombre_original ?? $archivo->nombre) ?></p>
            <span class="td-file-size"><?= $fileSizeText ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Dropzone vacío -->
    <div class="td-files-dropzone" onclick="document.getElementById('archivoInput')?.click()">
        <div class="td-files-dropzone-icon">
            <i class="bi bi-image"></i>
        </div>
        <h4>Arrastra archivos aquí</h4>
        <p>Soporta imágenes (PNG, JPG) y documentos PDF hasta 10MB.</p>
        <span class="td-files-dropzone-link">O selecciona desde tu ordenador</span>
    </div>
    <?php endif; ?>
</div>


