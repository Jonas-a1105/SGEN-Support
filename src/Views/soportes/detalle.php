<?php
/**
 * Vista de Detalle de Ticket - V2 Ultra Moderna
 * Diseño basado en componente React moderno
 */

// Required Helpers
require_once __DIR__ . '/../../Helpers/BadgeHelper.php';
require_once __DIR__ . '/../../Helpers/ViewHelper.php';
require_once __DIR__ . '/../../Helpers/PermissionHelper.php';

use App\Helpers\{BadgeHelper, ViewHelper, PermissionHelper};

// Preparar datos del ticket
$ticketId = $soporte->id ?? 0;
$ticketTitle = $soporte->descripcion ?? 'Sin descripción';
$ticketStatus = $soporte->estado ?? 'pendiente';
$ticketPriority = $soporte->prioridad ?? 'media';

// Estado labels
$estadoLabels = [
    'pendiente' => 'Pendiente',
    'abierto' => 'Abierto',
    'en_proceso' => 'En Proceso',
    'en_espera' => 'En Espera',
    'resuelto' => 'Resuelto',
    'cerrado' => 'Cerrado',
];

$prioridadLabels = [
    'baja' => 'Baja',
    'media' => 'Normal',
    'alta' => 'Alta',
    'critica' => 'Crítica',
];

// SLA status
$slaStatus = 'on_time';
$slaLabel = 'A tiempo';
if (!empty($soporte->fecha_vencimiento)) {
    $vence = strtotime($soporte->fecha_vencimiento);
    $ahora = time();
    if ($ticketStatus === 'resuelto' || $ticketStatus === 'cerrado') {
        $slaStatus = 'completed';
        $slaLabel = 'Completado';
    } elseif ($ahora > $vence) {
        $slaStatus = 'expired';
        $slaLabel = 'Vencido';
    } elseif ($ahora > $vence - 3600) {
        $slaStatus = 'warning';
        $slaLabel = 'Por vencer';
    }
}

// Iniciales del técnico
$techInitials = 'NA';
$techName = $soporte->tecnico_asignado ?? null;
if ($techName) {
    $parts = explode(' ', $techName);
    $techInitials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
    if (strlen($techInitials) < 2) $techInitials = strtoupper(substr($techName, 0, 2));
}
?>



<div class="td-main-container" style="width: 1150px; margin: 0 auto;">

<!-- ============================================================================
     HEADER MODERNO
     ============================================================================ -->
