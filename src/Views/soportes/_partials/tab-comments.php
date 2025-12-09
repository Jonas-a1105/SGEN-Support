<?php
/**
 * Tab: Comentarios - V2 Ultra Moderna
 * Hilo de conversación estilo chat
 */

use App\Helpers\ViewHelper;

$comentarios = $comentarios ?? [];
$canComment = in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor']);
?>

<style>
.td-comment {
    position: relative;
    transition: all 0.2s ease;
    border: 1px solid transparent; /* Prevent jump */
}
.td-comments-list.selection-active .td-comment {
    cursor: pointer;
    border: 1px solid var(--border-color, #e2e8f0);
}
.td-comments-list.selection-active .td-comment:hover {
    transform: scale(1.005);
    background-color: var(--bg-hover, #f8fafc);
}
.td-comments-list.selection-active .td-comment.selected {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
}
.td-comments-list.selection-active .td-comment.selected::after {
    content: '\F26B'; /* bi-check-circle-fill */
    font-family: 'bootstrap-icons';
    position: absolute;
    top: -8px;
    right: -8px;
    color: #ef4444;
    background: white;
    border-radius: 50%;
    font-size: 1.1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 10;
}
/* Disable interactions when selecting */
.td-comments-list.selection-active .td-comment a,
.td-comments-list.selection-active .td-comment button {
    pointer-events: none;
}
</style>

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
             <button id="btnBulkDelete" class="btn btn-danger btn-sm me-2 animate-fadeIn" style="display: none;">
                 <i class="bi bi-trash-fill"></i> Eliminar (<span id="selectedCount">0</span>)
             </button>
             <button id="btnToggleSelection" class="btn btn-outline-secondary btn-sm" title="Seleccionar varios">
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
            ?>
            <div class="td-comment <?= $isInternal ? 'internal' : '' ?>" data-comment-id="<?= $comentario->id ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Safe Configuration
    const CONFIG = {
        baseUrl: "<?= defined('BASE_URL') ? BASE_URL : '/' ?>",
        urls: {
            add: "<?= defined('BASE_URL') ? BASE_URL : '/' ?>soportes/agregar_comentario",
            edit: "<?= defined('BASE_URL') ? BASE_URL : '/' ?>soportes/editar_comentario",
            deleteBase: "<?= defined('BASE_URL') ? BASE_URL : '/' ?>soportes/eliminar_comentario/",
            bulkDelete: "<?= defined('BASE_URL') ? BASE_URL : '/' ?>soportes/eliminar_comentarios_masivos"
        }
    };

    const toggle = document.getElementById('toggleVisibility');
    const checkbox = document.getElementById('checkInterno');
    const icon = document.getElementById('visibilityIcon');
    const text = document.getElementById('visibilityText');
    const textarea = document.querySelector('.td-comment-textarea');
    const inputLabel = document.querySelector('.td-comment-input-label');
    const form = document.querySelector('.td-comment-input-wrapper');
    const submitBtn = document.querySelector('.td-comment-send-btn');
    const commentsList = document.querySelector('.td-comments-list');
    
    // Bulk Selection Elements
    const btnToggleSelection = document.getElementById('btnToggleSelection');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCountSpan = document.getElementById('selectedCount');
    let isSelectionMode = false;
    let selectedIds = new Set();
    
    // Toggle Visibility Logic
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

    // Toggle Selection Mode
    if (btnToggleSelection) {
        btnToggleSelection.addEventListener('click', function() {
            isSelectionMode = !isSelectionMode;
            
            if (isSelectionMode) {
                btnToggleSelection.classList.add('active', 'btn-secondary');
                btnToggleSelection.classList.remove('btn-outline-secondary');
                commentsList.classList.add('selection-active');
                // Disable normal form
                if (form) form.style.opacity = '0.5';
                if (textarea) textarea.disabled = true;
            } else {
                btnToggleSelection.classList.remove('active', 'btn-secondary');
                btnToggleSelection.classList.add('btn-outline-secondary');
                commentsList.classList.remove('selection-active');
                // Reset selection
                selectedIds.clear();
                updateSelectionUI();
                // Enable normal form
                if (form) form.style.opacity = '1';
                if (textarea) textarea.disabled = false;
            }
        });
    }

    // Bulk Delete Action
    if (btnBulkDelete) {
        btnBulkDelete.addEventListener('click', function() {
            if (selectedIds.size === 0) return;

            Swal.fire({
                title: `¿Eliminar ${selectedIds.size} comentarios?`,
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar todos'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    fetch(CONFIG.urls.bulkDelete, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ ids: Array.from(selectedIds) })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Find and remove elements
                            selectedIds.forEach(id => {
                                const el = commentsList.querySelector(`[data-comment-id="${id}"]`);
                                if (el) {
                                    el.style.opacity = '0';
                                    setTimeout(() => el.remove(), 300);
                                }
                            });
                            
                            // Reset mode
                            btnToggleSelection.click(); // Exit selection mode
                            
                            if (window.Toast) Toast.show('success', data.message);
                            else Swal.fire('Eliminados', data.message, 'success');
                            
                        } else {
                            Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error de conexión', 'error');
                    });
                }
            });
        });
    }

    function updateSelectionUI() {
        if (!selectedCountSpan) return;
        
        // Remove class from all, add to selected
        const allComments = commentsList.querySelectorAll('.td-comment');
        allComments.forEach(el => {
            if (selectedIds.has(el.dataset.commentId)) {
                el.classList.add('selected');
            } else {
                el.classList.remove('selected');
            }
        });

        selectedCountSpan.textContent = selectedIds.size;
        
        if (selectedIds.size > 0) {
            btnBulkDelete.style.display = 'inline-block';
        } else {
            btnBulkDelete.style.display = 'none';
        }
    }

    // AJAX Submission
    if (form) {
        form.addEventListener('submit', function(e) {
            if (form.action.includes('editar_comentario')) {
                return;
            }

            e.preventDefault();
            
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Respuesta no válida del servidor. Posible sesión expirada.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    appendComment(data.comentario);
                    textarea.value = '';
                    if (commentsList.querySelector('.td-comments-empty')) {
                        commentsList.querySelector('.td-comments-empty').remove();
                    }
                    commentsList.scrollTop = commentsList.scrollHeight;
                } else {
                    Swal.fire('Error', data.message || 'Error al enviar el comentario', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message.includes('sesión expirada')) {
                     Swal.fire({
                        icon: 'warning',
                        title: 'Sesión Expirada',
                        text: 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.',
                        confirmButtonText: 'Ir al Login'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    function appendComment(data) {
        const commentDiv = document.createElement('div');
        commentDiv.className = `td-comment ${data.is_internal ? 'internal' : ''} animate-fadeIn`;
        commentDiv.setAttribute('data-comment-id', data.id); // Set ID for removal
        
        let internalBadge = '';
        if (data.is_internal) {
            internalBadge = `
                <span class="td-comment-internal-badge">
                    <i class="bi bi-lock-fill"></i> Interno
                </span>
            `;
        }
        
        const content = data.contenido ? data.contenido.replace(/\n/g, '<br>') : '';
        const deleteUrl = CONFIG.urls.deleteBase + data.id;
        const safeDataContent = data.contenido ? data.contenido.replace(/"/g, '&quot;') : '';

        commentDiv.innerHTML = `
            <div class="td-comment-avatar">${data.initials}</div>
            <div class="td-comment-content">
                <div class="td-comment-meta">
                    <span class="td-comment-author">${data.author}</span>
                    <span class="td-comment-time">${data.date}</span>
                    ${internalBadge}
                    
                     <div class="dropdown ms-auto">
                        <button class="btn btn-link td-comment-action-btn" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item btn-reply" href="#" 
                                   data-author="${data.author}" 
                                   data-content="${content.substring(0, 100)}...">
                                    <i class="bi bi-reply me-2"></i> Responder / Citar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item btn-edit" href="#" 
                                   data-id="${data.id}" 
                                   data-content="${safeDataContent}">
                                    <i class="bi bi-pencil me-2"></i> Editar
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger btn-delete-comment" href="#"
                                   data-url="${deleteUrl}">
                                    <i class="bi bi-trash me-2"></i> Eliminar
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="td-comment-text">
                    ${content}
                </div>
            </div>
        `;
        
        commentsList.appendChild(commentDiv);
    }

    // Event Delegation
    if (commentsList) {
        commentsList.addEventListener('click', function(e) {
            
            // Handle Selection Toggle
            if (isSelectionMode) {
                const commentEl = e.target.closest('.td-comment');
                if (commentEl) {
                    const id = commentEl.dataset.commentId;
                    if (id) {
                        if (selectedIds.has(id)) {
                            selectedIds.delete(id);
                        } else {
                            selectedIds.add(id);
                        }
                        updateSelectionUI();
                    }
                }
                // Stop other interactions
                e.preventDefault();
                e.stopPropagation();
                return;
            }

            // Reply Action
            const replyBtn = e.target.closest('.btn-reply');
            if (replyBtn) {
                e.preventDefault();
                const author = replyBtn.dataset.author;
                const content = replyBtn.dataset.content;
                const cleanContent = content ? content.replace(/<br>/g, '\n').replace(/<\/?[^>]+(>|$)/g, "") : '';
                const quote = `> **${author} dijo:**\n> ${cleanContent}\n\n`;
                textarea.value = quote + textarea.value;
                textarea.focus();
                return;
            }

            // Edit Action
            const editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                e.preventDefault();
                const id = editBtn.dataset.id;
                const content = editBtn.dataset.content;

                form.action = CONFIG.urls.edit;
                let idInput = form.querySelector('input[name="comentario_id"]');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'comentario_id';
                    form.appendChild(idInput);
                }
                idInput.value = id;

                textarea.value = content;
                inputLabel.textContent = 'Editando comentario...';
                inputLabel.classList.add('text-warning');
                submitBtn.innerHTML = 'Actualizar <i class="bi bi-check-lg"></i>';
                textarea.focus();

                if (!document.querySelector('.btn-cancel-edit')) {
                    const cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'td-comment-cancel-btn btn-cancel-edit';
                    cancelBtn.innerHTML = '<i class="bi bi-x"></i>';
                    cancelBtn.title = 'Cancelar edición';
                    cancelBtn.onclick = function() {
                        resetForm();
                    };
                    submitBtn.parentNode.insertBefore(cancelBtn, submitBtn);
                }
                return;
            }
            
            // Delete Action (AJAX)
            const deleteBtn = e.target.closest('.btn-delete-comment');
            if (deleteBtn) {
                e.preventDefault();
                const url = deleteBtn.dataset.url;
                
                Swal.fire({
                    title: '¿Eliminar comentario?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(url, {
                            method: 'GET', // Or convert to POST/DELETE if route supported it, but Controller supports GET
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove element from DOM
                                const commentItem = deleteBtn.closest('.td-comment');
                                if (commentItem) {
                                    commentItem.style.transition = 'all 0.3s ease';
                                    commentItem.style.opacity = '0';
                                    commentItem.style.transform = 'translateX(20px)';
                                    setTimeout(() => commentItem.remove(), 300);
                                }
                                
                                if (window.Toast) Toast.show('success', 'Comentario eliminado.');
                                else Swal.fire('Eliminado', 'Comentario eliminado.', 'success');
                                
                            } else {
                                Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Error de conexión', 'error');
                        });
                    }
                });
            }
        });
    }

    function resetForm() {
        form.action = CONFIG.urls.add;
        const idInput = form.querySelector('input[name="comentario_id"]');
        if (idInput) idInput.remove();
        
        textarea.value = '';
        inputLabel.textContent = 'Nuevo Mensaje';
        inputLabel.classList.remove('text-warning');
        submitBtn.innerHTML = 'Enviar <i class="bi bi-send"></i>';
        
        const cancelBtn = document.querySelector('.btn-cancel-edit');
        if (cancelBtn) cancelBtn.remove();
    }
});
</script>
