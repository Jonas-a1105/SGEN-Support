/**
 * Department Form Logic
 * Handles Manager Selection and Employee Search
 */

const DepartmentForm = {
    state: {
        employees: [], // Will be populated from PHP
        managerId: null,
        searchQuery: '',
    },

    init(employeesData, currentManagerId) {
        this.state.employees = employeesData || [];
        this.state.managerId = currentManagerId ? parseInt(currentManagerId) : null;

        this.cacheDOM();
        this.bindEvents();
        this.renderManagerCard();
    },

    cacheDOM() {
        this.container = document.getElementById('departmentForm');
        this.managerDisplay = document.getElementById('managerDisplay');
        this.searchArea = document.getElementById('managerSearchArea');
        this.btnToggleSearch = document.getElementById('btnToggleSearch');
        this.employeeGrid = document.getElementById('employeeGrid');
        this.hiddenInput = document.getElementById('inputManagerId');
        this.hiddenInputName = document.getElementById('inputManagerName');
    },

    bindEvents() {
        // Toggle Search
        if (this.btnToggleSearch) {
            this.btnToggleSearch.addEventListener('click', () => {
                const isHidden = this.searchArea.style.display === 'none';
                this.searchArea.style.display = isHidden ? 'block' : 'none';
                if (isHidden) this.renderEmployeeGrid();
            });
        }
    },

    getInitials(name) {
        return name
            .split(' ')
            .map(n => n[0])
            .slice(0, 2)
            .join('')
            .toUpperCase();
    },

    selectManager(empId) {
        this.state.managerId = empId;

        // Update hidden inputs
        if (this.hiddenInput) this.hiddenInput.value = empId;

        // Find employee name for the fallback name input (if needed)
        const emp = this.state.employees.find(e => e.id === empId);
        if (this.hiddenInputName && emp) {
            this.hiddenInputName.value = emp.nombre + ' ' + (emp.apellido || '');
        }

        this.renderManagerCard();
        this.searchArea.style.display = 'none'; // Close search
    },

    removeManager() {
        this.state.managerId = null;
        if (this.hiddenInput) this.hiddenInput.value = '';
        if (this.hiddenInputName) this.hiddenInputName.value = '';

        this.renderManagerCard();
        this.searchArea.style.display = 'none';
    },

    renderManagerCard() {
        if (!this.managerDisplay) return;

        const manager = this.state.employees.find(e => e.id === this.state.managerId);

        if (manager) {
            const initials = this.getInitials(manager.nombre + ' ' + (manager.apellido || ''));
            const role = manager.cargo || 'Empleado';
            const fullName = manager.nombre + ' ' + (manager.apellido || '');

            this.managerDisplay.innerHTML = `
                <div class="current-manager-info">
                    <div class="manager-avatar assigned">${initials}</div>
                    <div class="manager-details">
                        <p style="font-size: 0.875rem; color: var(--df-slate-500); margin-bottom: 0;">Jefe de Área Actual</p>
                        <h4 class="text-dark">${fullName}</h4>
                        <span class="manager-role-badge">${role}</span>
                    </div>
                </div>
            `;
            if (this.btnToggleSearch) this.btnToggleSearch.innerHTML = 'Cambiar Jefe <i class="bi bi-search"></i>';
        } else {
            this.managerDisplay.innerHTML = `
                <div class="current-manager-info">
                    <div class="manager-avatar empty"><i class="bi bi-person"></i></div>
                    <div class="manager-details">
                        <p style="font-size: 0.875rem; color: var(--df-slate-500); margin-bottom: 0;">Jefe de Área</p>
                        <h4 class="text-muted">-- Sin asignar --</h4>
                    </div>
                </div>
            `;
            if (this.btnToggleSearch) this.btnToggleSearch.innerHTML = 'Asignar Jefe <i class="bi bi-search"></i>';
        }
    },

    renderEmployeeGrid() {
        if (!this.employeeGrid) return;

        // Simple render of all employees (could receive filter logic later)
        // Add "Remove Assignment" button first
        let html = `
            <button type="button" class="btn-remove-assign" onclick="DepartmentForm.removeManager()">
                <i class="bi bi-x-lg"></i> Quitar Asignación
            </button>
        `;

        this.state.employees.forEach(emp => {
            const isActive = this.state.managerId === emp.id;
            const initials = this.getInitials(emp.nombre + ' ' + (emp.apellido || ''));
            const fullName = emp.nombre + ' ' + (emp.apellido || '');

            html += `
                <div class="emp-card ${isActive ? 'active' : ''}" onclick="DepartmentForm.selectManager(${emp.id})">
                    <div class="emp-avatar-small">${initials}</div>
                    <div class="emp-info">
                        <div class="emp-name">${fullName}</div>
                        <span class="emp-role">${emp.cargo || 'Empleado'}</span>
                    </div>
                    ${isActive ? '<i class="bi bi-check-circle-fill check-icon"></i>' : ''}
                </div>
            `;
        });

        this.employeeGrid.innerHTML = html;
    }
};
