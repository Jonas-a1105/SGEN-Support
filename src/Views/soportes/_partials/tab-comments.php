<?php
/**
 * Tab: Comentarios - V2 Ultra Moderna
 * Hilo de conversación estilo chat
 */

use App\Helpers\ViewHelper;

$comentarios = $comentarios ?? [];
$canComment = isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor']);
?>

<!-- CSS Styles moved to ticket-detail-v2.css -->


<div class="td-comments-container">
    <!-- Header -->
    <div class="td-comments-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h3 class="td-comments-title mb-0 me-2">
                <i class="bi bi-chat-dots"></i>
                Hilo de Conversación
            </h3>
            <span class="td-comments-count badge bg-light text-dark border"><?= count($comentarios) ?></span>
        </div>
        
        <?php if ($canComment): ?>
        <div class="td-comments-actions">
             <button id="btnBulkDelete" style="display: none;">
                 <i class="bi bi-trash3"></i> Eliminar (<span id="selectedCount">0</span>)
             </button>
             <button id="btnToggleSelection" class="btn btn-outline-secondary btn-sm ms-2" title="Seleccionar varios">
                 <i class="bi bi-check2-square"></i>
             </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lista de Comentarios -->
    <div class="td-comments-list">
        <?php if (!empty($comentarios)): ?>
            <?php foreach ($comentarios as $comentario): ?>
            <?php
                $initials = 'U';
                $authorName = $comentario->nombre_usuario ?? 'Usuario';
                $parts = explode(' ', $authorName);
                $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                if (strlen($initials) < 2) $initials = strtoupper(substr($authorName, 0, 2));
                
                $isInternal = !empty($comentario->es_interno) && $comentario->es_interno;
                $timeAgo = ViewHelper::timeAgo($comentario->fecha_creacion ?? $comentario->fecha);
                $isOwn = ($comentario->usuario_id ?? null) == ($_SESSION['user_id'] ?? null);
            ?>
            <div class="td-comment <?= $isInternal ? 'internal' : '' ?> <?= $isOwn ? 'own' : '' ?>" data-comment-id="<?= $comentario->id ?>">
                <div class="td-comment-avatar"><?= $initials ?></div>
                <div class="td-comment-content">
                    <div class="td-comment-meta">
                        <span class="td-comment-author"><?= htmlspecialchars($authorName) ?></span>
                        <span class="td-comment-time"><?= $timeAgo ?></span>
                        <?php if ($isInternal): ?>
                        <span class="td-comment-internal-badge">
                            <i class="bi bi-lock-fill"></i> Interno
                        </span>
                        <?php endif; ?>
                        
                        <!-- Comment Actions -->
                        <?php if ($canComment): ?>
                        <div class="dropdown ms-auto">
                            <button class="btn btn-link td-comment-action-btn" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item btn-reply" href="#" 
                                       data-author="<?= htmlspecialchars($authorName) ?>" 
                                       data-content="<?= htmlspecialchars(substr($comentario->contenido ?? $comentario->comentario, 0, 100)) ?>...">
                                        <i class="bi bi-reply me-2"></i> Responder / Citar
                                    </a>
                                </li>
                                <?php if ($comentario->usuario_id == $_SESSION['user_id'] || $_SESSION['rol'] == 'admin'): ?>
                                <li>
                                    <a class="dropdown-item btn-edit" href="#" 
                                       data-id="<?= $comentario->id ?>" 
                                       data-content="<?= htmlspecialchars($comentario->contenido ?? $comentario->comentario) ?>">
                                        <i class="bi bi-pencil me-2"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger btn-delete-comment" href="#"
                                       data-url="<?= BASE_URL ?>soportes/eliminar_comentario/<?= $comentario->id ?>">
                                        <i class="bi bi-trash me-2"></i> Eliminar
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="td-comment-text">
                        <?= nl2br(htmlspecialchars($comentario->contenido ?? $comentario->comentario)) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="td-comments-empty">
                <i class="bi bi-chat-dots"></i>
                <p>Sin mensajes aún.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Input Area -->
    <?php if ($canComment && $soporte->estado !== 'cerrado'): ?>
    <form action="<?= BASE_URL ?>soportes/agregar_comentario" method="POST" class="td-comment-input-wrapper">
        <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
        
        <div class="td-comment-input-box">
            <span class="td-comment-input-label">Nuevo Mensaje</span>
            <textarea 
                name="contenido" 
                class="td-comment-textarea" 
                placeholder="Escribe un comentario..." 
                rows="3"
                required
            ></textarea>
            
            <div class="td-comment-actions">
                <label class="td-comment-visibility-toggle" id="toggleVisibility">
                    <input type="checkbox" name="es_interno" value="1" style="display: none;" id="checkInterno">
                    <i class="bi bi-eye" id="visibilityIcon"></i>
                    <span id="visibilityText">Visible para todos</span>
                </label>
                
                <button type="submit" class="td-comment-send-btn">
                    Enviar <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<!-- Modular Ticket Comments Script -->
<script src="<?= BASE_URL ?>js/ticket-comments.js?v=<?= time() ?>"></script>

<!-- Modal de Eliminación Moderno (Genérico) -->
<div id="modernDeleteModal" class="bdm-overlay" style="display: none;">
    <div class="bdm-modal">
        <div class="bdm-body">
            <div class="bdm-icon">
                <i class="bi bi-trash3"></i>
            </div>
            <h3 class="bdm-title" id="mdmTitle">¿Eliminar?</h3>
            <p class="bdm-message" id="mdmMessage">Esta acción no se puede deshacer</p>
        </div>
        <div class="bdm-footer">
            <button type="button" class="bdm-btn bdm-btn-cancel" onclick="ModernDeleteModal.close()">
                Cancelar
            </button>
            <button type="button" class="bdm-btn bdm-btn-delete" id="mdmConfirmBtn">
                <i class="bi bi-trash3"></i> Eliminar
            </button>
        </div>
    </div>
</div>






