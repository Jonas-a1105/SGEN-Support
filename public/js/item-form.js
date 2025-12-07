/**
 * Item Registration Form Logic
 * Handles image preview, inventory value calculation, and form interactions.
 */

const ItemForm = {
    init() {
        this.cacheDOM();
        this.bindEvents();
        this.calculateValue(); // Initial calculation
    },

    cacheDOM() {
        this.form = document.getElementById('itemForm');
        this.imageInput = document.getElementById('imageUpload');
        this.previewContainer = document.getElementById('imagePreviewContainer');
        this.uploadPlaceholder = document.getElementById('uploadPlaceholder');

        this.stockInput = document.getElementById('stock_inicial');
        this.costInput = document.getElementById('valor_compra');
        this.valueContainer = document.getElementById('inventoryValueContainer');
        this.totalValueDisplay = document.getElementById('totalValueDisplay');
        this.formulaDisplay = document.getElementById('formulaDisplay');

        this.submitBtn = document.getElementById('btnSubmit');
        this.submitText = document.getElementById('submitText');
    },

    bindEvents() {
        // Image Upload
        if (this.imageInput) {
            this.imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }

        // Value Calculation
        if (this.stockInput) {
            this.stockInput.addEventListener('input', () => this.calculateValue());
        }
        if (this.costInput) {
            this.costInput.addEventListener('input', () => this.calculateValue());
        }

        // Form Submit
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    },

    handleImageUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validar tipo
        if (!file.type.match('image.*')) {
            alert('Por favor selecciona una imagen válida (PNG, JPG).');
            return;
        }

        // Validar tamaño (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen no debe superar los 5MB.');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            this.uploadPlaceholder.style.display = 'none';
            this.previewContainer.innerHTML = `<img src="${e.target.result}" class="preview-image" alt="Vista previa">`;
            this.previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    },

    calculateValue() {
        // Only calculate if elements exist (might be edit mode or missing inputs)
        if (!this.stockInput || !this.costInput || !this.valueContainer) return;

        const stock = parseFloat(this.stockInput.value) || 0;
        const cost = parseFloat(this.costInput.value) || 0;
        const total = stock * cost;

        if (total > 0) {
            this.totalValueDisplay.textContent = '$' + total.toFixed(2);
            this.formulaDisplay.textContent = `${stock} unidades x $${cost.toFixed(2)}`;
            this.valueContainer.style.display = 'flex';
        } else {
            this.valueContainer.style.display = 'none';
        }
    },

    handleSubmit(e) {
        // Visual feedback only, let default submission happen
        this.submitBtn.disabled = true;
        this.submitText.textContent = 'Guardando...';

        // Optional: simulate delay if AJAX, but we are using standard POST
        // e.preventDefault();
        // setTimeout(() => this.form.submit(), 1000);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ItemForm.init();
});
