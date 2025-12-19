/**
 * Ticket Comments - Comment Management
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.TICKET_COMMENTS_LOADED) return;
    window.TICKET_COMMENTS_LOADED = true;

    const getBaseUrl = () => window.BASE_URL || document.body.dataset.baseUrl || '/';

    function initTicketComments() {
        const commentsList = document.querySelector('.td-comments-list');
        if (!commentsList) return; // Not on ticket detail page with comments

        const CONFIG = {
            baseUrl: getBaseUrl(),
            urls: {
                add: getBaseUrl() + 'soportes/agregar_comentario',
                edit: getBaseUrl() + 'soportes/editar_comentario',
                deleteBase: getBaseUrl() + 'soportes/eliminar_comentario/',
                bulkDelete: getBaseUrl() + 'soportes/eliminar_comentarios_masivos'
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

        // Bulk Selection Elements
        const btnToggleSelection = document.getElementById('btnToggleSelection');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        let isSelectionMode = false;
        let selectedIds = new Set();

        // Toggle Visibility Logic
        if (toggle && checkbox) {
            toggle.addEventListener('click', function () {
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
            btnToggleSelection.addEventListener('click', function () {
                isSelectionMode = !isSelectionMode;

                if (isSelectionMode) {
                    btnToggleSelection.classList.add('active', 'btn-secondary');
                    btnToggleSelection.classList.remove('btn-outline-secondary');
                    commentsList.classList.add('selection-active');
                    if (form) form.style.opacity = '0.5';
                    if (textarea) textarea.disabled = true;
                } else {
                    btnToggleSelection.classList.remove('active', 'btn-secondary');
                    btnToggleSelection.classList.add('btn-outline-secondary');
                    commentsList.classList.remove('selection-active');
                    selectedIds.clear();
                    updateSelectionUI();
                    if (form) form.style.opacity = '1';
                    if (textarea) textarea.disabled = false;
                }
            });
        }

        // Bulk Delete Action
        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', function () {
                if (selectedIds.size === 0) return;

                if (typeof showBulkDeleteModal === 'function') {
                    showBulkDeleteModal(selectedIds.size, function () {
                        executeBulkDelete();
                    });
                } else {
                    if (confirm(`¿Eliminar ${selectedIds.size} comentarios?`)) {
                        executeBulkDelete();
                    }
                }
            });
        }

        function executeBulkDelete() {
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
                        selectedIds.forEach(id => {
                            const el = commentsList.querySelector(`[data-comment-id="${id}"]`);
                            if (el) {
                                el.style.opacity = '0';
                                setTimeout(() => el.remove(), 300);
                            }
                        });

                        if (btnToggleSelection) btnToggleSelection.click();

                        if (typeof showSuccessOverlay === 'function') {
                            showSuccessOverlay('¡Eliminados!', data.message || 'Comentarios eliminados');
                        } else if (window.Toast) {
                            Toast.show('success', data.message || 'Comentarios eliminados');
                        }
                    } else {
                        alert(data.message || 'Error al eliminar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
        }

        function updateSelectionUI() {
            if (!selectedCountSpan) return;

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
            form.addEventListener('submit', function (e) {
                if (form.action && form.action.includes('editar_comentario')) {
                    return;
                }

                e.preventDefault();

                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';

                const formData = new FormData(form);

                fetch(form.action || CONFIG.urls.add, {
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
                            if (textarea) textarea.value = '';
                            if (commentsList.querySelector('.td-comments-empty')) {
                                commentsList.querySelector('.td-comments-empty').remove();
                            }
                            commentsList.scrollTop = commentsList.scrollHeight;
                        } else {
                            if (window.Swal) Swal.fire('Error', data.message || 'Error al enviar el comentario', 'error');
                            else alert(data.message || 'Error al enviar el comentario');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (error.message.includes('sesión expirada')) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sesión Expirada',
                                    text: 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.',
                                    confirmButtonText: 'Ir al Login'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert('Sesión expirada. Recargando...');
                                window.location.reload();
                            }
                        } else {
                            if (window.Swal) Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                            else alert('Ocurrió un error al procesar la solicitud.');
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
            commentDiv.className = `td-comment own ${data.is_internal ? 'internal' : ''} animate-fadeIn`;
            commentDiv.setAttribute('data-comment-id', data.id);

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
            commentsList.addEventListener('click', function (e) {

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
                    if (textarea) {
                        textarea.value = quote + textarea.value;
                        textarea.focus();
                    }
                    return;
                }

                // Edit Action
                const editBtn = e.target.closest('.btn-edit');
                if (editBtn && form) {
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

                    if (textarea) textarea.value = content;
                    if (inputLabel) {
                        inputLabel.textContent = 'Editando comentario...';
                        inputLabel.classList.add('text-warning');
                    }
                    if (submitBtn) submitBtn.innerHTML = 'Actualizar <i class="bi bi-check-lg"></i>';
                    if (textarea) textarea.focus();

                    if (!document.querySelector('.btn-cancel-edit')) {
                        const cancelBtn = document.createElement('button');
                        cancelBtn.type = 'button';
                        cancelBtn.className = 'td-comment-cancel-btn btn-cancel-edit';
                        cancelBtn.innerHTML = '<i class="bi bi-x"></i>';
                        cancelBtn.title = 'Cancelar edición';
                        cancelBtn.onclick = function () {
                            resetForm();
                        };
                        if (submitBtn && submitBtn.parentNode) {
                            submitBtn.parentNode.insertBefore(cancelBtn, submitBtn);
                        }
                    }
                    return;
                }

                // Delete Action (AJAX)
                const deleteBtn = e.target.closest('.btn-delete-comment');
                if (deleteBtn) {
                    e.preventDefault();
                    const url = deleteBtn.dataset.url;

                    if (typeof showModernDeleteModal === 'function') {
                        showModernDeleteModal(
                            '¿Eliminar comentario?',
                            'Esta acción no se puede deshacer.',
                            function () { executeDelete(url, deleteBtn); }
                        );
                    } else if (confirm('¿Eliminar comentario?')) {
                        executeDelete(url, deleteBtn);
                    }
                }
            });
        }

        function executeDelete(url, deleteBtn) {
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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
                        if (window.Swal) Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                        else alert(data.message || 'Error al eliminar');
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (window.Swal) Swal.fire('Error', 'Error de conexión', 'error');
                    else alert('Error de conexión');
                });
        }

        function resetForm() {
            if (!form) return;
            form.action = CONFIG.urls.add;
            const idInput = form.querySelector('input[name="comentario_id"]');
            if (idInput) idInput.remove();

            if (textarea) textarea.value = '';
            if (inputLabel) {
                inputLabel.textContent = 'Nuevo Mensaje';
                inputLabel.classList.remove('text-warning');
            }
            if (submitBtn) submitBtn.innerHTML = 'Enviar <i class="bi bi-send"></i>';

            const cancelBtn = document.querySelector('.btn-cancel-edit');
            if (cancelBtn) cancelBtn.remove();
        }
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketComments);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketComments();
    }
})();
