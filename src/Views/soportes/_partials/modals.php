<?php
/**
 * Modals para la vista de Detalle de Ticket
 * Estilo consistente con el modal de "Asignar Técnico"
 */
?>

<!-- ===================================================================
     MODAL EDITAR FECHA DE CIERRE
     Usando overlay personalizado como el modal de Asignar Técnico
     =================================================================== -->
<div id="fechaCierreOverlay" class="fc-overlay" style="display: none;">
    <div class="fc-modal">
        <!-- Header -->
        <div class="fc-header">
            <div class="fc-header-content">
                <div class="fc-icon-box">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="fc-title-box">
                    <h3 class="fc-title">Editar Fecha de Cierre</h3>
                    <p class="fc-subtitle">Modificar la fecha y hora de cierre del ticket #<?= $soporte->id ?></p>
                </div>
            </div>
            <button type="button" class="fc-close-btn" onclick="FechaCierreModal.close()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <form action="<?= BASE_URL ?>soportes/actualizar_fecha_cierre" method="POST">
            <div class="fc-body">
                <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                
                <!-- Warning Alert -->
                <div class="fc-alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Esta acción actualizará la fecha de cierre del ticket. Use con precaución.</span>
                </div>
                
                <!-- Date Input -->
                <div class="fc-input-group">
                    <label class="fc-label">Nueva Fecha de Cierre</label>
                    <input type="datetime-local" 
                           class="fc-input" 
                           name="nueva_fecha_cierre" 
                           value="<?= $soporte->fecha_cierre ? date('Y-m-d\TH:i', strtotime($soporte->fecha_cierre)) : '' ?>" 
                           required>
                    <p class="fc-hint">Seleccione la nueva fecha y hora de cierre del ticket.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="fc-footer">
                <button type="button" class="fc-btn fc-btn-cancel" onclick="FechaCierreModal.close()">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="submit" class="fc-btn fc-btn-confirm">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================
     ESTILOS PARA MODALES PERSONALIZADOS
     Basados en modal-assign-tech.css
     =================================================================== -->
<style>
/* Overlay Base (compartido) */
.fc-overlay, .mr-overlay {
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
.fc-overlay.show, .mr-overlay.show {
    opacity: 1;
}

/* Modal Container Base */
.fc-modal, .mr-modal {
    background: white;
    width: 100%;
    max-width: 480px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fc-overlay.show .fc-modal,
.mr-overlay.show .mr-modal {
    transform: scale(1) translateY(0);
}

/* Header */
.fc-header, .mr-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background-color: rgba(248, 250, 252, 0.5);
}
.fc-header-content, .mr-header-content { 
    display: flex; 
    gap: 0.875rem; 
    align-items: center; 
}
.fc-icon-box, .mr-icon-box {
    width: 42px; 
    height: 42px;
    border-radius: 10px;
    display: flex; 
    align-items: center; 
    justify-content: center;
    font-size: 1.125rem;
}
.fc-icon-box {
    background-color: #dbeafe;
    color: #2563eb;
}
.mr-icon-box {
    background-color: #d1fae5;
    color: #059669;
}
.fc-title, .mr-title { 
    margin: 0; 
    font-size: 1.125rem; 
    font-weight: 700; 
    color: #0f172a; 
}
.fc-subtitle, .mr-subtitle { 
    margin: 0.125rem 0 0 0; 
    font-size: 0.8125rem; 
    color: #64748b; 
}
.fc-close-btn, .mr-close-btn {
    background: none; 
    border: none; 
    font-size: 1.125rem; 
    color: #94a3b8; 
    cursor: pointer;
    padding: 0.375rem;
    border-radius: 6px;
    transition: all 0.2s;
}
.fc-close-btn:hover, .mr-close-btn:hover { 
    color: #475569; 
    background-color: #f1f5f9;
}

/* Body */
.fc-body, .mr-body { 
    padding: 1.5rem; 
}

/* Alert Box */
.fc-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.75rem 1rem;
    background-color: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 0.625rem;
    color: #1e40af;
    font-size: 0.8125rem;
    margin-bottom: 1.25rem;
}
.fc-alert i {
    margin-top: 0.125rem;
}

/* Input Group */
.fc-input-group, .mr-input-group {
    margin-bottom: 1rem;
}
.fc-label, .mr-label { 
    display: block; 
    font-size: 0.75rem; 
    font-weight: 600; 
    color: #475569; 
    text-transform: uppercase; 
    letter-spacing: 0.05em; 
    margin-bottom: 0.5rem; 
}
.fc-input, .mr-input, .mr-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.625rem;
    border: 1px solid #e2e8f0;
    font-size: 0.875rem;
    color: #1e293b;
    background-color: #f8fafc;
    transition: all 0.2s;
}
.fc-input:focus, .mr-input:focus, .mr-select:focus {
    outline: none; 
    border-color: #3b82f6; 
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
    background-color: white;
}
.fc-hint, .mr-hint { 
    font-size: 0.75rem; 
    color: #94a3b8; 
    margin-top: 0.5rem;
    margin-bottom: 0;
}

/* Footer */
.fc-footer, .mr-footer {
    padding: 1rem 1.5rem;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex; 
    gap: 0.75rem; 
    justify-content: flex-end;
}
.fc-btn, .mr-btn {
    padding: 0.625rem 1.25rem; 
    border-radius: 0.5rem; 
    font-size: 0.875rem; 
    font-weight: 600; 
    cursor: pointer; 
    border: none; 
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}
.fc-btn-cancel, .mr-btn-cancel { 
    background-color: white; 
    border: 1px solid #e2e8f0; 
    color: #475569; 
}
.fc-btn-cancel:hover, .mr-btn-cancel:hover { 
    background-color: #f1f5f9; 
    border-color: #cbd5e1; 
    color: #1e293b; 
}
.fc-btn-confirm { 
    background-color: #2563eb; 
    color: white; 
}
.fc-btn-confirm:hover { 
    background-color: #1d4ed8; 
}
.mr-btn-confirm { 
    background-color: #059669; 
    color: white; 
}
.mr-btn-confirm:hover { 
    background-color: #047857; 
}
</style>

<!-- ===================================================================
     JAVASCRIPT PARA MODALES
     =================================================================== -->
<script>
// Modal Fecha de Cierre
window.FechaCierreModal = {
    overlay: null,
    
    init() {
        this.overlay = document.getElementById('fechaCierreOverlay');
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

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
    FechaCierreModal.init();
});

// Función global para abrir el modal de fecha de cierre
function abrirModalFechaCierre() {
    FechaCierreModal.open();
}
</script>
