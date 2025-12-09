<div id="logoutModal" class="logout-modal-overlay">
    <!-- Backdrop -->
    <div class="logout-backdrop" onclick="LogoutModal.close()"></div>

    <!-- Modal Container -->
    <div class="logout-modal-container" role="dialog" aria-modal="true">
        
        <!-- Decoration Top -->
        <div class="logout-modal-decoration"></div>

        <div class="logout-content">
            
            <!-- Header -->
            <div class="logout-header">
                <div class="logout-icon-wrapper group">
                    <div class="logout-icon-ring"></div>
                    <!-- Icon: LogOut -->
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    
                    <!-- Status Icon -->
                    <div class="logout-status-icon">
                        <!-- Icon: ShieldCheck -->
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </div>
                </div>

                <h3 class="logout-title">¿Finalizar sesión?</h3>
                
                <p class="logout-text">
                    Tu sesión actual se cerrará de forma segura. Tendrás que ingresar tus credenciales para volver.
                </p>
            </div>

            <!-- Actions -->
            <div class="logout-actions">
                <button id="btnConfirmLogout" onclick="LogoutModal.confirm()" class="btn-logout group">
                    <span id="txtLogout">Cerrar Sesión</span>
                    <span id="spinnerLogout" class="animate-spin" style="display: none;">
                        <!-- Icon: Loader2 -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-6.21-14.99"></path>
                        </svg>
                    </span>
                    <!-- Icon: ArrowRight -->
                    <svg id="iconArrowLogout" class="logout-arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="M12 5l7 7-7 7"></path>
                    </svg>
                </button>

                <button onclick="LogoutModal.close()" id="btnCancelLogout" class="btn-cancel">
                    Cancelar y volver
                </button>
            </div>

        </div>

        <!-- Close Button (Absolute) -->
        <button onclick="LogoutModal.close()" class="btn-close-absolute">
            <!-- Icon: X -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

    </div>
</div>

<script>
if (!window.LogoutModal) {
    window.LogoutModal = {
        modal: null,
        btnConfirm: null,
        btnCancel: null,
        txtLogout: null,
        spinnerLogout: null,
        iconArrowLogout: null,
        isLoggingOut: false,

        init: function() {
            this.modal = document.getElementById('logoutModal');
            this.btnConfirm = document.getElementById('btnConfirmLogout');
            this.btnCancel = document.getElementById('btnCancelLogout');
            this.txtLogout = document.getElementById('txtLogout');
            this.spinnerLogout = document.getElementById('spinnerLogout');
            this.iconArrowLogout = document.getElementById('iconArrowLogout');

            // Close on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.modal && this.modal.classList.contains('open') && !this.isLoggingOut) {
                    this.close();
                }
            });
        },

        open: function(e) {
            if (e) e.preventDefault();
            if (!this.modal || !this.modal.isConnected) this.init(); // Re-init if modal lost
            if (!this.modal) return;
            
            this.modal.classList.remove('hidden'); 
            this.modal.style.display = 'flex'; 
            void this.modal.offsetWidth;
            this.modal.classList.add('open');
        },

        close: function() {
            if (this.isLoggingOut) return;
            if (!this.modal) return;
            this.modal.classList.remove('open');
            setTimeout(() => {
                this.modal.style.display = 'none';
            }, 300);
        },

        confirm: function() {
            if (this.isLoggingOut) return;
            this.isLoggingOut = true;

            this.btnConfirm.disabled = true;
            this.btnCancel.disabled = true;
            this.txtLogout.textContent = 'Cerrando sesión...';
            this.spinnerLogout.style.display = 'inline-block';
            this.iconArrowLogout.style.display = 'none';
            this.txtLogout.classList.add('text-slate-200');

            setTimeout(() => {
                window.location.href = '<?= BASE_URL ?>auth/logout';
            }, 1500);
        }
    };
}

// Ensure init is called on every load (even Turbo renders)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.LogoutModal.init());
} else {
    window.LogoutModal.init();
}
</script>
