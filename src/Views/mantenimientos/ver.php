<?php
/**
 * Vista de Mantenimientos - Modern Hub
 * Card-based layout with visual status indicators.
 */

$fechaObj = new DateTime($mantenimiento->fecha);
$now = new DateTime();
// Overdue logic: Past date AND not realized/completed AND not cancelled
$isOverdue = $fechaObj < $now && $mantenimiento->estado !== 'realizado' && $mantenimiento->estado !== 'completado' && $mantenimiento->estado !== 'cancelado';

// Status configurations
$statusConfig = [
    'pendiente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'bi-clock-history'],
    'programado' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'bi-calendar-check'],
    'en_proceso' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'bi-gear-wide-connected'],
    'realizado' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'bi-check-circle-fill'],
    'completado' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'bi-check-circle-fill'],
    'cancelado' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'bi-x-circle-fill'],
];

$currentStatus = strtolower($mantenimiento->estado);
$sStyle = $statusConfig[$currentStatus] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'bi-circle'];
?>

<div class="mh-container">
    <div class="mh-max-w-6xl">
        
        <!-- HEADER (Outside Card) -->
        <div class="d-flex justify-content-between align-items-start mb-4 animate-fadeIn">
            <div>
                <a href="<?= BASE_URL ?>mantenimientos" class="text-decoration-none text-muted mb-2 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Mantenimientos
                </a>
                <div class="d-flex align-items-center gap-3">
                    <h1 class="h3 fw-bold text-dark mb-0">
                        <?= $mantenimiento->tipo_mantenimiento == 'correctivo' ? 'Mantenimiento Correctivo' : 'Mantenimiento Preventivo' ?>
                    </h1>
                    <span class="badge rounded-pill <?= $sStyle['bg'] ?> <?= $sStyle['text'] ?> px-3 py-2 border-0 d-flex align-items-center gap-2">
                        <i class="bi <?= $sStyle['icon'] ?>"></i>
                        <?= ucfirst($mantenimiento->estado) ?>
                    </span>

                    <!-- Duration Info Badge -->
                    <span class="badge rounded-pill bg-slate-100 text-slate-700 px-3 py-2 border-0 d-flex align-items-center gap-2">
                        <i class="bi bi-hourglass-split"></i>
                        <?= $mantenimiento->duracion ?? 60 ?> min
                    </span>
                    
                    <!-- Timer Badge -->
                    <?php 
                        $startTime = strtotime($mantenimiento->fecha);
                        $durationSeconds = ($mantenimiento->duracion ?? 60) * 60;
                        $endTime = $startTime + $durationSeconds;
                        $now = time();
                        $showTimer = false;
                        
                        if ($mantenimiento->estado == 'pendiente' && $startTime > $now) {
                            $showTimer = true;
                        } elseif ($mantenimiento->estado == 'en_proceso' && $endTime > $now) {
                            $showTimer = true;
                        }
                    ?>
                    <?php if($showTimer): ?>
                        <span id="maintenanceTimerBadge" class="badge rounded-pill bg-white border shadow-sm text-dark px-3 py-2 d-flex align-items-center gap-2">
                             <i class="bi bi-stopwatch text-primary"></i>
                             <span id="maintenanceTimer" 
                                   data-start="<?= date('Y-m-d H:i:s', $startTime) ?>" 
                                   data-end="<?= date('Y-m-d H:i:s', $endTime) ?>" 
                                   data-status="<?= $mantenimiento->estado ?>"
                                   class="font-monospace fw-bold">Calculando...</span>
                        </span>
                    <?php endif; ?>


                </div>
                <p class="text-muted mt-1">ID: #<?= $mantenimiento->id ?> • Registrado el <?= date('d M, Y', strtotime($mantenimiento->fecha_creacion ?? $mantenimiento->fecha)) ?></p>
            </div>
            
            <div class="d-flex gap-2">
                <?php 
                    // Check if timer has expired
                    $timerExpired = ($now >= $endTime && ($mantenimiento->duracion ?? 0) > 0);
                ?>
                <?php if ($mantenimiento->estado !== 'realizado' && $mantenimiento->estado !== 'completado' && $mantenimiento->estado !== 'cancelado' && !$timerExpired): ?>
                    
                    <button type="button"
                       class="btn btn-emerald-600 shadow-sm text-white" 
                       style="background-color: #059669; border-color: #059669;"
                       onclick="confirmarMarcarRealizado(<?= $mantenimiento->id ?>)">
                        <i class="bi bi-check-lg me-1"></i> Marcar Realizado
                    </button>

                    <a href="<?= BASE_URL ?>mantenimientos/editar/<?= $mantenimiento->id ?>" class="btn btn-outline-primary shadow-sm">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                <?php endif; ?>
                <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
            </div>
        </div>

        <!-- MAIN CONTENT (Grid Layout, no outer card) -->
        <div class="animate-fadeIn mh-main-content-card" style="animation-delay: 0.1s;">
            <div class="row g-4">
                
                <!-- LEFT COLUMN: Main Details -->
                <div class="col-lg-8">
                    
                    <!-- Equipment Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-4 d-flex align-items-center">
                                <i class="bi bi-pc-display me-2 text-primary"></i>
                                Información del Equipo
                            </h5>
                            
                            <div class="d-flex align-items-center p-3 bg-slate-50 rounded-3 border border-slate-100 mb-3">
                                <div class="me-3">
                                    <div class="avatar-circle bg-white text-primary shadow-sm" style="width: 48px; height: 48px; display:flex; align-items:center; justify-content:center; border-radius:12px;">
                                        <i class="bi bi-laptop fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($mantenimiento->equipo_nombre ?? 'Equipo Desconocido') ?></h6>
                                    <p class="mb-0 text-muted small">
                                        Código: <span class="text-dark fw-medium"><?= htmlspecialchars($mantenimiento->equipo_codigo ?? 'N/A') ?></span>
                                        <span class="mx-1">•</span>
                                        Serial: <?= htmlspecialchars($mantenimiento->equipo_serial ?? 'N/A') ?>
                                    </p>
                                </div>
                                <div class="ms-auto">
                                    <a href="<?= BASE_URL ?>equipos/ver/<?= $mantenimiento->equipo_id ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-light text-primary"
                                       onclick="event.stopPropagation();">
                                        Ver Equipo <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-3">Descripción del Trabajo</h5>
                            <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.6;">
                                <?= htmlspecialchars($mantenimiento->descripcion) ?>
                            </div>
                            
                            <?php if(!empty($mantenimiento->checklist) && $mantenimiento->checklist !== '[]'): ?>
                            <hr class="my-4 text-muted opacity-25">
                            <h6 class="fw-bold text-dark mb-3">Checklist de Actividades</h6>
                            <ul class="list-group list-group-flush rounded-3 border-0">
                                <?php 
                                // Try decode JSON first
                                $checklist = json_decode($mantenimiento->checklist, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($checklist)) {
                                    foreach($checklist as $item): ?>
                                        <li class="list-group-item bg-transparent border-0 px-0 py-2 d-flex align-items-start">
                                            <i class="bi bi-check2-square text-primary me-2 mt-1"></i>
                                            <span><?= htmlspecialchars($item['text'] ?? $item) ?></span>
                                        </li>
                                    <?php endforeach; 
                                } else {
                                    // Fallback text
                                    $items = explode("\n", $mantenimiento->checklist);
                                    foreach($items as $item): 
                                        if(trim($item) === '') continue;
                                    ?>
                                    <li class="list-group-item bg-transparent border-0 px-0 py-2 d-flex align-items-start">
                                        <i class="bi bi-check2-square text-primary me-2 mt-1"></i>
                                        <span><?= htmlspecialchars(trim($item)) ?></span>
                                    </li>
                                    <?php endforeach; 
                                } ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Observations Card (if any) -->
                    <?php if (!empty($mantenimiento->observaciones)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-3">Observaciones Técnicas</h5>
                            <div class="p-3 bg-amber-50 text-amber-900 rounded-3 border border-amber-100">
                                <i class="bi bi-info-circle me-2"></i>
                                <?= nl2br(htmlspecialchars($mantenimiento->observaciones)) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- RIGHT COLUMN: Meta & Actions -->
                <div class="col-lg-4">
                    
                    <!-- Schedule Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-4">Planificación</h5>
                            
                            <div class="timeline-simple">
                                <div class="d-flex mb-4">
                                    <div class="me-3 d-flex flex-column align-items-center">
                                        <div class="rounded-circle bg-blue-100 text-blue-600 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                        <div class="h-100 border-start border-2 border-light my-1"></div>
                                    </div>
                                    <div>
                                        <label class="text-muted small fw-bold text-uppercase">Fecha Programada</label>
                                        <p class="mb-0 fw-medium text-dark"><?= date('d F, Y', strtotime($mantenimiento->fecha)) ?></p>
                                        <p class="text-muted small"><?= date('h:i A', strtotime($mantenimiento->fecha)) ?></p>
                                    </div>
                                </div>
                                
                                <div class="d-flex">
                                    <div class="me-3 d-flex flex-column align-items-center">
                                        <div class="rounded-circle bg-green-100 text-green-600 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-hourglass-split"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-muted small fw-bold text-uppercase">Próximo Vencimiento</label>
                                        <?php if(!empty($mantenimiento->proxima_fecha)): ?>
                                            <p class="mb-0 fw-medium text-dark"><?= date('d F, Y', strtotime($mantenimiento->proxima_fecha)) ?></p>
                                            <p class="text-muted small">En tiempo</p>
                                        <?php else: ?>
                                            <p class="mb-0 text-muted">No recurrente</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3 border-light">

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Frecuencia</span>
                                <span class="badge bg-light text-dark border"><?= ucfirst($mantenimiento->frecuencia ?? 'Única') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Technician Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-4">Responsable</h5>
                            
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-3" style="width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    <?= strtoupper(substr($mantenimiento->tecnico_completo ?? $mantenimiento->tecnico_nombre ?? 'U', 0, 2)) ?>
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold text-dark"><?= htmlspecialchars($mantenimiento->tecnico_completo ?? $mantenimiento->tecnico_nombre ?? 'Sin Asignar') ?></p>
                                    <p class="mb-0 text-muted small">Técnico Asignado</p>
                                </div>
                            </div>
                            
                            <?php if(!empty($mantenimiento->realizado_por)): ?>
                            <div class="mt-3 pt-3 border-top">
                                <p class="text-muted small mb-1">Realizado por (Externo/Otro):</p>
                                <span class="fw-medium text-dark"><?= htmlspecialchars($mantenimiento->realizado_por) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Admin Info Card -->
                     <div class="card border-0 shadow-sm rounded-4 bg-slate-50">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Costo del Servicio</span>
                                <span class="fw-bold text-dark">$<?= number_format($mantenimiento->costo ?? 0, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Tipo</span>
                                <span class="badge bg-white border text-secondary"><?= ucfirst($mantenimiento->tipo_mantenimiento) ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Utilities for this view */
.bg-slate-50 { background-color: #f8fafc; }
.bg-slate-100 { background-color: #f1f5f9; }
.text-slate-700 { color: #334155; }

.bg-amber-50 { background-color: #fffbeb; }
.bg-amber-100 { background-color: #fef3c7; }
.text-amber-700 { color: #b45309; }
.text-amber-900 { color: #78350f; }

.bg-blue-100 { background-color: #dbeafe; }
.text-blue-600 { color: #2563eb; }
.text-blue-700 { color: #1d4ed8; }

.bg-emerald-100 { background-color: #d1fae5; }
.text-emerald-700 { color: #047857; }

.bg-rose-100 { background-color: #ffe4e6; }
.text-rose-600 { color: #e11d48; }
.text-rose-700 { color: #be123c; }

.bg-green-100 { background-color: #dcfce7; }
.text-green-600 { color: #16a34a; }

.animate-fadeIn {
    animation: fadeIn 0.4s ease-out forwards;
    opacity: 0;
    transform: translateY(10px);
}

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* Reusing card structure via explicit class if imported css is not enough */
.mh-main-content-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
</style>
<script src="<?= BASE_URL ?>js/maintenance-timer.js?v=<?= time() ?>"></script>

<!-- Modal de Confirmación Moderno (Estilo Tickets) -->
<div id="confirmRealizadoOverlay" class="mh-confirm-overlay" style="display: none;">
    <div class="mh-confirm-modal">
        <div class="mh-confirm-body">
            <div class="mh-confirm-icon-container">
                <div class="mh-confirm-icon success">
                    <i class="bi bi-check-lg"></i>
                </div>
            </div>
            <h3 class="mh-confirm-title">¿Marcar como Realizado?</h3>
            <p class="mh-confirm-message">Este mantenimiento se registrará como completado exitosamente.</p>
        </div>
        <div class="mh-confirm-footer">
            <button type="button" class="mh-confirm-btn mh-btn-cancel" onclick="MhConfirmModal.close()">
                Cancelar
            </button>
            <button type="button" class="mh-confirm-btn mh-btn-confirm" id="mhConfirmActionBtn">
                <i class="bi bi-check-lg me-1"></i> Sí, marcar realizado
            </button>
        </div>
    </div>
</div>

<style>
/* Modal de Confirmación - Estilo moderno como Tickets */
.mh-confirm-overlay {
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
.mh-confirm-overlay.show {
    opacity: 1;
}
.mh-confirm-modal {
    background: white;
    width: 100%;
    max-width: 380px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.mh-confirm-overlay.show .mh-confirm-modal {
    transform: scale(1) translateY(0);
}
.mh-confirm-body {
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
}
.mh-confirm-icon-container {
    margin-bottom: 1rem;
}
.mh-confirm-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.75rem;
    border: 3px solid;
    animation: mhIconPulse 2s ease-in-out infinite;
}
.mh-confirm-icon.success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    border-color: #6ee7b7;
}
.mh-confirm-title {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
}
.mh-confirm-message {
    margin: 0;
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
}
.mh-confirm-footer {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.mh-confirm-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    min-width: 120px;
}
.mh-btn-cancel {
    background: white;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.mh-btn-cancel:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.mh-btn-confirm {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.mh-btn-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

@keyframes mhIconPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}
</style>

<script>
window.MhConfirmModal = {
    overlay: null,
    actionId: null,

    init() {
        this.overlay = document.getElementById('confirmRealizadoOverlay');
        if (this.overlay && this.overlay.parentNode !== document.body) {
            document.body.appendChild(this.overlay);
        }
    },

    open(id) {
        if (!this.overlay) this.init();
        if (!this.overlay) return;
        
        this.actionId = id;
        this.overlay.style.display = 'flex';
        this.overlay.offsetHeight; // Force reflow
        this.overlay.classList.add('show');
        
        // Configurar botón de confirmar
        document.getElementById('mhConfirmActionBtn').onclick = () => this.confirm();
    },

    close() {
        if (!this.overlay) return;
        this.overlay.classList.remove('show');
        setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 300);
    },

    confirm() {
        if (this.actionId) {
            window.location.href = BASE_URL + 'mantenimientos/completar/' + this.actionId;
        }
    }
};

function confirmarMarcarRealizado(id) {
    MhConfirmModal.open(id);
}

document.addEventListener('turbo:load', () => {
    MhConfirmModal.init();
});
</script>
