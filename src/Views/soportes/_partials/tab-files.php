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
        <div class="td-file-item-wrapper">
            <a href="<?= BASE_URL ?>soportes/descargar_archivo/<?= $archivo->id ?>" 
               class="td-file-item" 
               title="Descargar archivo"
               download="<?= htmlspecialchars($archivo->nombre_original ?? $archivo->nombre_archivo ?? 'archivo') ?>">
                <div class="td-file-icon <?= $iconType ?>">
                    <i class="bi <?= $iconClass ?>"></i>
                </div>
                <p class="td-file-name"><?= htmlspecialchars($archivo->nombre_original ?? $archivo->nombre) ?></p>
                <span class="td-file-size"><?= $fileSizeText ?></span>
            </a>
            <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico']) && $soporte->estado !== 'cerrado'): ?>
            <button type="button" class="td-file-delete-btn" 
                    onclick="confirmarEliminarArchivo(<?= $archivo->id ?>, '<?= htmlspecialchars(addslashes($archivo->nombre_original ?? $archivo->nombre_archivo ?? 'archivo')) ?>')"
                    title="Eliminar archivo">
                <i class="bi bi-trash3"></i>
            </button>
            <?php endif; ?>
        </div>
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

<!-- CSS para botón eliminar -->
<style>
/* Wrapper hereda estilos del card */
.td-file-item-wrapper {
    position: relative;
    background: var(--td-slate-50, #f8fafc);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    border: 1px solid var(--td-slate-100, #f1f5f9);
    transition: all 0.2s ease;
}
.td-file-item-wrapper:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: var(--td-primary, #6366f1);
    transform: translateY(-2px);
}

/* El enlace interno ahora es simple, sin estilos duplicados */
.td-file-item-wrapper .td-file-item {
    display: block;
    background: none;
    border: none;
    padding: 0;
    text-decoration: none;
    color: inherit;
}
.td-file-item-wrapper .td-file-item:hover {
    box-shadow: none;
    transform: none;
}

/* Botón eliminar */
.td-file-delete-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s;
    z-index: 5;
}
.td-file-item-wrapper:hover .td-file-delete-btn {
    opacity: 1;
}
.td-file-delete-btn:hover {
    background: #ef4444;
    color: white;
    transform: scale(1.1);
}
</style>

<!-- Modal de Confirmación Eliminar Archivo -->
<div id="deleteFileOverlay" class="df-overlay" style="display: none;">
    <div class="df-modal">
        <div class="df-body">
            <div class="df-icon-container">
                <div class="df-icon">
                    <i class="bi bi-trash3"></i>
                </div>
            </div>
            <h3 class="df-title">¿Eliminar archivo?</h3>
            <p class="df-message">Esta acción no se puede deshacer. Se eliminará permanentemente:</p>
            <p class="df-filename" id="deleteFileName">archivo.jpg</p>
        </div>
        <div class="df-footer">
            <button type="button" class="df-btn df-btn-cancel" onclick="DeleteFileModal.close()">
                Cancelar
            </button>
            <button type="button" class="df-btn df-btn-delete" id="confirmDeleteBtn">
                <i class="bi bi-trash3"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<style>
/* Modal Eliminar Archivo - Mismo estilo que otros modales */
.df-overlay {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(0.75px);
    -webkit-backdrop-filter: blur(0.75px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.df-overlay.show {
    opacity: 1;
}
.df-modal {
    background: white;
    width: 100%;
    max-width: 380px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.df-overlay.show .df-modal {
    transform: scale(1) translateY(0);
}
.df-body {
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
}
.df-icon-container {
    margin-bottom: 1.25rem;
}
.df-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: #ef4444;
    font-size: 1.5rem;
    border: 3px solid #fecaca;
}
.df-title {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
}
.df-message {
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
}
.df-filename {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    background: #f1f5f9;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: inline-block;
    max-width: 100%;
    word-break: break-all;
}
.df-footer {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.df-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    min-width: 120px;
}
.df-btn-cancel {
    background: white;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.df-btn-cancel:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.df-btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}
.df-btn-delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}
.df-btn-delete i {
    margin-right: 0.25rem;
}
</style>

<script>
window.DeleteFileModal = {
    overlay: null,
    archivoId: null,

    init() {
        this.overlay = document.getElementById('deleteFileOverlay');
        if (this.overlay && this.overlay.parentNode !== document.body) {
            document.body.appendChild(this.overlay);
        }
        
        // Bind confirm button
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.confirm());
        }
    },

    open(archivoId, nombreArchivo) {
        if (!this.overlay) this.init();
        if (!this.overlay) return;

        this.archivoId = archivoId;
        document.getElementById('deleteFileName').textContent = nombreArchivo;

        this.overlay.style.display = 'flex';
        this.overlay.offsetHeight; // Force reflow
        this.overlay.classList.add('show');
    },

    close() {
        if (!this.overlay) return;
        this.overlay.classList.remove('show');
        setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 250);
    },

    confirm() {
        if (this.archivoId) {
            window.location.href = BASE_URL + 'soportes/eliminar_archivo/' + this.archivoId;
        }
    }
};

function confirmarEliminarArchivo(archivoId, nombreArchivo) {
    DeleteFileModal.open(archivoId, nombreArchivo);
}

document.addEventListener('DOMContentLoaded', () => {
    DeleteFileModal.init();
});
</script>
