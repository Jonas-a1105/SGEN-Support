<?php
/**
 * Vista de Detalle de Ticket - Refactorizada con Tabs
 * Optimizada con DRY principles y BadgeHelper
 */

// Required Helpers
require_once __DIR__ . '/../../Helpers/BadgeHelper.php';
require_once __DIR__ . '/../../Helpers/ViewHelper.php';
require_once __DIR__ . '/../../Helpers/PermissionHelper.php';

use App\Helpers\{BadgeHelper, ViewHelper, PermissionHelper};

// Include tab styles
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/ticket-detail-tabs.css?v=<?= time() ?>">

<!-- Sticky Header con Info Crítica -->
<div class="ticket-header-sticky p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h2 class="h5 mb-0">
            <i class="bi bi-ticket-detailed text-primary"></i>
            Ticket #<?= $soporte->id ?>
        </h2>
        
        <!-- KPI Bar - Info crítica siempre visible -->
        <div class="ticket-kpi-bar">
            <?= BadgeHelper::renderEstado($soporte->estado) ?>
            <?= BadgeHelper::renderPrioridad($soporte->prioridad) ?>
            
            <!-- SLA Badge -->
           <?php if (!empty($soporte->fecha_vencimiento)): ?>
                <?php
                    $vence = strtotime($soporte->fecha_vencimiento);
                    $ahora = time();
                    $esResuelto = $soporte->estado === 'resuelto';
                    
                    if ($esResuelto) {
                        echo '<span class="badge bg-secondary"><i class="bi bi-check-circle me-1"></i>Completado</span>';
                    } elseif ($ahora > $vence) {
                        echo '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>VENCIDO</span>';
                    } elseif ($ahora > $vence - 3600) {
                        echo '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Por vencer</span>';
                    } else {
                        echo '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>A tiempo</span>';
                    }
                ?>
            <?php endif; ?>
            
            <!-- Actions Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if ($soporte->estado == 'en_proceso' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                        <li>
                            <a class="dropdown-item" href="#" onclick="confirmarAccion('<?= BASE_URL ?>soportes/marcar_espera/<?= $soporte->id ?>', '¿Poner en espera?', 'El ticket cambiará a estado de espera.', 'warning', 'Sí, poner en espera'); return false;">
                                <i class="bi bi-pause-circle me-2"></i> Poner en Espera
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="confirmarAccion('<?= BASE_URL ?>soportes/resolver/<?= $soporte->id ?>', '¿Marcar como resuelto?', 'El ticket se marcará como completado.', 'success', 'Sí, resolver'); return false;">
                                <i class="bi bi-check-lg me-2"></i> Marcar Resuelto
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($soporte->estado == 'en_espera' && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                        <li>
                            <a class="dropdown-item" href="#" onclick="confirmarAccion('<?= BASE_URL ?>soportes/reanudar/<?= $soporte->id ?>', '¿Reanudar ticket?', 'El ticket volverá a estar en proceso.', 'info', 'Sí, reanudar'); return false;">
                                <i class="bi bi-play-circle me-2"></i> Reanudar
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider"></li>
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

<!-- Tabs Navigation -->
<ul class="nav nav-tabs nav-fill mb-4" id="ticketTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
            <i class="bi bi-ticket-detailed"></i>
            <span>Información</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button">
            <i class="bi bi-paperclip"></i>
            <span>Archivos</span>
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
            <span>Observaciones</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="signature-tab" data-bs-toggle="tab" data-bs-target="#signature" type="button">
            <i class="bi bi-pen"></i>
            <span>Firma</span>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="ticketTabContent">
    
    <!-- TAB 1: INFORMACIÓN -->
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <?php require __DIR__ . '/_partials/tab-info.php'; ?>
    </div>
    
    <!-- TAB 2: ARCHIVOS -->
    <div class="tab-pane fade" id="files" role="tabpanel">
        <?php require __DIR__ . '/_partials/tab-files.php'; ?>
    </div>
    
    <!-- TAB 3: COMENTARIOS -->
    <div class="tab-pane fade" id="comments" role="tabpanel">
        <?php require __DIR__ . '/_partials/tab-comments.php'; ?>
    </div>
    
    <!-- TAB 4: OBSERVACIONES -->
    <div class="tab-pane fade" id="notes" role="tabpanel">
        <?php require __DIR__ . '/_partials/tab-notes.php'; ?>
    </div>
    
    <!-- TAB 5: FIRMA -->
    <div class="tab-pane fade" id="signature" role="tabpanel">
        <?php require __DIR__ . '/_partials/tab-signature.php'; ?>
    </div>
    
</div>

<!-- Include Tab Manager JS -->
<script src="<?= BASE_URL ?>js/ticket-tabs.js?v=<?= time() ?>"></script>

<!-- Existing modals and scripts from original file -->
<?php require __DIR__ . '/_partials/modals.php'; ?>
<?php require __DIR__ . '/_partials/scripts.php'; ?>
