/**
 * Ticket Info - Materials/Repuestos Modal
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.TICKET_INFO_LOADED) return;
    window.TICKET_INFO_LOADED = true;

    window.MaterialesModal = {
        overlay: null,

        init() {
            this.overlay = document.getElementById('materialesOverlay');
            if (this.overlay && this.overlay.parentNode !== document.body) {
                document.body.appendChild(this.overlay);
            }
        },

        open() {
            if (!this.overlay) this.init();
            if (!this.overlay) return;

            this.overlay.style.display = 'flex';
            this.overlay.offsetHeight; // Force reflow
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

    function initTicketInfo() {
        MaterialesModal.init();
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketInfo);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketInfo();
    }
})();
