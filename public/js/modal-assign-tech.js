/**
 * Assign Technician Modal Logic
 */
if (!window.AssignTechModal) {
    window.AssignTechModal = {
        modal: null,
        overlay: null,
        ticketId: null,
        selectedTech: null,
        technicians: [], // To store fetched techs for client-side filtering (optional)

        init() {
            // Create modal structure if not exists (handled by footer injection usually, but we assume it's there)
            this.overlay = document.getElementById('assignTechOverlay');
            this.modal = document.getElementById('assignTechModal');

            if (!this.overlay) return;

            // Bind Search (Nuclear property assignment)
            const searchInput = document.getElementById('atSearchInput');
            if (searchInput) {
                searchInput.oninput = (e) => this.filterTechnicians(e.target.value);
            }

            // Bind Close
            const closeBtns = this.overlay.querySelectorAll('.at-close-btn, .at-btn-cancel');
            closeBtns.forEach(btn => {
                btn.onclick = () => this.close();
            });

            // Bind Confirm
            const confirmBtn = document.getElementById('atConfirmBtn');
            if (confirmBtn) {
                confirmBtn.onclick = () => this.submitAssignment();
            }
        },

        open(ticketData) {
            if (!this.overlay) this.init(); // Retry init if failed earlier
            if (!this.overlay) return;

            this.ticketId = ticketData.id;
            this.selectedTech = null;

            // Populate Ticket Info
            document.getElementById('atTicketId').innerText = `#${ticketData.id}`;
            document.getElementById('atTicketTitle').innerText = ticketData.title || 'Sin Título';

            // Context Column
            document.getElementById('atDevice').innerText = ticketData.device || 'N/A';
            document.getElementById('atLocation').innerText = ticketData.location || 'N/A';
            document.getElementById('atCategory').innerText = ticketData.category || 'N/A';

            // Priority Badge
            const pBadge = document.getElementById('atPriorityBadge');
            pBadge.innerText = (ticketData.priority || 'NORMAL').toUpperCase();
            pBadge.className = 'at-priority-badge'; // reset
            if (ticketData.priority === 'alta') pBadge.classList.add('at-priority-high');
            else if (ticketData.priority === 'media') pBadge.classList.add('at-priority-medium');
            else pBadge.classList.add('at-priority-low');

            document.getElementById('atTime').innerText = ticketData.timeElapsed || 'Reciente';

            // Reset Search
            const searchInput = document.getElementById('atSearchInput');
            if (searchInput) searchInput.value = '';

            // Reset UI
            this.renderPlaceholder('Cargando técnicos...');
            this.updateActionState();

            // Show Modal
            this.overlay.style.display = 'flex';
            // Force reflow for animation
            setTimeout(() => this.overlay.classList.add('show'), 10);

            // Fetch Technicians
            this.loadTechnicians();
        },

        close() {
            if (!this.overlay) return;
            this.overlay.classList.remove('show');
            setTimeout(() => {
                this.overlay.style.display = 'none';
            }, 300);
        },

        loadTechnicians() {
            const url = BASE_URL + 'soportes/buscar_tecnicos';
            // alert("DEBUG: Consultando " + url); 

            fetch(url)
                .then(res => {
                    if (!res.ok) {
                        this.renderPlaceholder("Error HTTP: " + res.status);
                        throw new Error("HTTP " + res.status);
                    }
                    return res.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);

                        if (data.error || !Array.isArray(data)) {
                            this.technicians = [];
                            this.renderPlaceholder('Respuesta invalida: ' + JSON.stringify(data));
                        } else {
                            this.technicians = data;
                            if (this.technicians.length === 0) {
                                this.renderPlaceholder(' Servidor devolvió lista VACÍA (0 técnicos).');
                            }
                        }
                        this.renderTechnicians(this.technicians);

                    } catch (e) {
                        console.error("JSON Error", e, text);
                        this.renderPlaceholder('Error: Respuesta no es JSON. Posible error PHP.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.renderPlaceholder('Error crítico de conexión.');
                });
        },

        renderTechnicians(list) {
            const container = document.getElementById('atTechList');
            container.innerHTML = '';

            if (list.length === 0) {
                this.renderPlaceholder('No hay técnicos disponibles.');
                return;
            }

            list.forEach(tech => {
                const card = document.createElement('div');

                // Logic for status/colors
                const fullName = `${tech.nombre} ${tech.apellido}`;
                const initials = (tech.nombre[0] + (tech.apellido[0] || '')).toUpperCase();
                const workloadPct = Math.min((tech.active_tickets / 5) * 100, 100);

                let statusColor = 'at-status-offline';
                let barColor = 'bg-slate-300';

                if (tech.status === 'available') { statusColor = 'at-status-available'; barColor = '#10b981'; }
                else if (tech.status === 'busy') { statusColor = 'at-status-busy'; barColor = '#f59e0b'; }

                // Override color if workload is high
                if (tech.active_tickets > 3) barColor = '#f59e0b'; // amber

                card.className = `at-tech-card ${tech.status === 'offline' ? 'offline' : ''}`;
                if (this.selectedTech && this.selectedTech.id === tech.id) {
                    card.classList.add('selected');
                }

                card.innerHTML = `
                    <div class="at-avatar-wrapper">
                        <div class="at-avatar">${initials}</div>
                        <div class="at-status-dot ${statusColor}"></div>
                    </div>
                    <div class="at-tech-info">
                        <div class="at-tech-header">
                            <span class="at-tech-name">${fullName}</span>
                            <span class="at-tech-specialty">${tech.specialty || 'General'}</span>
                        </div>
                        <div class="at-workload-bar-container">
                            <div class="at-bar-bg">
                                <div class="at-bar-fill" style="width: ${workloadPct}%; background-color: ${barColor};"></div>
                            </div>
                            <span class="at-workload-text">${tech.active_tickets} tickets</span>
                        </div>
                    </div>
                    ${this.selectedTech && this.selectedTech.id === tech.id ?
                        '<div class="at-check-icon"><i class="bi bi-check-circle-fill" style="font-size: 1.25rem;"></i></div>' : ''}
                `;

                if (tech.status !== 'offline') {
                    card.addEventListener('click', () => this.selectTech(tech));
                }

                container.appendChild(card);
            });
        },

        renderPlaceholder(msg) {
            const container = document.getElementById('atTechList');
            container.innerHTML = `<div class="p-4 text-center text-sm text-slate-400 italic">${msg}</div>`;
        },

        filterTechnicians(term) {
            const lowerTerm = term.toLowerCase();
            const filtered = this.technicians.filter(t =>
                (t.nombre + ' ' + t.apellido).toLowerCase().includes(lowerTerm) ||
                (t.specialty && t.specialty.toLowerCase().includes(lowerTerm))
            );
            this.renderTechnicians(filtered);
        },

        selectTech(tech) {
            this.selectedTech = tech;
            // Re-render to show selection state (inefficient but safe for now)
            // Or manually toggle classes
            this.renderTechnicians(this.technicians); // Filter context handling might be needed if user is filtering
            this.updateActionState();
        },

        updateActionState() {
            const confirmBtn = document.getElementById('atConfirmBtn');
            const warningBox = document.getElementById('atWarningBox');

            if (!this.selectedTech) {
                confirmBtn.disabled = true;
                confirmBtn.innerText = 'Confirmar Asignación';
                warningBox.style.display = 'none';
                return;
            }

            confirmBtn.disabled = false;

            if (this.selectedTech.active_tickets > 3) {
                warningBox.style.display = 'flex';
                warningBox.querySelector('span').innerText = `Advertencia: ${this.selectedTech.nombre} tiene una carga alta de trabajo.`;
            } else {
                warningBox.style.display = 'none';
            }
        },

        submitAssignment() {
            if (!this.selectedTech || !this.ticketId) return;

            const btn = document.getElementById('atConfirmBtn');
            btn.disabled = true;
            btn.innerText = 'Asignando...';

            // POST to backend
            const formData = new FormData();
            formData.append('soporte_id', this.ticketId);
            formData.append('empleado_id', this.selectedTech.id);

            fetch(BASE_URL + 'soportes/procesar_asignacion', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        // Flash message or reload
                        window.location.reload();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Hubo un error al asignar el ticket.');
                    btn.disabled = false;
                    btn.innerText = 'Confirmar Asignación';
                });
        }
    };
}

// Initialize on Load (Supports Turbo)
// Initialize on Load (Supports Turbo)
// Global Guard
if (!window._assignTechModalListenerAttached) {
    document.addEventListener('turbo:load', () => {
        if (window.AssignTechModal) {
            window.AssignTechModal.init();
        }
    });
    window._assignTechModalListenerAttached = true;
}
