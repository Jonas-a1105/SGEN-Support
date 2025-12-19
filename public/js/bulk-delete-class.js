/**
 * BulkDeleteManager - Class-based Multi-Select Delete Module
 * Allows multiple instances on the same page.
 */
// Prevent re-declaration
if (!window.BulkDeleteManager) {

    class BulkDeleteManager {
        constructor(options) {
            this.config = Object.assign({
                containerId: '',
                itemSelector: '',
                itemIdAttribute: 'data-id',
                deleteUrl: '',
                entityName: 'elementos',
                entityNameSingular: 'elemento',
                toggleId: '',
                selectAllId: '',
                selectAllContainerId: '',
                deleteButtonId: '',
                countSpanId: '',
                onSuccess: null,
                onError: null
            }, options);

            this.selectedIds = new Set();
            this.isSelectionMode = false;
            this.modalElement = null; // Can share modal or create separate? Sharing is fine if managed.
            // Actually, safer to let each instance create its own modal or share a global one.
            // For simplicity, let's look for existing modal first.

            this.init();
        }

        init() {
            const container = document.getElementById(this.config.containerId);
            if (!container) {
                console.warn('BulkDeleteManager: Container not found:', this.config.containerId);
                return;
            }

            // Element-Level Idempotency for Turbo Cache
            if (container.dataset.bulkInitialized === 'true') {
                return;
            }
            container.dataset.bulkInitialized = 'true';

            container.classList.add('bulk-container');
            this.injectModal();
            this.bindEvents(container);
        }

        injectModal() {
            // Shared Modal Logic - Check if already exists in DOM
            if (document.getElementById('bulkDeleteModal')) {
                this.modalElement = document.getElementById('bulkDeleteModal');
                return;
            }

            // Create Modal
            const modalTemplate = `
            <div class="bulk-modal-content">
                <div class="bulk-modal-header">
                    <div class="bulk-modal-header-left">
                        <div class="bulk-modal-icon-box">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h3 class="bulk-modal-title">Eliminar Selección</h3>
                    </div>
                    <button class="bulk-modal-close" id="bulkModalClose">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="bulk-modal-body">
                    <p class="bulk-modal-context">
                        Estás a punto de eliminar permanentemente <strong class="bulk-modal-count" id="bulkDeleteCount">0 elementos</strong>.
                    </p>
                    <div class="bulk-modal-warning">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <div>
                            <strong>Advertencia:</strong> Esta acción no se puede deshacer.
                        </div>
                    </div>
                    <div class="bulk-modal-input-group">
                        <label class="bulk-modal-input-label">
                            PARA CONFIRMAR, ESCRIBE "ELIMINAR" ABAJO
                        </label>
                        <div class="bulk-modal-input-wrapper">
                            <input type="text" class="bulk-modal-input" id="bulkConfirmInput" placeholder="ELIMINAR" autocomplete="off">
                            <div class="bulk-modal-unlock" id="bulkUnlockIcon">
                                <i class="bi bi-unlock-fill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bulk-modal-footer">
                        <button class="bulk-modal-cancel" id="bulkModalCancel">Cancelar</button>
                        <button class="bulk-modal-confirm" id="bulkModalConfirm" disabled>
                            <span class="normal-state"><i class="bi bi-trash"></i> Eliminar</span>
                            <span class="loading-state"><span class="spinner-border spinner-border-sm"></span> Eliminando...</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

            this.modalElement = document.createElement('div');
            this.modalElement.id = 'bulkDeleteModal';
            this.modalElement.className = 'bulk-modal-backdrop';
            this.modalElement.innerHTML = modalTemplate;
            document.body.appendChild(this.modalElement);

            // Global Modal Events (Close) - Only bind once usually, or safe to rebind?
            // If shared modal, we need to be careful about not binding multiple listeners to the *modal buttons*.
            // Better to attach instance-specific listeners when opening the modal?
            // Or keep the modal "dumb" and drive it from the instance.

            // Let's bind the CLOSE events globally as they just hide it.
            const closeModal = () => this.closeModal(); // This calls instance method, but closes global modal

            document.getElementById('bulkModalClose').addEventListener('click', () => {
                document.querySelector('.bulk-modal-backdrop.active')?.classList.remove('active');
            });
            document.getElementById('bulkModalCancel').addEventListener('click', () => {
                document.querySelector('.bulk-modal-backdrop.active')?.classList.remove('active');
            });

            // Input validation is global logic
            const input = document.getElementById('bulkConfirmInput');
            const confirmBtn = document.getElementById('bulkModalConfirm');
            const unlockIcon = document.getElementById('bulkUnlockIcon');

            input.addEventListener('input', function () {
                const val = this.value.toUpperCase();
                if (val === 'ELIMINAR') {
                    this.classList.add('match');
                    confirmBtn.classList.add('active');
                    confirmBtn.disabled = false;
                    unlockIcon.classList.add('visible');
                } else {
                    this.classList.remove('match');
                    confirmBtn.classList.remove('active');
                    confirmBtn.disabled = true;
                    unlockIcon.classList.remove('visible');
                }
            });
        }

        bindEvents(container) {
            const toggle = document.getElementById(this.config.toggleId);
            const selectAll = document.getElementById(this.config.selectAllId);
            const selectAllContainer = document.getElementById(this.config.selectAllContainerId);
            const deleteBtn = document.getElementById(this.config.deleteButtonId);

            // Toggle Selection Mode
            if (toggle) {
                toggle.addEventListener('change', (e) => {
                    this.isSelectionMode = e.target.checked;
                    if (this.isSelectionMode) {
                        container.classList.add('selection-active');
                        if (selectAllContainer) selectAllContainer.classList.add('visible');
                    } else {
                        container.classList.remove('selection-active');
                        if (selectAllContainer) selectAllContainer.classList.remove('visible');
                        if (selectAll) selectAll.checked = false;
                        this.clearSelection(container);
                    }
                });
            }

            // Select All
            if (selectAll) {
                selectAll.addEventListener('change', (e) => {
                    const items = container.querySelectorAll(this.config.itemSelector);
                    items.forEach(item => {
                        const id = item.getAttribute(this.config.itemIdAttribute);
                        if (e.target.checked) {
                            this.selectedIds.add(id);
                            item.classList.add('selected');
                        } else {
                            this.selectedIds.delete(id);
                            item.classList.remove('selected');
                        }
                    });
                    this.updateUI();
                });
            }

            // Item Click
            container.addEventListener('click', (e) => {
                if (!this.isSelectionMode) return;
                const item = e.target.closest(this.config.itemSelector);
                if (item) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = item.getAttribute(this.config.itemIdAttribute);
                    if (this.selectedIds.has(id)) {
                        this.selectedIds.delete(id);
                        item.classList.remove('selected');
                    } else {
                        this.selectedIds.add(id);
                        item.classList.add('selected');
                    }
                    this.updateUI();
                }
            });

            // Delete Button
            if (deleteBtn) {
                deleteBtn.addEventListener('click', () => this.openModal());
            }
        }

        openModal() {
            if (this.selectedIds.size === 0) return;

            // Update Modal Content with current instance context
            document.getElementById('bulkDeleteCount').textContent =
                `${this.selectedIds.size} ${this.selectedIds.size === 1 ? this.config.entityNameSingular : this.config.entityName}`;

            // Bind Confirm Action specifically for this instance
            const confirmBtn = document.getElementById('bulkModalConfirm');

            // Remove old listeners (Hack: clone to clear)
            const newBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

            // Check input state again just in case
            const input = document.getElementById('bulkConfirmInput');
            if (input.value.toUpperCase() === 'ELIMINAR') {
                newBtn.classList.add('active');
                newBtn.disabled = false;
            } else {
                newBtn.classList.remove('active');
                newBtn.disabled = true;
            }

            newBtn.addEventListener('click', () => this.executeDelete());

            this.modalElement.classList.add('active');
            setTimeout(() => document.getElementById('bulkConfirmInput').focus(), 100);
        }

        closeModal() {
            this.modalElement.classList.remove('active');
            const input = document.getElementById('bulkConfirmInput');
            const unlockIcon = document.getElementById('bulkUnlockIcon');

            // Reset inputs
            input.value = '';
            input.classList.remove('match');
            input.disabled = false;
            unlockIcon.classList.remove('visible');
        }

        executeDelete() {
            // Find current button
            const confirmBtn = document.getElementById('bulkModalConfirm');
            const input = document.getElementById('bulkConfirmInput');

            if (input.value.toUpperCase() !== 'ELIMINAR') return;

            confirmBtn.disabled = true;
            confirmBtn.classList.add('loading');
            input.disabled = true;

            fetch(this.config.deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: Array.from(this.selectedIds) })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById(this.config.containerId);
                        this.selectedIds.forEach(id => {
                            const el = container.querySelector(`${this.config.itemSelector}[${this.config.itemIdAttribute}="${id}"]`);
                            if (el) {
                                el.style.opacity = '0';
                                el.style.transform = 'scale(0.9)';
                                setTimeout(() => el.remove(), 300);
                            }
                        });

                        setTimeout(() => {
                            this.closeModal();
                            // Reset toggles
                            const toggle = document.getElementById(this.config.toggleId);
                            const selectAll = document.getElementById(this.config.selectAllId);
                            const selectAllContainer = document.getElementById(this.config.selectAllContainerId);

                            if (toggle) toggle.checked = false;
                            if (selectAll) selectAll.checked = false;
                            if (selectAllContainer) selectAllContainer.classList.remove('visible');
                            container.classList.remove('selection-active');

                            this.selectedIds.clear();
                            this.updateUI();

                            if (this.config.onSuccess) this.config.onSuccess(data);

                            if (window.Toast) Toast.show('success', data.message);
                            if (window.Toast) Toast.show('success', data.message);
                            else if (window.Swal) Swal.fire({
                                title: 'Eliminados',
                                text: data.message,
                                icon: 'success',
                                customClass: {
                                    container: 'desktop-modal-container',
                                    popup: 'desktop-modal-popup'
                                },
                                backdrop: 'rgba(0,0,0,0)'
                            });
                        }, 400);
                    } else {
                        this.closeModal();
                        if (window.Swal) Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            customClass: {
                                container: 'desktop-modal-container',
                                popup: 'desktop-modal-popup'
                            },
                            backdrop: 'rgba(0,0,0,0)'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.closeModal();
                    if (window.Swal) Swal.fire({
                        title: 'Error',
                        text: 'Error de conexión',
                        icon: 'error',
                        customClass: {
                            container: 'desktop-modal-container',
                            popup: 'desktop-modal-popup'
                        },
                        backdrop: 'rgba(0,0,0,0)'
                    });
                });
        }

        clearSelection(container) {
            this.selectedIds.clear();
            const items = container.querySelectorAll(this.config.itemSelector + '.selected');
            items.forEach(item => item.classList.remove('selected'));
            this.updateUI();
        }

        updateUI() {
            const countSpan = document.getElementById(this.config.countSpanId);
            const deleteBtn = document.getElementById(this.config.deleteButtonId);
            if (countSpan) countSpan.textContent = this.selectedIds.size;
            if (deleteBtn) {
                if (this.selectedIds.size > 0) deleteBtn.classList.add('visible');
                else deleteBtn.classList.remove('visible');
            }

            window.BulkDeleteManager = BulkDeleteManager;
        }
    }
    window.BulkDeleteManager = BulkDeleteManager;
}
