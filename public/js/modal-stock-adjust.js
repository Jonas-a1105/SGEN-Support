/**
 * Stock Adjustment Modal Script
 * Handles "Recibir Stock" / Add Stock functionality.
 */

if (!window.StockAdjustmentModal) {
    window.StockAdjustmentModal = {
        // State
        currentItem: null,
        formData: {
            type: 'purchase', // purchase, return, audit
            quantity: '',
            reference: '',
            notes: ''
        },
        isSubmitting: false,

        // Elements
        elements: {
            overlay: null,
            articleName: null,

            // Buttons (Type Selector)
            btnTypePurchase: null,
            btnTypeReturn: null,
            btnTypeAudit: null,

            // Math Viz
            displayCurrent: null,
            displayInput: null,
            displayTotal: null,

            // Inputs
            inputQuantity: null,
            inputReference: null,
            inputNotes: null,

            // Actions
            btnCancel: null,
            btnSubmit: null,
            btnClose: null,
            spinner: null,
            submitText: null
        },

        init() {
            this.elements.overlay = document.getElementById('stockAdjustmentModal');
            if (!this.elements.overlay) return;

            // Binds
            this.elements.articleName = document.getElementById('adjModalArticleName');

            this.elements.btnTypePurchase = document.getElementById('adjTypePurchase');
            this.elements.btnTypeReturn = document.getElementById('adjTypeReturn');
            this.elements.btnTypeAudit = document.getElementById('adjTypeAudit');

            this.elements.displayCurrent = document.getElementById('adjMathCurrent');
            this.elements.displayInput = document.getElementById('adjMathInput');
            this.elements.displayTotal = document.getElementById('adjMathTotal');

            this.elements.inputQuantity = document.getElementById('adjInputQuantity');
            this.elements.inputReference = document.getElementById('adjInputReference');
            this.elements.inputNotes = document.getElementById('adjInputNotes');

            this.elements.btnCancel = document.querySelector('.stock-adjust-footer .btn-cancel');
            this.elements.btnClose = document.querySelector('.stock-adjust-close');
            this.elements.btnSubmit = document.getElementById('adjBtnSubmit');
            this.elements.spinner = document.getElementById('adjSpinner');
            this.elements.submitText = document.getElementById('adjSubmitText');

            // Events
            // Events (Nuclear Option)
            this.elements.btnTypePurchase.onclick = () => this.setType('purchase');
            this.elements.btnTypeReturn.onclick = () => this.setType('return');
            this.elements.btnTypeAudit.onclick = () => this.setType('audit');

            this.elements.inputQuantity.oninput = (e) => this.handleQuantityChange(e.target.value);

            this.elements.btnCancel.onclick = () => this.close();
            this.elements.btnClose.onclick = () => this.close();
            this.elements.overlay.onclick = (e) => {
                if (e.target === this.elements.overlay) this.close();
            };

            this.elements.btnSubmit.onclick = () => this.handleSubmit();
        },

        open(itemData) {
            this.currentItem = itemData; // { id, name, code, stock, unit }

            // Reset Form
            this.formData = {
                type: 'purchase',
                quantity: '',
                reference: '',
                notes: ''
            };
            this.isSubmitting = false;

            // UI Updates
            this.elements.articleName.innerHTML = `<span class="font-semibold text-slate-700">${itemData.name}</span>`;
            this.elements.displayCurrent.innerText = itemData.stock;

            this.elements.inputQuantity.value = '';
            this.elements.inputReference.value = '';
            this.elements.inputNotes.value = '';

            this.setType('purchase'); // Reset visual state
            this.handleQuantityChange(''); // Reset math

            // Open Animation
            this.elements.overlay.style.display = 'flex';
            // Reflow
            this.elements.overlay.offsetHeight;
            this.elements.overlay.classList.add('show');

            // Focus
            setTimeout(() => this.elements.inputQuantity.focus(), 100);
        },

        close() {
            this.elements.overlay.classList.remove('show');
            setTimeout(() => {
                this.elements.overlay.style.display = 'none';
            }, 300);
        },

        setType(type) {
            this.formData.type = type;

            // Update UI Classes
            [
                { el: this.elements.btnTypePurchase, id: 'purchase' },
                { el: this.elements.btnTypeReturn, id: 'return' },
                { el: this.elements.btnTypeAudit, id: 'audit' }
            ].forEach(item => {
                if (item.id === type) {
                    item.el.classList.add('active');
                } else {
                    item.el.classList.remove('active');
                }
            });
        },

        handleQuantityChange(val) {
            this.formData.quantity = val;
            const qty = parseInt(val) || 0;
            const current = parseInt(this.currentItem.stock) || 0;
            const total = current + qty;

            // Update Math Viz
            this.elements.displayInput.innerText = `+${qty}`;
            this.elements.displayTotal.innerText = total;

            // Validate Submit Button
            this.elements.btnSubmit.disabled = (qty <= 0);
        },

        handleSubmit() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;

            // UI Loading
            this.elements.submitText.style.display = 'none';
            this.elements.spinner.style.display = 'block';

            // Prepare Form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = (typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '') + 'inventario/movimiento';

            // Map frontend types to backend expectations
            // Assuming backend handles 'entrada' generically, and we put details in 'motivo'
            // Or if backend supports subtype, we add it. 
            // For now: type='entrada', and prompt adds "[Compra] " to reason.

            const typeLabels = {
                'purchase': 'Entrada por Compra',
                'return': 'Devolución de Cliente',
                'audit': 'Ajuste de Inventario (Entrada)'
            };

            const prefix = `[${typeLabels[this.formData.type]}] `;
            const finalMotivo = prefix + (this.elements.inputNotes.value || '') + (this.elements.inputReference.value ? ` (Ref: ${this.elements.inputReference.value})` : '');

            const fields = {
                'item_id': this.currentItem.id,
                'cantidad': this.formData.quantity,
                'tipo': 'ENTRADA', // Always an "Entrance" for this modal
                'motivo': finalMotivo
            };

            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            setTimeout(() => form.submit(), 800);
        }
    };
}

// Initialize on Load (Supports Turbo)
// Initialize on Load (Supports Turbo)
// Global Guard
if (!window._stockModalListenerAttached) {
    document.addEventListener('turbo:load', () => {
        if (window.StockAdjustmentModal) {
            window.StockAdjustmentModal.init();
        }
    });
    window._stockModalListenerAttached = true;
}
