/**
 * Ticket Files - Delete File Modal
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.TICKET_FILES_LOADED) return;
    window.TICKET_FILES_LOADED = true;

    const getBaseUrl = () => window.BASE_URL || document.body.dataset.baseUrl || '/';

    window.DeleteFileModal = {
        overlay: null,
        archivoId: null,

        init() {
            this.overlay = document.getElementById('deleteFileOverlay');
            if (this.overlay && this.overlay.parentNode !== document.body) {
                document.body.appendChild(this.overlay);
            }

            // Bind confirm button
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => this.confirm());
            }
        },

        open(archivoId, nombreArchivo) {
            if (!this.overlay) this.init();
            if (!this.overlay) return;

            this.archivoId = archivoId;
            const fileNameEl = document.getElementById('deleteFileName');
            if (fileNameEl) fileNameEl.textContent = nombreArchivo;

            this.overlay.style.display = 'flex';
            this.overlay.offsetHeight; // Force reflow
            this.overlay.classList.add('show');
        },

        close() {
            if (!this.overlay) return;
            this.overlay.classList.remove('show');
            setTimeout(() => {
                this.overlay.style.display = 'none';
            }, 250);
        },

        confirm() {
            if (this.archivoId) {
                window.location.href = getBaseUrl() + 'soportes/eliminar_archivo/' + this.archivoId;
            }
        }
    };

    // Global function for onclick handlers
    window.confirmarEliminarArchivo = function (archivoId, nombreArchivo) {
        DeleteFileModal.open(archivoId, nombreArchivo);
    };

    function initTicketFiles() {
        DeleteFileModal.init();
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketFiles);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketFiles();
    }
})();