<div class="td-header">
    <div class="td-header-left">
        <div class="td-header-number">#<?= $ticketId ?></div>
        <div class="td-header-info">
            <h1><?= htmlspecialchars(mb_strimwidth($ticketTitle, 0, 80, '...')) ?></h1>
            <div class="td-header-meta">
                <span><i class="bi bi-display"></i> Soporte Técnico</span>
                <span class="separator">•</span>
                <span><i class="bi bi-flag"></i> Prioridad <?= $prioridadLabels[$ticketPriority] ?? 'Normal' ?></span>
            </div>
        </div>
    </div>
    
    <div class="td-header-badges">
        <?php 
        $badgeClass = 'td-badge-pending';
        if ($ticketStatus === 'en_proceso') $badgeClass = 'td-badge-in-process';
        elseif ($ticketStatus === 'resuelto' || $ticketStatus === 'cerrado') $badgeClass = 'td-badge-resolved';
        ?>
        <div class="td-badge <?= $badgeClass ?>">
            <?php if ($ticketStatus === 'pendiente' || $ticketStatus === 'abierto'): ?>
            <span class="dot"></span>
            <?php endif; ?>
            <?= $estadoLabels[$ticketStatus] ?? ucfirst($ticketStatus) ?>
        </div>
        
        <?php 
        $slaClass = 'td-badge-sla';
        if ($slaStatus === 'warning') $slaClass = 'td-badge-sla-warning';
        elseif ($slaStatus === 'expired') $slaClass = 'td-badge-sla-danger';
        ?>
        <div class="td-badge <?= $slaClass ?>">
            <i class="bi bi-shield-check"></i>
            <?= $slaLabel ?> (SLA)
        </div>
        
        <div class="td-header-actions">
            <div class="dropdown">
                <button class="td-action-btn" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if ($ticketStatus == 'en_proceso' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                    <li>
                        <a class="dropdown-item btn-confirmar" href="#" 
                           data-url="<?= BASE_URL ?>soportes/marcar_espera/<?= $soporte->id ?>"
                           data-titulo="¿Poner en espera?"
                           data-texto="El ticket cambiará a estado de espera."
                           data-icono="warning"
                           data-boton="Sí, poner en espera">
                            <i class="bi bi-pause-circle me-2"></i> Poner en Espera
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item btn-confirmar" href="#" 
                           data-url="<?= BASE_URL ?>soportes/resolver/<?= $soporte->id ?>"
                           data-titulo="¿Marcar como resuelto?"
                           data-texto="El ticket se marcará como completado."
                           data-icono="success"
                           data-boton="Sí, resolver">
                            <i class="bi bi-check-lg me-2"></i> Marcar Resuelto
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($ticketStatus == 'en_espera' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                    <li>
                        <a class="dropdown-item btn-confirmar" href="#" 
                           data-url="<?= BASE_URL ?>soportes/reanudar/<?= $soporte->id ?>"
                           data-titulo="¿Reanudar ticket?"
                           data-texto="El ticket volverá a estar en proceso."
                           data-icono="info"
                           data-boton="Sí, reanudar">
                            <i class="bi bi-play-circle me-2"></i> Reanudar
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>soportes/pdf/<?= $soporte->id ?>" target="_blank">
                            <i class="bi bi-file-pdf me-2"></i> Descargar PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>soportes">
                            <i class="bi bi-arrow-left me-2"></i> Volver
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     TAB BAR MODERNO
     ============================================================================ -->
<ul class="nav nav-tabs td-nav-tabs" id="ticketTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
            <i class="bi bi-file-text"></i>
            <span>Información General</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button">
            <i class="bi bi-paperclip"></i>
            <span>Archivos Adjuntos</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button">
            <i class="bi bi-chat-dots"></i>
            <span>Comentarios</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button">
            <i class="bi bi-clipboard-check"></i>
            <span>Bitácora Técnica</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="signature-tab" data-bs-toggle="tab" data-bs-target="#signature" type="button">
            <i class="bi bi-star-fill"></i>
            <span>Calificación</span>
        </button>
    </li>
</ul>

<!-- ============================================================================
     TAB CONTENT
     ============================================================================ -->
<!-- ============================================================================
     TAB CONTENT WRAPPER
     ============================================================================ -->
<div class="td-content-wrapper">
    <div class="tab-content" id="ticketTabContent">
        
        <!-- TAB 1: INFORMACIÓN -->
        <div class="tab-pane fade show active animate-fadeIn" id="info" role="tabpanel">
            <?php require __DIR__ . '/_partials/tab-info.php'; ?>
        </div>
        
        <!-- TAB 2: ARCHIVOS -->
        <div class="tab-pane fade animate-fadeIn" id="files" role="tabpanel">
            <?php require __DIR__ . '/_partials/tab-files.php'; ?>
        </div>
        
        <!-- TAB 3: COMENTARIOS -->
        <div class="tab-pane fade animate-fadeIn" id="comments" role="tabpanel">
            <?php require __DIR__ . '/_partials/tab-comments.php'; ?>
        </div>
        
        <!-- TAB 4: BITÁCORA TÉCNICA -->
        <div class="tab-pane fade animate-fadeIn" id="notes" role="tabpanel">
            <?php require __DIR__ . '/_partials/tab-notes.php'; ?>
        </div>
        
        <!-- TAB 5: CALIFICACIÓN -->
        <div class="tab-pane fade animate-fadeIn" id="signature" role="tabpanel">
            <?php require __DIR__ . '/_partials/tab-signature.php'; ?>
        </div>
        
    </div>
</div>
</div> <!-- End .td-main-container -->

<!-- Existing modals and scripts -->
<?php require __DIR__ . '/_partials/modals.php'; ?>

<!-- ==========================================
     MODAL SUBIR ARCHIVO (Custom Implementation)
     Based on "Assign Technician" Modal to fix z-index/flicker
     ========================================== -->

<!-- Overlay & Modal Structure -->
<div id="uploadFileOverlay" class="uf-overlay" style="display: none;">
    <div class="uf-modal">
        <!-- Header -->
        <div class="uf-header">
            <div class="uf-header-content">
                <div class="uf-icon-box">
                    <i class="bi bi-cloud-upload"></i>
                </div>
                <div class="uf-title-box">
                    <h3 class="uf-title">Subir Archivo</h3>
                    <p class="uf-subtitle">Adjunta evidencia o documentos al ticket</p>
                </div>
            </div>
            <button type="button" class="uf-close-btn" onclick="window.UploadFileModal.close()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <form id="uploadFileForm" action="<?= BASE_URL ?>soportes/subir_archivo" method="POST" enctype="multipart/form-data">
            <div class="uf-body">
                <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                
                <!-- File Input -->
                <div class="mb-4">
                    <label class="uf-label">Seleccionar Archivo <span class="text-danger">*</span></label>
                    <div class="uf-input-group">
                        <input type="file" 
                               name="archivo" 
                               id="ufArchivoInput"
                               class="uf-input-file" 
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx"
                               required>
                    </div>
                     <p class="uf-hint">
                        <i class="bi bi-info-circle me-1"></i> Formatos: PDF, Imágenes, Office (Máx 10MB)
                    </p>
                </div>
                
                <!-- Description Input -->
                <div class="mb-2">
                    <label class="uf-label">Descripción (Opcional)</label>
                    <input type="text" 
                           name="descripcion" 
                           class="uf-input-text" 
                           placeholder="Ej: Foto del error, Factura #123...">
                </div>
            </div>

            <!-- Footer -->
            <div class="uf-footer">
                <button type="button" class="uf-btn uf-btn-cancel" onclick="window.UploadFileModal.close()">Cancelar</button>
                <button type="submit" class="uf-btn uf-btn-confirm">
                    <i class="bi bi-cloud-arrow-up me-2"></i> Subir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CSS Styles (Copied & Adapted from modal-assign-tech.css) -->
<style>
.uf-overlay {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.6); /* Match Assign Tech Modal: White overlay */
    backdrop-filter: blur(0.75px); /* Match exact blur */
    -webkit-backdrop-filter: blur(0.75px);
    z-index: 999999;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.uf-overlay.show {
    opacity: 1;
}

.uf-modal {
    background: white;
    width: 100%;
    max-width: 500px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.uf-overlay.show .uf-modal {
    transform: scale(1) translateY(0);
}

.uf-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.uf-header-content { display: flex; gap: 1rem; align-items: center; }
.uf-icon-box {
    width: 40px; height: 40px;
    background-color: #eff6ff;
    color: #2563eb;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.uf-title { margin: 0; font-size: 1.125rem; font-weight: 600; color: #0f172a; }
.uf-subtitle { margin: 0; font-size: 0.875rem; color: #64748b; }
.uf-close-btn {
    background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer; padding: 0.25rem;
    transition: color 0.2s;
}
.uf-close-btn:hover { color: #0f172a; }

.uf-body { padding: 1.5rem; }
.uf-label { display: block; font-size: 0.75rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
.uf-input-text, .uf-input-file {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    font-size: 0.875rem;
    color: #1e293b;
    background-color: #f8fafc;
    transition: all 0.2s;
}
.uf-input-file {
    padding: 0.5rem; /* Slight adjustment for file input */
}
.uf-input-file::file-selector-button {
    margin-right: 1rem;
    background-color: #e2e8f0;
    color: #1e293b;
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}
.uf-input-file::file-selector-button:hover {
    background-color: #cbd5e1;
}
.uf-input-text:focus, .uf-input-file:focus {
    outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); background-color: white;
}
.uf-hint { font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem; margin-bottom: 0; }

.uf-footer {
    padding: 1.25rem 1.5rem;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex; gap: 0.75rem; justify-content: flex-end;
}
.uf-btn {
    padding: 0.625rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; border: none; transition: all 0.2s;
}
.uf-btn-cancel { background-color: white; border: 1px solid #e2e8f0; color: #475569; }
.uf-btn-cancel:hover { background-color: #f1f5f9; border-color: #cbd5e1; color: #1e293b; }
.uf-btn-confirm { background-color: #2563eb; color: white; display: flex; align-items: center; }
.uf-btn-confirm:hover { background-color: #1d4ed8; }
</style>

<!-- JS Logic for Modal -->
<script>
window.UploadFileModal = {
    overlay: null,
    
    init() {
        this.overlay = document.getElementById('uploadFileOverlay');
        // Mover al body para asegurar z-index
        if (this.overlay && this.overlay.parentNode !== document.body) {
            document.body.appendChild(this.overlay);
        }
    },

    open() {
        if (!this.overlay) this.init();
        if (!this.overlay) return;

        this.overlay.style.display = 'flex';
        // Force reflow
        this.overlay.offsetHeight; 
        this.overlay.classList.add('show');
    },

    close() {
        if (!this.overlay) return;
        this.overlay.classList.remove('show');
        setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 300); // Match CSS transition duration
    }
};

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => {
    window.UploadFileModal.init();
});

// Intercept button click (si existe un botn con data-bs-target="#modalSubirArchivo")
// y redirigirlo a nuestra funcin open()
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-bs-target="#modalSubirArchivo"]');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        window.UploadFileModal.open();
    }
});
</script>

<?php require __DIR__ . '/_partials/scripts.php'; ?>

<!-- Script para botones de confirmación y tabs -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // SOLUCIÓN DEFINITIVA MODAL: Mover al body y forzar estilos
    const modalArchivo = document.getElementById('modalSubirArchivo');
    if (modalArchivo) {
        // 1. Mover al body
        document.body.appendChild(modalArchivo);
        
        // 2. Forzar estilos directos para asegurar que flote sobre todo
        modalArchivo.style.position = 'fixed';
        modalArchivo.style.zIndex = '9999999';
        modalArchivo.style.top = '0';
        modalArchivo.style.left = '0';
        modalArchivo.style.width = '100%';
        modalArchivo.style.height = '100%';
        
        // 3. Arreglar backdrop cuando se abre
        modalArchivo.addEventListener('show.bs.modal', function () {
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => {
                    document.body.appendChild(backdrop);
                    backdrop.style.zIndex = '9999998';
                });
            }, 10);
        });
    }

    // Activar tab desde URL hash
    if (window.location.hash) {
        const hash = window.location.hash;
        const triggerEl = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
        if (triggerEl) {
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }

    // Event listeners para botones de confirmación (Delegación de eventos)
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-confirmar');
        if (!btn) return;
        
        e.preventDefault();
        
        const url = btn.getAttribute('data-url');
        const titulo = btn.getAttribute('data-titulo');
        const texto = btn.getAttribute('data-texto');
        const icono = btn.getAttribute('data-icono');
        const boton = btn.getAttribute('data-boton');
        
        // Usar modal de confirmación moderno
        showConfirmOverlay(titulo, texto, icono, boton, url);
    });
});
</script>

