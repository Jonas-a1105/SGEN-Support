<?php
/**
 * Tab: Comentarios - V2 Ultra Moderna
 * Hilo de conversación estilo chat
 */

use App\Helpers\ViewHelper;

$comentarios = $comentarios ?? [];
$canComment = isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor']);
?>

<style>
/* Estilos de selección mejorados - sin borde rojo */
.td-comment {
    position: relative;
    transition: all 0.2s ease;
}
.td-comments-list.selection-active .td-comment {
    cursor: pointer;
}
.td-comments-list.selection-active .td-comment:hover .td-comment-text {
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
}
/* Selección sutil - resaltado en la burbuja, sin borde rojo */
.td-comments-list.selection-active .td-comment.selected .td-comment-text {
    background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%) !important;
    border-color: #818cf8 !important;
}
.td-comments-list.selection-active .td-comment.selected::after {
    content: '\F26B'; /* bi-check-circle-fill */
    font-family: 'bootstrap-icons';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: #6366f1;
    background: white;
    border-radius: 50%;
    font-size: 1.1rem;
    box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
    z-index: 10;
}
.td-comments-list.selection-active .td-comment:not(.own).selected::after { right: -8px; }
.td-comments-list.selection-active .td-comment.own.selected::after { left: -8px; }
/* Disable interactions when selecting */
.td-comments-list.selection-active .td-comment a,
.td-comments-list.selection-active .td-comment button {
    pointer-events: none;
}

/* Botón eliminar moderno */
#btnBulkDelete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    transition: all 0.2s;
}
#btnBulkDelete:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
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

            // Abrir modal de confirmación moderno
            showBulkDeleteModal(selectedIds.size, function() {
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
                        
                        // Mostrar éxito con overlay moderno
                        if (typeof showSuccessOverlay === 'function') {
                            showSuccessOverlay('¡Eliminados!', data.message || 'Comentarios eliminados');
                        }
                        
                    } else {
                        alert(data.message || 'Error al eliminar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
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
        // Nuevos comentarios siempre son del usuario actual, agregar clase 'own'
        commentDiv.className = `td-comment own ${data.is_internal ? 'internal' : ''} animate-fadeIn`;
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
                
                showModernDeleteModal(
                    '¿Eliminar comentario?', 
                    'Esta acción no se puede deshacer.', 
                    function() {
                        fetch(url, {
                            method: 'GET',
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
                                else if (window.Swal) Swal.fire('Eliminado', 'Comentario eliminado.', 'success');
                                
                            } else {
                                Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Error de conexión', 'error');
                        });
                    }
                );
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

<style>
/* Modal Eliminación Masiva - Mismo estilo que otros modales */
.bdm-overlay {
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
.bdm-overlay.show { opacity: 1; }
.bdm-modal {
    background: white;
    width: 100%;
    max-width: 360px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bdm-overlay.show .bdm-modal { transform: scale(1) translateY(0); }
.bdm-body { padding: 2rem 1.5rem 1.5rem; text-align: center; }
.bdm-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem; color: #ef4444; font-size: 1.5rem;
    border: 3px solid #fecaca;
}
.bdm-title { margin: 0 0 0.5rem; font-size: 1.125rem; font-weight: 700; color: #0f172a; }
.bdm-message { margin: 0; font-size: 0.875rem; color: #64748b; }
.bdm-footer {
    padding: 1rem 1.5rem; background: #f8fafc;
    display: flex; gap: 0.75rem; justify-content: center;
}
.bdm-btn {
    padding: 0.625rem 1.25rem; border-radius: 10px;
    font-size: 0.875rem; font-weight: 600;
    cursor: pointer; border: none; transition: all 0.2s;
}
.bdm-btn-cancel {
    background: white; color: #475569; border: 1px solid #e2e8f0;
}
.bdm-btn-cancel:hover { background: #f1f5f9; }
.bdm-btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}
.bdm-btn-delete:hover {
    transform: translateY(-1px); box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}
</style>

<script>
window.ModernDeleteModal = {
    overlay: null,
    callback: null,
    
    init() {
        this.overlay = document.getElementById('modernDeleteModal');
        if (this.overlay && this.overlay.parentNode !== document.body) {
            document.body.appendChild(this.overlay);
        }
    },
    
    close() {
        if (!this.overlay) return;
        this.overlay.classList.remove('show');
        setTimeout(() => { this.overlay.style.display = 'none'; }, 300);
    },
    
    confirm() {
        if (this.callback) this.callback();
        this.close();
    }
};

function showModernDeleteModal(title, message, callback) {
    let overlay = document.getElementById('modernDeleteModal');
    if (!overlay) {
        ModernDeleteModal.init();
        overlay = ModernDeleteModal.overlay;
    }
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }
    
    document.getElementById('mdmTitle').textContent = title;
    document.getElementById('mdmMessage').textContent = message;
    
    ModernDeleteModal.callback = callback;
    document.getElementById('mdmConfirmBtn').onclick = () => ModernDeleteModal.confirm();
    
    overlay.style.display = 'flex';
    overlay.offsetHeight;
    overlay.classList.add('show');
}

// Wrapper for Bulk Delete to maintain compatibility if needed, or simply update call site
function showBulkDeleteModal(count, callback) {
    const title = `¿Eliminar ${count} comentario${count > 1 ? 's' : ''}?`;
    showModernDeleteModal(title, 'Esta acción no se puede deshacer', callback);
}

document.addEventListener('DOMContentLoaded', () => { ModernDeleteModal.init(); });
</script>
