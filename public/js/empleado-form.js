/**
 * Empleado Form - Employee Card Preview
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.EMPLEADO_FORM_LOADED) return;
    window.EMPLEADO_FORM_LOADED = true;

    window.updatePreview = function () {
        const nombre = document.getElementById('nombre')?.value || '';
        const apellido = document.getElementById('apellido')?.value || '';
        const email = document.getElementById('email')?.value || '';
        const cedula = document.getElementById('cedula')?.value || '';
        const cargo = document.getElementById('cargo')?.value || '';
        const deptSelect = document.getElementById('departamento_id');
        const deptName = deptSelect && deptSelect.options[deptSelect.selectedIndex] ? deptSelect.options[deptSelect.selectedIndex].text : '---';

        // Update name
        const displayName = (nombre || apellido) ? `${nombre} ${apellido}`.trim() : 'Nombre Apellido';
        const previewName = document.getElementById('previewName');
        if (previewName) previewName.textContent = displayName;

        // Update email
        const previewEmail = document.getElementById('previewEmail');
        if (previewEmail) previewEmail.textContent = email || 'correo@empresa.com';

        // Update cedula
        const previewCedula = document.getElementById('previewCedula');
        if (previewCedula) previewCedula.textContent = cedula || '---';

        // Update cargo
        const previewCargo = document.getElementById('previewCargo');
        if (previewCargo) previewCargo.textContent = cargo ? cargo.toUpperCase() : 'SIN CARGO';

        // Update department
        const previewDept = document.getElementById('previewDept');
        if (previewDept) previewDept.textContent = (deptSelect && deptSelect.value) ? deptName.replace('-- ', '').replace(' --', '') : '---';

        // Update avatar initials
        const initials = (nombre && apellido)
            ? (nombre.charAt(0) + apellido.charAt(0)).toUpperCase()
            : '';

        const avatarEl = document.getElementById('avatarPreview');
        if (avatarEl) {
            if (initials) {
                avatarEl.innerHTML = initials;
            } else {
                avatarEl.innerHTML = '<i class="bi bi-person" style="font-size: 2rem;"></i>';
            }
        }
    };

    window.toggleSharedEmail = function () {
        const container = document.getElementById('checkboxContainer');
        const icon = document.getElementById('checkIcon');
        const input = document.getElementById('permitir_email_compartido');

        if (!container || !icon || !input) return;

        if (input.value === '0') {
            input.value = '1';
            container.style.background = '#6366f1';
            container.style.borderColor = '#6366f1';
            icon.style.display = 'block';
        } else {
            input.value = '0';
            container.style.background = 'white';
            container.style.borderColor = '#cbd5e1';
            icon.style.display = 'none';
        }
    };

    function initEmpleadoForm() {
        const form = document.getElementById('formEmpleado');
        if (!form) return; // Not on employee form page

        // Initialize preview
        updatePreview();
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initEmpleadoForm);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initEmpleadoForm();
    }
})();