<!-- Overlay de Confirmación Moderno -->
<div id="confirmOverlay" class="co-overlay" style="display: none;">
    <div class="co-modal">
        <div class="co-body">
            <div class="co-icon-container">
                <div class="co-icon" id="confirmIcon">
                    <i class="bi bi-question-lg"></i>
                </div>
            </div>
            <h3 class="co-title" id="confirmTitle">¿Confirmar acción?</h3>
            <p class="co-message" id="confirmMessage">¿Estás seguro de realizar esta acción?</p>
        </div>
        <div class="co-footer">
            <button type="button" class="co-btn co-btn-cancel" onclick="ConfirmModal.close()">
                Cancelar
            </button>
            <button type="button" class="co-btn co-btn-confirm" id="confirmActionBtn">
                Confirmar
            </button>
        </div>
    </div>
</div>

<style>
/* Overlay de Confirmación - Mismo estilo que otros modales */
.co-overlay {
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
.co-overlay.show {
    opacity: 1;
}
.co-modal {
    background: white;
    width: 100%;
    max-width: 380px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.co-overlay.show .co-modal {
    transform: scale(1) translateY(0);
}
.co-body {
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
}
.co-icon-container {
    margin-bottom: 1rem;
}
.co-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.5rem;
    border: 3px solid;
}
.co-icon.warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
    border-color: #fcd34d;
}
.co-icon.success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    border-color: #6ee7b7;
}
.co-icon.info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #2563eb;
    border-color: #93c5fd;
}
.co-icon.error {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #ef4444;
    border-color: #fecaca;
}
.co-title {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
}
.co-message {
    margin: 0;
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
}
.co-footer {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.co-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    min-width: 120px;
}
.co-btn-cancel {
    background: white;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.co-btn-cancel:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.co-btn-confirm {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.co-btn-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}
.co-btn-confirm.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}
.co-btn-confirm.warning:hover {
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
}
.co-btn-confirm.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.co-btn-confirm.success:hover {
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}
</style>

