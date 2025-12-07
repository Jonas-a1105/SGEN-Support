/**
 * Inventory Write-Off Modal Script
 * Handles the "Baja / Reportar Daño" modal logic with modern interactions.
 */

const InventoryModal = {
    // State
    currentItem: null,
    isDangerMode: false,

    // DOM Elements
    elements: {
        overlay: null,
        header: null,
        title: null,
        titleIcon: null,
        titleText: null,
        articleName: null,

        stockCurrent: null,
        stockNewGroup: null,
        stockNewValue: null,

        inputQuantity: null,
        inputReason: null,
        inputDeleteFromCatalog: null,

        dangerZoneBox: null,

        btnSubmit: null,
        btnSubmitText: null,
        btnSubmitIcon: null,
        spinner: null
    },

    init() {
        // Create references
        this.elements.overlay = document.getElementById('inventoryWriteOffModal');
        if (!this.elements.overlay) return;

        this.elements.header = this.elements.overlay.querySelector('.inventory-modal-header');
        this.elements.title = this.elements.header.querySelector('.inventory-modal-title');
        this.elements.titleIcon = document.getElementById('invModalTitleIcon');
        this.elements.titleText = document.getElementById('invModalTitleText');
        this.elements.articleName = document.getElementById('invModalArticleName');

        this.elements.stockCurrent = document.getElementById('invModalStockCurrent');
        this.elements.stockNewGroup = document.getElementById('invModalNewStockGroup');
        this.elements.stockNewValue = document.getElementById('invModalNewStockValue');

        this.elements.inputQuantity = document.getElementById('invModalQuantity');
        this.elements.inputReason = document.getElementById('invModalReason');
        this.elements.inputDeleteFromCatalog = document.getElementById('invModalDeleteCheck');

        this.elements.dangerZoneBox = document.querySelector('.danger-zone-box');
        this.elements.btnSubmit = document.getElementById('invModalSubmitBtn');
        this.elements.btnSubmitText = document.getElementById('invModalSubmitText');
        this.elements.spinner = document.getElementById('invModalSpinner');

        // Bind Events
        this.elements.inputQuantity.addEventListener('input', () => this.validate());
        this.elements.inputReason.addEventListener('input', () => this.validate());
        this.elements.inputDeleteFromCatalog.addEventListener('change', (e) => this.toggleDangerMode(e.target.checked));

        this.elements.overlay.querySelector('.inventory-modal-close').addEventListener('click', () => this.close());
        this.elements.overlay.querySelector('.btn-cancel').addEventListener('click', () => this.close());

        // Close on clean background click
        this.elements.overlay.addEventListener('click', (e) => {
            if (e.target === this.elements.overlay) this.close();
        });

        // Quick Tags
        document.querySelectorAll('.tag-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const reason = btn.getAttribute('data-reason');
                const currentVal = this.elements.inputReason.value;
                this.elements.inputReason.value = currentVal ? `${currentVal}, ${reason}` : reason;
                this.validate();
            });
        });

        // Submit
        this.elements.btnSubmit.addEventListener('click', () => this.handleSubmit());
    },

    /**
     * Open the modal for a specific item
     * @param {Object} itemData - { id, name, code, stock, unit }
     */
    open(itemData) {
        this.currentItem = itemData;

        // Reset State
        this.resetForm();

        // Populate Data
        this.elements.articleName.innerHTML = `<span class="font-semibold text-slate-700">${itemData.name}</span> (${itemData.code})`;
        this.elements.stockCurrent.innerText = `${itemData.stock} ${itemData.unit}`;
        this.elements.stockNewGroup.style.display = 'none';

        // Set Max on Input
        this.elements.inputQuantity.max = itemData.stock;

        // Show
        this.elements.overlay.style.display = 'flex';
        // Trigger reflow for animation
        this.elements.overlay.offsetHeight;
        this.elements.overlay.classList.add('show');
    },

    close() {
        this.elements.overlay.classList.remove('show');
        setTimeout(() => {
            this.elements.overlay.style.display = 'none';
        }, 300);
    },

    resetForm() {
        this.isDangerMode = false;
        this.elements.inputQuantity.value = '';
        this.elements.inputReason.value = '';
        this.elements.inputDeleteFromCatalog.checked = false;

        this.elements.inputQuantity.classList.remove('error');
        this.toggleDangerMode(false);
        this.validate();
    },

    toggleDangerMode(isDanger) {
        this.isDangerMode = isDanger;

        if (isDanger) {
            // Header Styles
            this.elements.header.classList.add('danger-mode');
            this.elements.title.classList.add('danger-mode');

            // Icons & Text
            this.elements.titleIcon.classList.remove('bi-box-seam');
            this.elements.titleIcon.classList.add('bi-trash3-fill');
            this.elements.titleIcon.style.color = '#dc2626'; // red

            this.elements.titleText.innerText = "Eliminar Ítem y Stock";

            // Context Box
            this.elements.dangerZoneBox.classList.add('checked');

            // Button styles
            this.elements.btnSubmit.classList.remove('orange');
            this.elements.btnSubmit.classList.add('red');
            this.elements.btnSubmitText.innerText = "Confirmar Eliminación Total";

            // Inputs
            this.elements.inputQuantity.value = this.currentItem.stock;
            this.elements.inputQuantity.setAttribute('readonly', 'true');

            // Auto-fill reason for danger mode
            if (!this.elements.inputReason.value) {
                this.elements.inputReason.value = "Eliminación completa del catálogo";
            }

        } else {
            // Header Styles
            this.elements.header.classList.remove('danger-mode');
            this.elements.title.classList.remove('danger-mode');

            // Icons & Text
            this.elements.titleIcon.classList.add('bi-box-seam');
            this.elements.titleIcon.classList.remove('bi-trash3-fill');
            this.elements.titleIcon.style.color = '#f97316'; // orange

            this.elements.titleText.innerText = "Reportar Daño / Baja";

            // Context Box
            this.elements.dangerZoneBox.classList.remove('checked');

            // Button styles
            this.elements.btnSubmit.classList.add('orange');
            this.elements.btnSubmit.classList.remove('red');
            this.elements.btnSubmitText.innerText = "Confirmar Baja";

            // Inputs
            this.elements.inputQuantity.value = '';
            this.elements.inputQuantity.removeAttribute('readonly');

            // Clear reason if it was auto-filled
            if (this.elements.inputReason.value === "Eliminación completa del catálogo") {
                this.elements.inputReason.value = '';
            }
        }

        this.validate();
    },

    validate() {
        const qty = parseInt(this.elements.inputQuantity.value) || 0;
        const reason = this.elements.inputReason.value.trim();
        const maxStock = parseInt(this.currentItem.stock);

        // 1. Quantity Validation
        // If in danger mode (complete deletion), quantity doesn't matter (can be 0 if stock is 0).
        // Otherwise (partial write-off), must be > 0 and <= maxStock.
        let isValidQty = this.isDangerMode ? true : (qty > 0 && qty <= maxStock);

        // Visual Feedback for Stock
        if (qty > 0) {
            this.elements.stockNewGroup.style.display = 'block';
            const remaining = maxStock - qty;

            this.elements.stockNewValue.innerText = `${Math.max(0, remaining)} ${this.currentItem.unit}`;

            if (remaining < 0) {
                this.elements.stockNewValue.className = 'stock-value invalid';
                this.elements.inputQuantity.classList.add('error');
            } else {
                this.elements.stockNewValue.className = 'stock-value valid';
                this.elements.inputQuantity.classList.remove('error');
            }
        } else {
            // In danger mode if qty is 0, we don't show stock preview or error
            this.elements.stockNewGroup.style.display = 'none';
            this.elements.inputQuantity.classList.remove('error');
        }

        // 2. Reason Validation
        // If in danger mode, we bypass strict check because we auto-filled it or it implies full deletion
        const isValidReason = this.isDangerMode ? true : reason.length >= 5;

        // 3. Overall Validity
        const isValid = isValidQty && isValidReason;

        this.elements.btnSubmit.disabled = !isValid;
    },

    handleSubmit() {
        // Show Spinner
        this.elements.btnSubmitText.style.display = 'none';
        this.elements.spinner.style.display = 'block';

        // Create form logic
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = (typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '') + 'inventario/baja';

        const fields = {
            'item_id': this.currentItem.id,
            'cantidad': this.elements.inputQuantity.value,
            'motivo': this.elements.inputReason.value,
            'eliminar_completo': this.elements.inputDeleteFromCatalog.checked ? '1' : '0'
        };

        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);

        // Simulate network delay for UX then submit
        setTimeout(() => {
            form.submit();
        }, 800);
    }
};

// Initialize on Load
document.addEventListener('DOMContentLoaded', () => {
    InventoryModal.init();
});
