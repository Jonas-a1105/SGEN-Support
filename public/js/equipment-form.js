/**
 * Equipment Registration Wizard Logic
 */

const EquipmentWizard = {
    activeStep: 0,
    totalSteps: 4,

    init() {
        this.cacheDOM();
        this.bindEvents();
        this.updateStep();
        this.handleOtherType(); // Init dynamic field logic
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
    },

    bindEvents() {
        if (!this.wrapper) return;

        // Navigation
        if (this.btnPrev) this.btnPrev.addEventListener('click', () => this.prevStep());
        if (this.btnNext) this.btnNext.addEventListener('click', () => this.nextStep());

        // Step buttons (Direct navigation)
        this.steps.forEach((btn, index) => {
            btn.addEventListener('click', () => this.goToStep(index));
        });

        // Dynamic Field: Tipo Otro
        if (this.typeSelect) {
            this.typeSelect.addEventListener('change', () => this.handleOtherType());
        }
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
                // Optional: clear value if hiding? Maybe better not content loss.
            }
        }
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

document.addEventListener('DOMContentLoaded', () => {
    EquipmentWizard.init();
});