<script>
window.ConfirmModal = {
    overlay: null,
    actionUrl: null,

    init() {
        this.overlay = document.getElementById('confirmOverlay');
        if (this.overlay && this.overlay.parentNode !== document.body) {
            document.body.appendChild(this.overlay);
        }
    },

    close() {
        if (!this.overlay) return;
        this.overlay.classList.remove('show');
        setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 300);
    },

    confirm() {
        if (this.actionUrl) {
            window.location.href = this.actionUrl;
        }
    }
};

function showConfirmOverlay(titulo, mensaje, tipo, botonTexto, url) {
    let overlay = document.getElementById('confirmOverlay');
    if (!overlay) {
        ConfirmModal.init();
        overlay = ConfirmModal.overlay;
    }
    
    if (overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }

    // Actualizar contenido
    document.getElementById('confirmTitle').textContent = titulo;
    document.getElementById('confirmMessage').textContent = mensaje;
    
    // Configurar icono según tipo
    const iconDiv = document.getElementById('confirmIcon');
    iconDiv.className = 'co-icon ' + (tipo || 'info');
    
    const iconMap = {
        'warning': 'bi-exclamation-triangle',
        'success': 'bi-check-circle',
        'info': 'bi-info-circle',
        'error': 'bi-x-circle'
    };
    iconDiv.innerHTML = '<i class="bi ' + (iconMap[tipo] || 'bi-question-circle') + '"></i>';
    
    // Configurar botón
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.textContent = botonTexto || 'Confirmar';
    confirmBtn.className = 'co-btn co-btn-confirm ' + (tipo || 'info');
    
    // Guardar URL y configurar click
    ConfirmModal.actionUrl = url;
    confirmBtn.onclick = () => ConfirmModal.confirm();
    
    // Mostrar modal
    overlay.style.display = 'flex';
    overlay.offsetHeight;
    overlay.classList.add('show');
}

document.addEventListener('DOMContentLoaded', () => {
    ConfirmModal.init();
});
</script>

<!-- CSS HOTFIX FOR MODALS -->
<style>
.modal {
    z-index: 99999 !important;
}
.modal-backdrop {
    z-index: 99998 !important;
}
.ms-sidebar {
    z-index: 1040 !important;
}
</style>
