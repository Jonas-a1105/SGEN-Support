/**
 * Maintenance Schedule Form Logic
 * Turbo-compatible version
 */
document.addEventListener('turbo:load', () => {
    // --- DOM Elements ---
    const btnPreventive = document.getElementById('btnTypePreventive');
    const btnCorrective = document.getElementById('btnTypeCorrective');
    const inputType = document.getElementById('inputTypeMantenimiento');
    const headerIconWrapper = document.getElementById('headerIconWrapper');
    const headerTitle = document.getElementById('headerTitle');
    const btnSave = document.getElementById('btnSave');
    const headerIconPreventive = document.getElementById('iconPreventive');
    const headerIconCorrective = document.getElementById('iconCorrective');
    const recurrenceContainer = document.getElementById('recurrenceContainer');

    const checklistContainer = document.getElementById('checklistItems');
    const checklistInput = document.getElementById('inputChecklistJson');
    const addTaskInput = document.getElementById('inputAddTask');
    const addTaskForm = document.getElementById('formAddTask'); // Assuming container acts as form or has input
    const taskCount = document.getElementById('taskCount');
    const initialChecklist = document.getElementById('initialChecklist');

    const selectDevice = document.getElementById('selectDevice');
    const inputDeviceID = document.getElementById('inputDeviceID');
    const deviceSearchView = document.getElementById('deviceSearchView');
    const deviceSelectedView = document.getElementById('deviceSelectedView');
    const selectedDeviceName = document.getElementById('selectedDeviceName');
    const selectedDeviceMeta = document.getElementById('selectedDeviceMeta');

    // --- State ---
    const state = {
        type: 'preventive',
        checklist: [],
        device: null
    };

    // --- Initialization ---
    // 1. Checklist
    if (initialChecklist && initialChecklist.value) {
        try {
            const val = initialChecklist.value.trim();
            if (val) {
                state.checklist = JSON.parse(val);
                renderChecklist();
            }
        } catch (e) { console.error('Error parsing checklist', e); }
    }

    // 2. Type
    if (inputType && inputType.value) {
        setType(inputType.value);
    }

    // --- Event Listeners ---
    if (btnPreventive) btnPreventive.addEventListener('click', () => setType('preventive'));
    if (btnCorrective) btnCorrective.addEventListener('click', () => setType('corrective'));

    // Add Task (Enter key on input)
    if (addTaskInput) {
        addTaskInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const text = addTaskInput.value.trim();
                if (!text) return;

                state.checklist.push({ id: Date.now(), text: text, done: false });
                addTaskInput.value = '';
                renderChecklist();
                updateHiddenInput();
            }
        });
    }

    // Also click on plus icon? Not strictly button but we can add logic if wrapper is clicked
    if (addTaskForm) {
        addTaskForm.addEventListener('click', (e) => {
            // If user clicks the plus icon
            if (e.target.classList.contains('bi-plus-lg')) {
                const text = addTaskInput.value.trim();
                if (text) {
                    state.checklist.push({ id: Date.now(), text: text, done: false });
                    addTaskInput.value = '';
                    renderChecklist();
                    updateHiddenInput();
                } else {
                    addTaskInput.focus();
                }
            }
        });
    }



    // --- Device Search Logic ---
    const searchInput = document.getElementById('deviceSearchInput');
    const searchResults = document.getElementById('deviceSearchResults');
    const hiddenSelect = document.getElementById('selectDevice');

    // Load all devices from hidden select
    let cachedDevices = [];
    if (hiddenSelect) {
        Array.from(hiddenSelect.options).forEach(opt => {
            if (opt.value) {
                cachedDevices.push({
                    id: opt.value,
                    name: opt.text,
                    code: opt.getAttribute('data-code') || '',
                    serial: opt.getAttribute('data-serial') || '',
                    brand: opt.getAttribute('data-brand') || '',
                    model: opt.getAttribute('data-model') || '',
                    location: opt.getAttribute('data-location') || '',
                    searchString: `${opt.text} ${opt.getAttribute('data-code')} ${opt.getAttribute('data-serial')} ${opt.getAttribute('data-brand')}`.toLowerCase()
                });
            }
        });
    }

    if (searchInput) {
        // Filter on input
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            if (query.length < 1) {
                searchResults.style.display = 'none';
                return;
            }

            const matches = cachedDevices.filter(d => d.searchString.includes(query));
            renderSearchResults(matches);
        });

        // Focus show all (optional, or just placeholder)
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length > 0) {
                searchResults.style.display = 'block';
            }
        });
    }

    // Render results
    function renderSearchResults(devices) {
        if (!devices.length) {
            searchResults.innerHTML = '<div class="mf-no-results">No se encontraron equipos</div>';
            searchResults.style.display = 'block';
            return;
        }

        searchResults.innerHTML = '';
        devices.forEach(d => {
            const div = document.createElement('div');
            div.className = 'mf-search-item';
            div.innerHTML = `
                <div>
                    <div class="mf-item-main">${escapeHtml(d.name)}</div>
                    <div class="mf-item-sub">S/N: ${escapeHtml(d.serial)} • Código: ${escapeHtml(d.code)}</div>
                </div>
                <div class="mf-item-sub">${escapeHtml(d.location)}</div>
            `;
            div.onclick = () => selectDeviceFromSearch(d);
            searchResults.appendChild(div);
        });
        searchResults.style.display = 'block';
    }

    // Select Device
    function selectDeviceFromSearch(device) {
        searchInput.value = '';
        searchResults.style.display = 'none';

        updateDeviceUI(device.id, device.name, device.code, device.location);
    }

    // Click outside to close
    document.addEventListener('click', (e) => {
        if (searchResults && searchInput && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    window.clearDeviceSelection = function () {
        if (inputDeviceID) inputDeviceID.value = '';
        if (selectDevice) selectDevice.value = ''; // Reset hidden select too
        toggleDeviceView('search');
    };

    // --- Functions ---

    function setType(type) {
        state.type = type;
        if (inputType) inputType.value = type;

        // UI Updates
        if (type === 'preventive') {
            if (btnPreventive) btnPreventive.classList.add('active', 'preventive');
            if (btnCorrective) btnCorrective.classList.remove('active', 'corrective');

            if (headerIconWrapper) {
                headerIconWrapper.classList.add('preventive');
                headerIconWrapper.classList.remove('corrective');
            }

            if (headerIconPreventive) headerIconPreventive.style.display = 'block';
            if (headerIconCorrective) headerIconCorrective.style.display = 'none';

            if (headerTitle) headerTitle.textContent = 'Programar Mantenimiento';

            if (btnSave) {
                btnSave.classList.add('preventive');
                btnSave.classList.remove('corrective');
            }

            if (recurrenceContainer) recurrenceContainer.style.display = 'block';
        } else {
            if (btnCorrective) btnCorrective.classList.add('active', 'corrective');
            if (btnPreventive) btnPreventive.classList.remove('active', 'preventive');

            if (headerIconWrapper) {
                headerIconWrapper.classList.add('corrective');
                headerIconWrapper.classList.remove('preventive');
            }

            if (headerIconPreventive) headerIconPreventive.style.display = 'none';
            if (headerIconCorrective) headerIconCorrective.style.display = 'block';

            if (headerTitle) headerTitle.textContent = 'Reportar Mantenimiento Correctivo';

            if (btnSave) {
                btnSave.classList.add('corrective');
                btnSave.classList.remove('preventive');
            }

            if (recurrenceContainer) recurrenceContainer.style.display = 'none';
        }
    }

    function renderChecklist() {
        if (!checklistContainer) return;
        checklistContainer.innerHTML = '';
        state.checklist.forEach(task => {
            const div = document.createElement('div');
            div.className = 'mf-checklist-item';
            div.innerHTML = `
                <input type="checkbox" disabled style="width: 1rem; height: 1rem;">
                <span class="mf-checklist-text">${escapeHtml(task.text)}</span>
                <button type="button" class="mf-btn-remove-task" onclick="removeTask(${task.id})">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            checklistContainer.appendChild(div);
        });
        if (taskCount) taskCount.textContent = state.checklist.length + ' tareas';
    }

    function updateHiddenInput() {
        if (checklistInput) checklistInput.value = JSON.stringify(state.checklist);
    }

    function updateDeviceUI(id, name, code, loc) {
        if (selectedDeviceName) selectedDeviceName.textContent = name;
        if (selectedDeviceMeta) selectedDeviceMeta.innerHTML = `<i class="bi bi-geo-alt"></i> ${loc || ''} &bull; ${code || ''}`;

        if (inputDeviceID) inputDeviceID.value = id;
        toggleDeviceView('selected');
    }

    function toggleDeviceView(view) {
        if (view === 'search') {
            if (deviceSearchView) deviceSearchView.style.display = 'block';
            if (deviceSelectedView) deviceSelectedView.style.display = 'none';
        } else {
            if (deviceSearchView) deviceSearchView.style.display = 'none';
            if (deviceSelectedView) deviceSelectedView.style.display = 'block';
        }
    }

    function escapeHtml(text) {
        if (!text) return text;
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
