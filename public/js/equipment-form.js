/**
 * Equipment Registration Wizard Logic
 */

const EquipmentWizard = {
    activeStep: 0,
    totalSteps: 4,

    bindEvents() {
        if (!this.wrapper) return;

        // Navigation
        if (this.btnPrev) this.btnPrev.addEventListener('click', () => this.prevStep());
        if (this.btnNext) this.btnNext.addEventListener('click', () => this.nextStep());

        // Step buttons (Direct navigation)
        this.steps.forEach((btn, index) => {
            btn.addEventListener('click', () => this.goToStep(index));
        });

        // Dynamic Field: Tipo Change
        if (this.typeSelect) {
            this.typeSelect.addEventListener('change', () => {
                this.handleOtherType();
                this.updateFieldsByType();
            });
        }

        if (this.photoBox && this.imageInput) {
            this.photoBox.addEventListener('click', (e) => {
                // Don't trigger if clicking the remove button
                if (e.target.closest('#removePhotoBtn')) return;
                this.imageInput.click();
            });
            this.imageInput.addEventListener('change', (e) => this.handleImagePreview(e));
        }

        if (this.removePhotoBtn) {
            this.removePhotoBtn.addEventListener('click', () => this.removePhoto());
        }
    },

    cacheDOM() {
        this.wrapper = document.querySelector('.eq-wizard-wrapper');
        if (!this.wrapper) return;

        this.steps = this.wrapper.querySelectorAll('.eq-step-btn');
        this.panes = this.wrapper.querySelectorAll('.step-pane');

        this.btnPrev = this.wrapper.querySelector('.btn-nav-prev');
        this.btnNext = this.wrapper.querySelector('.btn-nav-next');
        this.btnSave = this.wrapper.querySelector('.btn-nav-save');

        this.stepLabel = this.wrapper.querySelector('.eq-step-title');
        this.stepCount = this.wrapper.querySelector('.eq-step-badge');

        // Dynamic Elements
        this.typeSelect = document.getElementById('tipo');
        this.otherTypeInput = document.getElementById('tipo_otro');
        this.otherTypeContainer = document.getElementById('tipo_otro_container');

        // Photo Elements
        this.photoBox = document.getElementById('photoBox');
        this.imageInput = document.getElementById('imagenInput');
        this.photoPreview = document.getElementById('photoPreview');
        this.removePhotoBtn = document.getElementById('removePhotoBtn');
        this.eliminarImagenInput = document.getElementById('eliminarImagenInput');
        this.photoText = document.getElementById('photoText');
    },

    init() {
        this.cacheDOM();
        this.bindEvents();
        this.updateStep();
        this.handleOtherType();
        this.updateFieldsByType();
    },

    handleOtherType() {
        if (!this.typeSelect || !this.otherTypeContainer) return;

        if (this.typeSelect.value === 'otro') {
            this.otherTypeContainer.style.display = 'block';
            if (this.otherTypeInput) this.otherTypeInput.setAttribute('required', 'required');
        } else {
            this.otherTypeContainer.style.display = 'none';
            if (this.otherTypeInput) {
                this.otherTypeInput.removeAttribute('required');
                this.otherTypeInput.classList.remove('border-red-500'); // Clear error style if hidden
                this.otherTypeInput.style.borderColor = '';
            }
        }
    },

    updateFieldsByType() {
        if (!this.typeSelect) return;
        const type = this.typeSelect.value;
        const hardwarePane = document.querySelector('.step-pane[data-step="1"]');
        const cpuInput = hardwarePane ? hardwarePane.querySelector('input[name="procesador"]') : null;
        const ramInput = hardwarePane ? hardwarePane.querySelector('input[name="memoria_ram"]') : null;
        const hddInput = hardwarePane ? hardwarePane.querySelector('input[name="almacenamiento"]') : null;
        const osInput = hardwarePane ? hardwarePane.querySelector('input[name="sistema_operativo"]') : null;

        // Types that require full hardware specs
        const computerTypes = ['computadora', 'laptop', 'servidor', 'portatil'];
        const isComputer = computerTypes.includes(type);

        if (hardwarePane) {
            const inputs = [cpuInput, ramInput, hddInput, osInput];

            if (isComputer) {
                // Show fields
                hardwarePane.querySelectorAll('.eq-field').forEach(div => div.style.display = 'block');

                // Update Callout
                const callout = hardwarePane.querySelector('.eq-callout');
                if (callout) callout.style.display = 'flex';

                // Optional: Make them required? Currently they are optional in backend, so we keep them optional or strict based on preference.
                // Keeping them optional for flexibility but visible.
            } else {
                // Hide fields for printers, monitors, etc.
                // Or maybe just show specific ones? For now, hiding these specific CP/RAM fields
                if (type === 'impresora') {
                    // Maybe show only connectivity? 
                    // For simplicity, hiding the hardware fields but keeping the pane accessible (maybe empty or with a message)
                }

                // Hide all hardware inputs
                hardwarePane.querySelectorAll('.eq-field').forEach(div => div.style.display = 'none');

                // Update Callout to explain
                const callout = hardwarePane.querySelector('.eq-callout');
                if (callout) callout.style.display = 'none';

                // Add a message if not exists
                let msg = hardwarePane.querySelector('.no-specs-msg');
                if (!msg) {
                    msg = document.createElement('div');
                    msg.className = 'no-specs-msg eq-empty-state';
                    msg.innerHTML = `
                        <div class="eq-empty-icon"><i class="bi bi-info-circle"></i></div>
                        <p>No se requieren especificaciones de hardware (CPU, RAM) para este tipo de equipo.</p>
                        <p class="text-sm text-gray-500">Puede continuar al siguiente paso.</p>
                    `;
                    msg.style.textAlign = 'center';
                    msg.style.padding = '3rem';
                    msg.style.color = '#64748b';
                    hardwarePane.appendChild(msg);
                }
                msg.style.display = 'block';
            }

            // If showing fields, hide message
            if (isComputer) {
                const msg = hardwarePane.querySelector('.no-specs-msg');
                if (msg) msg.style.display = 'none';
            }
        }
    },

    handleImagePreview(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Basic validation
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen es demasiado grande (Máximo 2MB)');
            e.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            if (this.photoPreview) {
                this.photoPreview.style.backgroundImage = `url(${event.target.result})`;
                this.photoPreview.style.backgroundSize = 'cover';
                this.photoPreview.style.backgroundPosition = 'center';
                this.photoPreview.innerHTML = ''; // Remove the icon

                if (this.removePhotoBtn) this.removePhotoBtn.style.display = 'flex';
                if (this.eliminarImagenInput) this.eliminarImagenInput.value = '0';
                if (this.photoText) this.photoText.innerText = 'Cambiar foto';
            }
        };
        reader.readAsDataURL(file);
    },

    removePhoto() {
        if (this.photoPreview) {
            this.photoPreview.style.backgroundImage = 'none';
            this.photoPreview.innerHTML = '<i class="bi bi-camera" style="font-size: 1.5rem;"></i>';
        }
        if (this.imageInput) this.imageInput.value = '';
        if (this.removePhotoBtn) this.removePhotoBtn.style.display = 'none';
        if (this.eliminarImagenInput) this.eliminarImagenInput.value = '1';
        if (this.photoText) this.photoText.innerText = 'Subir foto';
    },

    goToStep(index) {
        if (index < 0 || index >= this.totalSteps) return;
        // Validate before moving forward? For now, we allow free navigation but validate on Submit
        this.activeStep = index;
        this.updateStep();
    },

    nextStep() {
        // Validate current step before moving
        if (!this.validateStep(this.activeStep)) {
            // Flash or focus error
            return;
        }

        if (this.activeStep < this.totalSteps - 1) {
            this.activeStep++;
            this.updateStep();
        }
    },

    prevStep() {
        if (this.activeStep > 0) {
            this.activeStep--;
            this.updateStep();
        }
    },

    validateStep(stepIndex) {
        const pane = this.panes[stepIndex];
        const inputs = pane.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500'); // Assuming Tailwind utility or add CSS
                input.style.borderColor = 'var(--eq-red-500)';

                // Remove error on input
                input.addEventListener('input', function () {
                    this.style.borderColor = '';
                }, { once: true });
            }
        });

        return isValid;
    },

    updateStep() {
        // 1. Update Buttons State
        this.steps.forEach((btn, index) => {
            btn.classList.remove('active', 'completed');
            if (index === this.activeStep) {
                btn.classList.add('active');
            } else if (index < this.activeStep) {
                btn.classList.add('completed');
            }
        });

        // 2. Show correct Pane
        this.panes.forEach((pane, index) => {
            if (index === this.activeStep) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });

        // 3. Update Header
        const titles = [
            'Información Básica',
            'Especificaciones',
            'Ubicación',
            'Adquisición'
        ];
        if (this.stepLabel) this.stepLabel.innerText = titles[this.activeStep];
        if (this.stepCount) this.stepCount.innerText = `Paso ${this.activeStep + 1} de ${this.totalSteps}`;

        // 4. Update Footer Buttons
        if (this.activeStep === 0) {
            if (this.btnPrev) this.btnPrev.disabled = true;
        } else {
            if (this.btnPrev) this.btnPrev.disabled = false;
        }

        if (this.activeStep === this.totalSteps - 1) {
            if (this.btnNext) this.btnNext.style.display = 'none';
            if (this.btnSave) this.btnSave.style.display = 'flex';
        } else {
            if (this.btnNext) this.btnNext.style.display = 'flex';
            if (this.btnSave) this.btnSave.style.display = 'none';
        }
    }
};

// Initialize on Turbo navigation
document.addEventListener('turbo:load', () => {
    EquipmentWizard.init();
});

// Also run immediately if already loaded
if (document.readyState !== 'loading') {
    EquipmentWizard.init();
}
