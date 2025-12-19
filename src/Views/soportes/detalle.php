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
<!-- CSS Styles moved to ticket-detail-v2.css -->



<!-- JS Logic moved to ticket-detail-v2.js -->
<script src="<?= BASE_URL ?>js/ticket-detail-v2.js"></script>


<?php require __DIR__ . '/_partials/scripts.php'; ?>

<!-- Script para botones de confirmación y tabs -->



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
