/**
 * Ticket Detail V2 - Consolidated JavaScript
 * Turbo-compatible version.
 */
(function () {
    // Idempotency guard
    if (window.TICKET_DETAIL_V2_LOADED) return;
    window.TICKET_DETAIL_V2_LOADED = true;

    /* ============================================================================
       UPLOAD FILE MODAL
       ============================================================================ */
    window.UploadFileModal = {
        overlay: null,

        init() {
            this.overlay = document.getElementById('uploadFileOverlay');
            if (this.overlay && this.overlay.parentNode !== document.body) {
                document.body.appendChild(this.overlay);
            }
        },

        open() {
            if (!this.overlay) this.init();
            if (!this.overlay) return;

            this.overlay.style.display = 'flex';
            this.overlay.offsetHeight;
            this.overlay.classList.add('show');
        },

        close() {
            if (!this.overlay) return;
            this.overlay.classList.remove('show');
            setTimeout(() => {
                this.overlay.style.display = 'none';
            }, 300);
        }
    };

    /* ============================================================================
       CONFIRMATION MODAL (Generic)
       ============================================================================ */
    window.ConfirmModal = {
        overlay: null,
        actionUrl: null,

        init() {
            this.overlay = document.getElementById('confirmOverlay');
            if (this.overlay && this.overlay.parentNode !== document.body) {
                document.body.appendChild(this.overlay);
            }
        },

        close() {
            if (!this.overlay) return;
            this.overlay.classList.remove('show');
            setTimeout(() => {
                this.overlay.style.display = 'none';
            }, 300);
        },

        confirm() {
            if (this.actionUrl) {
                window.location.href = this.actionUrl;
            }
        }
    };

    window.showConfirmOverlay = function (titulo, mensaje, tipo, botonTexto, url) {
        let overlay = document.getElementById('confirmOverlay');
        if (!overlay) {
            ConfirmModal.init();
            overlay = ConfirmModal.overlay;
        }

        if (overlay && overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }

        const titleEl = document.getElementById('confirmTitle');
        const messageEl = document.getElementById('confirmMessage');
        if (titleEl) titleEl.textContent = titulo;
        if (messageEl) messageEl.textContent = mensaje;

        const iconDiv = document.getElementById('confirmIcon');
        if (iconDiv) {
            iconDiv.className = 'co-icon ' + (tipo || 'info');
            const iconMap = {
                'warning': 'bi-exclamation-triangle',
                'success': 'bi-check-circle',
                'info': 'bi-info-circle',
                'error': 'bi-x-circle'
            };
            iconDiv.innerHTML = '<i class="bi ' + (iconMap[tipo] || 'bi-question-circle') + '"></i>';
        }

        const confirmBtn = document.getElementById('confirmActionBtn');
        if (confirmBtn) {
            confirmBtn.textContent = botonTexto || 'Confirmar';
            confirmBtn.className = 'co-btn co-btn-confirm ' + (tipo || 'info');
            ConfirmModal.actionUrl = url;
            confirmBtn.onclick = () => ConfirmModal.confirm();
        }

        if (overlay) {
            overlay.style.display = 'flex';
            overlay.offsetHeight;
            overlay.classList.add('show');
        }
    };

    /* ============================================================================
       MODERN DELETE MODAL (For Comments)
       ============================================================================ */
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

    window.showModernDeleteModal = function (title, message, callback) {
        let overlay = document.getElementById('modernDeleteModal');
        if (!overlay) {
            ModernDeleteModal.init();
            overlay = ModernDeleteModal.overlay;
        }
        if (overlay && overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }

        const titleEl = document.getElementById('mdmTitle');
        const messageEl = document.getElementById('mdmMessage');
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;

        ModernDeleteModal.callback = callback;
        const confirmBtn = document.getElementById('mdmConfirmBtn');
        if (confirmBtn) confirmBtn.onclick = () => ModernDeleteModal.confirm();

        if (overlay) {
            overlay.style.display = 'flex';
            overlay.offsetHeight;
            overlay.classList.add('show');
        }
    };

    window.showBulkDeleteModal = function (count, callback) {
        const title = `¿Eliminar ${count} comentario${count > 1 ? 's' : ''}?`;
        showModernDeleteModal(title, 'Esta acción no se puede deshacer', callback);
    };

    /* ============================================================================
       INITIALIZATION
       ============================================================================ */
    function initTicketDetail() {
        // Check if we're on ticket detail page
        const confirmOverlay = document.getElementById('confirmOverlay');
        const uploadOverlay = document.getElementById('uploadFileOverlay');
        if (!confirmOverlay && !uploadOverlay) return;

        // Init Modals
        window.UploadFileModal.init();
        window.ConfirmModal.init();
        window.ModernDeleteModal.init();

        // Upload File Modal Interception (using delegation)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-bs-target="#modalSubirArchivo"]');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                window.UploadFileModal.open();
            }
        });

        // Tab Activation from Hash
        if (window.location.hash) {
            const hash = window.location.hash;
            const triggerEl = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
            if (triggerEl && window.bootstrap) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Confirmation Buttons (Delegation)
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-confirmar');
            if (!btn) return;

            e.preventDefault();

            const url = btn.getAttribute('data-url');
            const titulo = btn.getAttribute('data-titulo');
            const texto = btn.getAttribute('data-texto');
            const icono = btn.getAttribute('data-icono');
            const boton = btn.getAttribute('data-boton');

            showConfirmOverlay(titulo, texto, icono, boton, url);
        });

        // Legacy modal fix
        const modalArchivo = document.getElementById('modalSubirArchivo');
        if (modalArchivo) {
            document.body.appendChild(modalArchivo);
            modalArchivo.style.position = 'fixed';
            modalArchivo.style.zIndex = '9999999';
            modalArchivo.style.top = '0';
            modalArchivo.style.left = '0';
            modalArchivo.style.width = '100%';
            modalArchivo.style.height = '100%';

            modalArchivo.addEventListener('show.bs.modal', function () {
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => {
                        document.body.appendChild(backdrop);
                        backdrop.style.zIndex = '9999998';
                    });
                }, 10);
            });
        }
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketDetail);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketDetail();
    }
})();
