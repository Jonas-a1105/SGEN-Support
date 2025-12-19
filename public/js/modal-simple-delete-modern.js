/**
 * SimpleDeleteModal - Exact React Replica (Vanilla JS)
 * Matches strict visual design:
 * - Icons: Mapping Lucide to Bootstrap Icons
 * - Structure: Header (Red-50), Body (P-6), Footer (Actions)
 */

window.SimpleDeleteModal = window.SimpleDeleteModal || (function () {
    let modalElement = null;
    let targetUrl = null;
    let isDeleting = false;

    // Icons (Bootstrap Icons mapping for Lucide)
    // AlertTriangle -> bi-exclamation-triangle
    // X -> bi-x-lg
    // Trash2 -> bi-trash
    // AlertCircle -> bi-exclamation-circle
    // Loader2 -> bi-arrow-repeat (spin)

    const htmlTemplate = `
        <div class="sdm-backdrop"></div>
        <div class="sdm-container">
            
            <!-- Header de Peligro -->
            <div class="sdm-header">
                <div class="sdm-header-left">
                    <div class="sdm-icon-box">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.2rem;"></i>
                    </div>
                    <h3 class="sdm-title" id="sdmTitle">¿Eliminar Elemento?</h3>
                </div>
                <button class="sdm-close-btn" id="sdmCloseBtn">
                    <i class="bi bi-x-lg" style="font-size: 1.2rem;"></i>
                </button>
            </div>

            <div class="sdm-body">
                
                <!-- Mensaje de Contexto -->
                <div>
                    <p class="sdm-context-text" id="sdmMainText">
                        Estás a punto de eliminar este elemento del sistema.
                    </p>
                    
                    <div class="sdm-warning-card">
                        <i class="bi bi-exclamation-circle flex-shrink-0" style="font-size: 1.125rem; margin-top: 0.125rem;"></i>
                        <span class="sdm-warning-card-text" id="sdmWarningText">Esta acción es definitiva. Se perderá todo el historial asociado.</span>
                    </div>
                </div>

                <!-- Footer de Acciones -->
                <div class="sdm-actions">
                    <button class="sdm-btn sdm-btn-cancel" id="sdmCancelBtn">
                        Cancelar
                    </button>
                    
                    <button class="sdm-btn sdm-btn-confirm" id="sdmConfirmBtn">
                        <span id="sdmConfirmContent" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-trash"></i> Confirmar
                        </span>
                    </button>
                </div>

            </div>
        </div>
    `;

    function init() {
        if (document.getElementById('simpleDeleteModal')) {
            modalElement = document.getElementById('simpleDeleteModal');
            return;
        }

        modalElement = document.createElement('div');
        modalElement.id = 'simpleDeleteModal';
        modalElement.className = 'sdm-overlay';
        modalElement.innerHTML = htmlTemplate;
        document.body.appendChild(modalElement);

        bindEvents();
    }

    function bindEvents() {
        // Close Actions
        const close = () => closeModal();

        modalElement.querySelector('#sdmCloseBtn').addEventListener('click', close);
        modalElement.querySelector('#sdmCancelBtn').addEventListener('click', close);

        // Backdrop Click
        modalElement.querySelector('.sdm-backdrop').addEventListener('click', (e) => {
            if (!isDeleting) closeModal();
        });

        // Confirm
        modalElement.querySelector('#sdmConfirmBtn').addEventListener('click', confirmDelete);

        // Escape Key
        if (!window._sdmKeydownBound) {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modalElement && modalElement.classList.contains('active') && !isDeleting) {
                    closeModal();
                }
            });
            window._sdmKeydownBound = true;
        }

        // CRITICAL: Close modal and clear state when Turbo navigates away
        // This prevents accidental deletion if user cancels then navigates
        if (!window._sdmTurboBound) {
            document.addEventListener('turbo:before-visit', () => {
                if (modalElement && modalElement.classList.contains('active') && !isDeleting) {
                    closeModal();
                }
            });
            window._sdmTurboBound = true;
        }
    }

    function confirmDelete() {
        if (!targetUrl || isDeleting) return;

        isDeleting = true;
        updateLoadingState(true);

        // Simulate API/Network delay for visual feedback (like the React example's setTimeout)
        // Then redirect
        setTimeout(() => {
            window.location.href = targetUrl;
            // Note: Page will reload, so no need to reset state usually, 
            // but effectively we just leave it spinning until unload.
        }, 800);
    }

    function updateLoadingState(loading) {
        const btn = modalElement.querySelector('#sdmConfirmBtn');
        const content = modalElement.querySelector('#sdmConfirmContent');

        if (loading) {
            btn.disabled = true;
            content.innerHTML = `<i class="bi bi-arrow-repeat sdm-spin" style="font-size: 1rem;"></i> Eliminando...`;
            modalElement.querySelector('#sdmCancelBtn').disabled = true;
            modalElement.querySelector('#sdmCloseBtn').disabled = true;
        } else {
            btn.disabled = false;
            content.innerHTML = `<i class="bi bi-trash"></i> Confirmar`;
            modalElement.querySelector('#sdmCancelBtn').disabled = false;
            modalElement.querySelector('#sdmCloseBtn').disabled = false;
        }
    }

    function open(url, options = {}) {
        // Turbo Support: Check if element was removed from DOM (detached)
        if (modalElement && !document.body.contains(modalElement)) {
            modalElement = null; // Reset to force re-creation
        }

        if (!modalElement) init();

        targetUrl = url;
        isDeleting = false;
        updateLoadingState(false);

        // Set Content
        const type = options.type || 'Elemento';
        document.getElementById('sdmTitle').textContent = `¿Eliminar ${type}?`;

        const mainText = document.getElementById('sdmMainText');
        if (options.name) {
            mainText.innerHTML = `Estás a punto de eliminar a <strong class="sdm-target-name">${options.name}</strong> del sistema.`;
        } else {
            mainText.innerHTML = `Estás a punto de eliminar este elemento del sistema.`;
        }

        if (options.warning) {
            document.getElementById('sdmWarningText').textContent = options.warning;
        }

        // Show
        modalElement.classList.add('active');
    }

    function closeModal() {
        if (isDeleting) return;
        if (modalElement) {
            modalElement.classList.remove('active');
        }
        // CRITICAL: Clear the target URL to prevent accidental deletion on navigation
        targetUrl = null;
        isDeleting = false;
    }

    return {
        open
    };

})();
