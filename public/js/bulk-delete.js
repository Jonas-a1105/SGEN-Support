/**
 * BulkDelete - Reusable Multi-Select Delete Module
 * 
 * Usage:
 * BulkDelete.init({
 *     containerId: 'itemsContainer',
 *     itemSelector: '.item-row',
 *     itemIdAttribute: 'data-item-id',
 *     deleteUrl: BASE_URL + 'controller/eliminar_masivo',
 *     entityName: 'elementos',
 *     entityNameSingular: 'elemento',
 *     onSuccess: (deletedIds) => { console.log('Deleted:', deletedIds); }
 * });
 */

const BulkDelete = (function () {
    let config = {};
    let selectedIds = new Set();
    let isSelectionMode = false;
    let modalElement = null;

    // Modal HTML Template
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

    function init(options) {
        config = Object.assign({
            containerId: '',
            itemSelector: '',
            itemIdAttribute: 'data-id',
            deleteUrl: '',
            entityName: 'elementos',
            entityNameSingular: 'elemento',
            toggleId: 'bulkModeToggle',
            selectAllId: 'bulkSelectAll',
            selectAllContainerId: 'bulkSelectAllContainer',
            deleteButtonId: 'bulkDeleteBtn',
            countSpanId: 'bulkSelectedCount',
            onSuccess: null,
            onError: null
        }, options);

        const container = document.getElementById(config.containerId);
        if (!container) {
            console.warn('BulkDelete: Container not found:', config.containerId);
            return;
        }

        container.classList.add('bulk-container');
        injectModal();
        bindEvents(container);
    }

    function injectModal() {
        if (document.getElementById('bulkDeleteModal')) {
            modalElement = document.getElementById('bulkDeleteModal');
            return;
        }

        modalElement = document.createElement('div');
        modalElement.id = 'bulkDeleteModal';
        modalElement.className = 'bulk-modal-backdrop';
        modalElement.innerHTML = modalTemplate;
        document.body.appendChild(modalElement);

        // Modal Events
        document.getElementById('bulkModalClose').addEventListener('click', closeModal);
        document.getElementById('bulkModalCancel').addEventListener('click', closeModal);
        modalElement.addEventListener('click', (e) => {
            if (e.target === modalElement) closeModal();
        });

        // Input Validation
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

        // Confirm Delete
        confirmBtn.addEventListener('click', executeDelete);

        // ESC to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalElement.classList.contains('active')) {
                closeModal();
            }
        });
    }

    function bindEvents(container) {
        const toggle = document.getElementById(config.toggleId);
        const selectAll = document.getElementById(config.selectAllId);
        const selectAllContainer = document.getElementById(config.selectAllContainerId);
        const deleteBtn = document.getElementById(config.deleteButtonId);

        // Toggle Selection Mode
        if (toggle) {
            toggle.addEventListener('change', function () {
                isSelectionMode = this.checked;

                if (isSelectionMode) {
                    container.classList.add('selection-active');
                    if (selectAllContainer) selectAllContainer.classList.add('visible');
                } else {
                    container.classList.remove('selection-active');
                    if (selectAllContainer) selectAllContainer.classList.remove('visible');
                    if (selectAll) selectAll.checked = false;
                    clearSelection(container);
                }
            });
        }

        // Select All
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const items = container.querySelectorAll(config.itemSelector);
                items.forEach(item => {
                    const id = item.getAttribute(config.itemIdAttribute);
                    if (this.checked) {
                        selectedIds.add(id);
                        item.classList.add('selected');
                    } else {
                        selectedIds.delete(id);
                        item.classList.remove('selected');
                    }
                });
                updateUI();
            });
        }

        // Item Click (Event Delegation)
        container.addEventListener('click', function (e) {
            if (!isSelectionMode) return;

            const item = e.target.closest(config.itemSelector);
            if (item) {
                e.preventDefault();
                e.stopPropagation();

                const id = item.getAttribute(config.itemIdAttribute);
                if (selectedIds.has(id)) {
                    selectedIds.delete(id);
                    item.classList.remove('selected');
                } else {
                    selectedIds.add(id);
                    item.classList.add('selected');
                }
                updateUI();
            }
        });

        // Delete Button
        if (deleteBtn) {
            deleteBtn.addEventListener('click', openModal);
        }
    }

    function openModal() {
        if (selectedIds.size === 0) return;

        document.getElementById('bulkDeleteCount').textContent =
            `${selectedIds.size} ${selectedIds.size === 1 ? config.entityNameSingular : config.entityName}`;

        modalElement.classList.add('active');
        setTimeout(() => document.getElementById('bulkConfirmInput').focus(), 100);
    }

    function closeModal() {
        modalElement.classList.remove('active');

        const input = document.getElementById('bulkConfirmInput');
        const confirmBtn = document.getElementById('bulkModalConfirm');
        const unlockIcon = document.getElementById('bulkUnlockIcon');

        input.value = '';
        input.classList.remove('match');
        input.disabled = false;
        confirmBtn.disabled = true;
        confirmBtn.classList.remove('active', 'loading');
        unlockIcon.classList.remove('visible');
    }

    function executeDelete() {
        const input = document.getElementById('bulkConfirmInput');
        const confirmBtn = document.getElementById('bulkModalConfirm');

        if (input.value.toUpperCase() !== 'ELIMINAR') return;

        // Loading State
        confirmBtn.disabled = true;
        confirmBtn.classList.add('loading');
        input.disabled = true;

        fetch(config.deleteUrl, {
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
                    const container = document.getElementById(config.containerId);

                    // Animate removal
                    selectedIds.forEach(id => {
                        const el = container.querySelector(`${config.itemSelector}[${config.itemIdAttribute}="${id}"]`);
                        if (el) {
                            el.style.opacity = '0';
                            el.style.transform = 'scale(0.9)';
                            setTimeout(() => el.remove(), 300);
                        }
                    });

                    // Reset
                    setTimeout(() => {
                        closeModal();
                        const toggle = document.getElementById(config.toggleId);
                        const selectAll = document.getElementById(config.selectAllId);
                        const selectAllContainer = document.getElementById(config.selectAllContainerId);

                        if (toggle) toggle.checked = false;
                        if (selectAll) selectAll.checked = false;
                        if (selectAllContainer) selectAllContainer.classList.remove('visible');
                        container.classList.remove('selection-active');

                        if (config.onSuccess) config.onSuccess(Array.from(selectedIds));
                        selectedIds.clear();
                        updateUI();

                        if (window.Toast) Toast.show('success', data.message);
                        else if (window.Swal) Swal.fire('Eliminados', data.message, 'success');
                    }, 400);

                } else {
                    closeModal();
                    if (config.onError) config.onError(data.message);
                    else if (window.Swal) Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                console.error('BulkDelete Error:', err);
                closeModal();
                if (config.onError) config.onError('Error de conexión');
                else if (window.Swal) Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    function clearSelection(container) {
        selectedIds.clear();
        const items = container.querySelectorAll(config.itemSelector + '.selected');
        items.forEach(item => item.classList.remove('selected'));
        updateUI();
    }

    function updateUI() {
        const countSpan = document.getElementById(config.countSpanId);
        const deleteBtn = document.getElementById(config.deleteButtonId);

        if (countSpan) countSpan.textContent = selectedIds.size;
        if (deleteBtn) {
            if (selectedIds.size > 0) {
                deleteBtn.classList.add('visible');
            } else {
                deleteBtn.classList.remove('visible');
            }
        }
    }

    // Public API
    return {
        init,
        getSelectedIds: () => Array.from(selectedIds),
        clearSelection: () => {
            const container = document.getElementById(config.containerId);
            if (container) clearSelection(container);
        }
    };

})();
