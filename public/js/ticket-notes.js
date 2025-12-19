/**
 * Ticket Notes - Technical Observations Editor
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.TICKET_NOTES_LOADED) return;
    window.TICKET_NOTES_LOADED = true;

    const getBaseUrl = () => window.BASE_URL || document.body.dataset.baseUrl || '/';

    // Format insertion helper
    window.insertFormat = function (prefix, suffix) {
        const textarea = document.getElementById('obsTextarea');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selectedText = text.substring(start, end);

        textarea.value = text.substring(0, start) + prefix + selectedText + suffix + text.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + prefix.length, end + prefix.length);
    };

    // Template insertion helper
    window.insertTemplate = function (type) {
        const textarea = document.getElementById('obsTextarea');
        if (!textarea) return;

        const templates = {
            'diagnóstico': '**Diagnóstico:** ',
            'reparación': '**Reparación realizada:** ',
            'piezas': '**Piezas reemplazadas:** ',
            'pruebas': '**Pruebas realizadas:** '
        };

        const template = templates[type] || '';
        const cursorPos = textarea.selectionStart;
        const text = textarea.value;

        textarea.value = text.substring(0, cursorPos) + '\n' + template + text.substring(cursorPos);
        textarea.focus();
        textarea.setSelectionRange(cursorPos + template.length + 1, cursorPos + template.length + 1);
    };

    // Success Overlay function
    window.showSuccessOverlay = function (title, message) {
        let overlay = document.getElementById('successOverlay');

        // Create if doesn't exist
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'successOverlay';
            overlay.className = 'so-overlay';
            overlay.innerHTML = `
                <div class="so-modal">
                    <div class="so-body">
                        <div class="so-icon-container">
                            <div class="so-icon">
                                <i class="bi bi-check-lg"></i>
                            </div>
                        </div>
                        <h3 class="so-title" id="successTitle">¡Éxito!</h3>
                        <p class="so-message" id="successMessage">Operación completada</p>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        // Move to body if not there
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }

        // Update content
        const titleEl = document.getElementById('successTitle');
        const messageEl = document.getElementById('successMessage');
        if (titleEl) titleEl.textContent = title || '¡Éxito!';
        if (messageEl) messageEl.textContent = message || 'Operación completada correctamente';

        // Show with animation
        overlay.style.display = 'flex';
        overlay.offsetHeight; // Force reflow
        overlay.classList.add('show');

        // Auto-close after 2 seconds
        setTimeout(() => {
            overlay.classList.remove('show');
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }, 2000);
    };

    function initTicketNotes() {
        const visBtn = document.getElementById('obsVisibilityBtn');
        const visIcon = document.getElementById('obsVisIcon');
        const visText = document.getElementById('obsVisText');
        let isPrivate = true;

        if (visBtn) {
            visBtn.addEventListener('click', function () {
                isPrivate = !isPrivate;

                if (isPrivate) {
                    visBtn.classList.remove('public');
                    visBtn.classList.add('private');
                    visIcon.className = 'bi bi-lock-fill';
                    visText.textContent = 'Privado';
                } else {
                    visBtn.classList.remove('private');
                    visBtn.classList.add('public');
                    visIcon.className = 'bi bi-eye';
                    visText.textContent = 'Público';
                }
            });
        }

        // AJAX Submission
        const form = document.querySelector('.td-obs-editor');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const submitBtn = form.querySelector('.td-obs-submit-btn');
                const originalText = submitBtn.innerHTML;
                const textarea = document.getElementById('obsTextarea');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

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
                            showSuccessOverlay('¡Avance Registrado!', 'La bitácora técnica se ha actualizado correctamente');

                            const timelineContent = document.querySelector('.td-obs-entry-content');
                            const emptyContainer = document.getElementById('empty-notes-container');

                            if (timelineContent) {
                                // Update existing entry content
                                timelineContent.innerHTML = textarea.value.replace(/\n/g, '<br>');
                            } else if (emptyContainer) {
                                // Get tech name from page data attribute or default
                                const techName = document.body.dataset.techName || 'Usuario';

                                // Replace empty state with new timeline
                                const newTimelineHTML = `
                            <div class="td-obs-timeline animate-fadeIn">
                                <div class="td-obs-entry">
                                    <div class="td-obs-entry-node action">
                                        <i class="bi bi-tools"></i>
                                    </div>
                                    <div class="td-obs-entry-card">
                                        <div class="td-obs-entry-meta">
                                            <div class="td-obs-entry-author">
                                                <span class="td-obs-entry-author-name">${techName}</span>
                                                <span class="td-obs-entry-author-role">Técnico</span>
                                            </div>
                                            <span class="td-obs-entry-time">
                                                <i class="bi bi-clock"></i>
                                                Ahora mismo
                                            </span>
                                        </div>
                                        <p class="td-obs-entry-content">${textarea.value.replace(/\n/g, '<br>')}</p>
                                    </div>
                                </div>
                            </div>`;

                                emptyContainer.insertAdjacentHTML('beforebegin', newTimelineHTML);
                                emptyContainer.remove();
                            }
                        } else {
                            if (window.Swal) Swal.fire('Error', data.message || 'Error al guardar', 'error');
                            else alert(data.message || 'Error al guardar');
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
                        submitBtn.innerHTML = originalText;
                    });
            });
        }
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketNotes);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketNotes();
    }
})();
