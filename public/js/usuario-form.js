/**
 * Usuario Form - User Card Preview
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.USUARIO_FORM_LOADED) return;
    window.USUARIO_FORM_LOADED = true;

    // Default role colors
    const roleColors = {
        'admin': '#8b5cf6',
        'tecnico': '#3b82f6',
        'consultor': '#f59e0b'
    };

    // Department colors will be loaded from data attributes
    let deptColors = { '': '#64748b' };

    window.toggleAdvancedOptions = function () {
        const checkbox = document.getElementById('showAdvanced');
        const advancedOptions = document.getElementById('advancedOptions');

        if (advancedOptions) {
            if (checkbox && checkbox.checked) {
                advancedOptions.style.display = 'flex';
            } else {
                advancedOptions.style.display = 'none';
                // Reset values when hiding
                const deptSelect = document.getElementById('departamento_id');
                const empSelect = document.getElementById('empleado_id');
                if (deptSelect) deptSelect.value = '';
                if (empSelect) empSelect.value = '';
                updatePreview();
            }
        }
    };

    window.updatePreview = function () {
        const username = document.getElementById('username')?.value || 'username';
        const rol = document.getElementById('rol')?.value || 'tecnico';
        const deptSelect = document.getElementById('departamento_id');
        const deptId = deptSelect ? deptSelect.value : '';
        const deptName = deptSelect && deptSelect.options[deptSelect.selectedIndex] ? deptSelect.options[deptSelect.selectedIndex].text : 'Sin Depto';

        const empSelect = document.getElementById('empleado_id');
        const selectedEmp = empSelect ? empSelect.options[empSelect.selectedIndex] : null;

        let nombre = '';
        let apellido = '';
        let email = '';

        if (selectedEmp && selectedEmp.value) {
            nombre = selectedEmp.getAttribute('data-nombre') || '';
            apellido = selectedEmp.getAttribute('data-apellido') || '';
            email = selectedEmp.getAttribute('data-email') || '';
        }

        const displayName = (nombre && apellido) ? `${nombre} ${apellido}` : username;
        const initials = nombre && apellido
            ? (nombre.charAt(0) + apellido.charAt(0)).toUpperCase()
            : username.substring(0, 2).toUpperCase();

        // Update card
        const previewName = document.getElementById('previewName');
        const previewEmail = document.getElementById('previewEmail');
        const previewRole = document.getElementById('previewRole');
        const previewDept = document.getElementById('previewDept');
        const avatarInitials = document.getElementById('avatarInitials');
        const cardHeader = document.getElementById('cardHeader');

        if (previewName) previewName.textContent = displayName;
        if (previewEmail) previewEmail.textContent = email || `@${username}`;
        if (previewRole) previewRole.textContent = rol.charAt(0).toUpperCase() + rol.slice(1);
        if (previewDept) previewDept.textContent = deptId ? deptName.replace('-- ', '').replace(' --', '') : 'Sin Depto';

        // Update avatar
        if (avatarInitials) {
            if (initials) {
                avatarInitials.innerHTML = initials;
            } else {
                avatarInitials.innerHTML = '<i class="bi bi-camera" style="font-size: 1.5rem;"></i>';
            }
        }

        // Update colors
        const headerColor = deptColors[deptId] || '#3b82f6';
        if (cardHeader) cardHeader.style.background = headerColor;
        if (previewDept) previewDept.style.background = headerColor;

        const roleColor = roleColors[rol] || '#3b82f6';
        if (previewRole) {
            previewRole.style.background = roleColor + '20';
            previewRole.style.color = roleColor;
        }
    };

    function initUsuarioForm() {
        const form = document.getElementById('userForm');
        if (!form) return; // Not on user form page

        // Load department colors from data attributes
        const deptSelect = document.getElementById('departamento_id');
        if (deptSelect) {
            const defaultColors = ['#3b82f6', '#10b981', '#8b5cf6', '#f43f5e', '#f59e0b', '#06b6d4'];
            Array.from(deptSelect.options).forEach((opt, i) => {
                if (opt.value) {
                    deptColors[opt.value] = opt.dataset.color || defaultColors[i % defaultColors.length];
                }
            });
        }

        // Initialize preview
        updatePreview();
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initUsuarioForm);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initUsuarioForm();
    }
})();
