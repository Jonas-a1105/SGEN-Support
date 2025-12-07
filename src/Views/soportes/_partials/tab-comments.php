<?php
/**
 * Tab: Comentarios - V2 Ultra Moderna
 * Hilo de conversación estilo chat
 */

use App\Helpers\ViewHelper;

$comentarios = $comentarios ?? [];
$canComment = in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor']);
?>

<div class="td-card td-comments-container">
    <!-- Header -->
    <div class="td-comments-header">
        <h3 class="td-comments-title">
            <i class="bi bi-chat-dots"></i>
            Hilo de Conversación
        </h3>
        <span class="td-comments-count"><?= count($comentarios) ?></span>
    </div>

    <!-- Lista de Comentarios -->
    <div class="td-comments-list">
        <?php if (!empty($comentarios)): ?>
            <?php foreach ($comentarios as $comentario): ?>
            <?php
                $initials = 'U';
                $authorName = $comentario->usuario_nombre ?? 'Usuario';
                $parts = explode(' ', $authorName);
                $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                if (strlen($initials) < 2) $initials = strtoupper(substr($authorName, 0, 2));
                
                $isInternal = !empty($comentario->es_interno) && $comentario->es_interno;
                $timeAgo = ViewHelper::timeAgo($comentario->fecha_creacion ?? $comentario->fecha);
            ?>
            <div class="td-comment <?= $isInternal ? 'internal' : '' ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('toggleVisibility');
    const checkbox = document.getElementById('checkInterno');
    const icon = document.getElementById('visibilityIcon');
    const text = document.getElementById('visibilityText');
    
    if (toggle && checkbox) {
        toggle.addEventListener('click', function() {
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                toggle.classList.add('internal');
                icon.className = 'bi bi-lock-fill';
                text.textContent = 'Solo visible para técnicos';
            } else {
                toggle.classList.remove('internal');
                icon.className = 'bi bi-eye';
                text.textContent = 'Visible para todos';
            }
        });
    }
});
</script>
