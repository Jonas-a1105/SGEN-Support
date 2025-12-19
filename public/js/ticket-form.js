/**
 * Ticket Form - Create/Edit Support Ticket
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.TICKET_FORM_LOADED) return;
    window.TICKET_FORM_LOADED = true;

    // Get BASE_URL from global or data attribute
    const getBaseUrl = () => window.BASE_URL || document.body.dataset.baseUrl || '/';

    function initTicketForm() {
        const form = document.getElementById('ticketForm');
        if (!form) return; // Not on ticket form page

        const btnBuscar = document.getElementById('btn_buscar_equipo');
        const inputBusqueda = document.getElementById('busqueda_equipo');
        const mensajeBusqueda = document.getElementById('mensaje_busqueda');
        const inputEquipoId = document.getElementById('equipo_id');
        const inputDeptoId = document.getElementById('departamento_id');
        const deviceCard = document.getElementById('device_card');
        const emptyDevice = document.getElementById('empty_device');

        // Category selection
        window.selectCategory = function (btn, id) {
            document.querySelectorAll('.tf-category-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('categoria_id').value = id;
        };

        // Priority selection
        window.selectPriority = function (btn, value) {
            document.querySelectorAll('.tf-priority-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('prioridad').value = value;
        };

        // Status selection (Edit mode)
        window.selectStatus = function (btn, value) {
            document.querySelectorAll('.tf-status-btn').forEach(b => {
                b.classList.remove('active');
                b.querySelector('.tf-status-check')?.remove();
            });
            btn.classList.add('active');
            const check = document.createElement('i');
            check.className = 'bi bi-check-circle-fill tf-status-check';
            btn.appendChild(check);
            document.getElementById('estado').value = value;

            // Show/hide resolution section based on status
            const resSection = document.querySelector('.tf-resolution-section');
            if (resSection) {
                resSection.style.display = (value === 'resuelto' || value === 'cerrado') ? 'block' : 'none';
            }
        };

        // Toggle device search (Edit mode)
        window.toggleDeviceSearch = function () {
            const panel = document.getElementById('device_search_panel');
            const info = document.getElementById('device_info_static');
            const text = document.getElementById('change_device_text');

            if (panel.style.display === 'none') {
                panel.style.display = 'block';
                info.style.display = 'none';
                text.textContent = 'Cancelar';
            } else {
                panel.style.display = 'none';
                info.style.display = 'block';
                text.textContent = 'Cambiar';
            }
        };

        // Fill device card
        function llenarCamposEquipo(data) {
            inputEquipoId.value = data.id;
            if (inputDeptoId) inputDeptoId.value = data.departamento_id || '';

            const deviceNameEl = document.getElementById('device_name');
            const deviceSerialEl = document.getElementById('device_serial');
            const deviceDeptEl = document.getElementById('device_dept');
            const deviceTypeEl = document.getElementById('device_type');

            if (deviceNameEl) deviceNameEl.textContent = `${data.marca} ${data.modelo}`;
            if (deviceSerialEl) deviceSerialEl.textContent = `S/N: ${data.serial}`;
            if (deviceDeptEl) deviceDeptEl.textContent = data.departamento_nombre || 'Sin Asignar';
            if (deviceTypeEl) deviceTypeEl.textContent = data.tipo ? data.tipo.toUpperCase() : 'EQUIPO';

            // Set icon based on type
            const iconEl = document.getElementById('device_icon');
            if (iconEl) {
                iconEl.className = 'bi ';
                const tipo = (data.tipo || '').toLowerCase();
                if (tipo.includes('laptop') || tipo.includes('portatil')) {
                    iconEl.classList.add('bi-laptop');
                } else if (tipo.includes('impresora')) {
                    iconEl.classList.add('bi-printer');
                } else if (tipo.includes('servidor')) {
                    iconEl.classList.add('bi-hdd-rack');
                } else {
                    iconEl.classList.add('bi-pc-display');
                }
            }

            if (deviceCard) deviceCard.style.display = 'block';
            if (emptyDevice) emptyDevice.style.display = 'none';
            if (mensajeBusqueda) mensajeBusqueda.classList.remove('show');

            // For edit mode, close search panel
            const panel = document.getElementById('device_search_panel');
            if (panel) {
                panel.style.display = 'none';
                const info = document.getElementById('device_info_static');
                if (info) info.style.display = 'block';
                const text = document.getElementById('change_device_text');
                if (text) text.textContent = 'Cambiar';
            }
        }

        window.limpiarEquipo = function () {
            inputEquipoId.value = '';
            if (inputDeptoId) inputDeptoId.value = '';
            if (deviceCard) deviceCard.style.display = 'none';
            if (emptyDevice) emptyDevice.style.display = 'block';
        };

        function mostrarModalSeleccion(equipos) {
            const listaEquipos = document.getElementById('lista_equipos');
            listaEquipos.innerHTML = '';

            equipos.forEach(equipo => {
                const item = document.createElement('div');
                item.className = 'tf-modal-item';
                item.innerHTML = `
                    <p class="tf-modal-item-title">${equipo.tipo.toUpperCase()} - ${equipo.marca} ${equipo.modelo}</p>
                    <p class="tf-modal-item-subtitle">Serial: ${equipo.serial} • ${equipo.departamento_nombre || 'Sin Asignar'}</p>
                `;
                item.addEventListener('click', function () {
                    llenarCamposEquipo(equipo);
                    const modalEl = document.getElementById('modalSeleccionEquipo');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                });
                listaEquipos.appendChild(item);
            });

            const modalEl = document.getElementById('modalSeleccionEquipo');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        if (btnBuscar) {
            btnBuscar.addEventListener('click', function () {
                const query = inputBusqueda.value.trim();
                if (!query) return;

                fetch(getBaseUrl() + 'equipos/apiBuscar?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.found) {
                            if (mensajeBusqueda) mensajeBusqueda.classList.remove('show');

                            if (data.multiple) {
                                mostrarModalSeleccion(data.equipos);
                            } else {
                                llenarCamposEquipo(data);
                            }
                        } else {
                            if (mensajeBusqueda) {
                                mensajeBusqueda.textContent = data.error || 'Equipo no encontrado.';
                                mensajeBusqueda.classList.add('show');
                            }
                            window.limpiarEquipo();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (mensajeBusqueda) {
                            mensajeBusqueda.textContent = 'Error al buscar equipo.';
                            mensajeBusqueda.classList.add('show');
                        }
                    });
            });
        }

        // Allow Enter key to search
        if (inputBusqueda) {
            inputBusqueda.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    btnBuscar.click();
                }
            });
        }
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketForm);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketForm();
    }
})();
