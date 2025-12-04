<?php
/**
 * Tab: Comentarios
 */
?>

<div class="card shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-dark"><i class="bi bi-chat-dots me-2"></i>Comentarios</h5>
        <span class="badge bg-secondary"><?= count($comentarios ?? []) ?></span>
    </div>
    <div class="card-body">
        <!-- Lista de Comentarios -->
        <div class="mb-4" style="max-height: 500px; overflow-y: auto;">
            <?php if (empty($comentarios)): ?>
                <p class="text-muted text-center py-3">No hay comentarios aún.</p>
            <?php else: ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="d-flex mb-3 <?= $comentario->es_interno ? 'ms-4' : '' ?>">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <?= strtoupper(substr($comentario->nombre_usuario, 0, 1)) ?>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="card <?= $comentario->es_interno ? 'border-warning bg-light-warning' : 'bg-light' ?>">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="card-title mb-0 small fw-bold">
                                            <?= htmlspecialchars($comentario->nombre_usuario) ?>
                                            <?php if ($comentario->es_interno): ?>
                                                <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock-fill"></i> Interno</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <?= date('d/m/Y H:i', strtotime($comentario->fecha)) ?>
                                        </small>
                                    </div>
                                    <p class="card-text small mb-0"><?= nl2br(htmlspecialchars($comentario->comentario)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Formulario Nuevo Comentario -->
        <?php if (in_array($soporte->estado, ['pendiente', 'en_proceso', 'en_espera'])): ?>
            <hr>
            <form action="<?= BASE_URL ?>soportes/agregar_comentario" method="POST">
                <input type="hidden" name="ticket_id" value="<?= $soporte->id ?>">
                <div class="mb-2">
                    <textarea name="comentario" class="form-control form-control-sm" rows="3" placeholder="Escribe un comentario..." required></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <?php if (in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="es_interno" name="es_interno" value="1">
                            <label class="form-check-label small text-muted" for="es_interno">
                                <i class="bi bi-lock"></i> Comentario Interno
                            </label>
                        </div>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i> Enviar
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
