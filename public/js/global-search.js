/**
 * Global Search - Navigation & Site Map
 * Enables searching and navigating to any section of the application
 */

if (!window.GlobalSearch) {
    window.GlobalSearch = {
        input: null,
        resultsContainer: null,
        selectedIndex: -1,
        filteredResults: [],
        isOpen: false,
        eventsBound: false,

        // Comprehensive Navigation Items
        navItems: [
            // --- Core ---
            { title: 'Dashboard', description: 'Panel principal y métricas', icon: 'bi-grid', url: '', keywords: ['inicio', 'home', 'resumen'] },
            { title: 'Mi Perfil', description: 'Información personal y contraseña', icon: 'bi-person-circle', url: 'perfil', keywords: ['cuenta', 'usuario', 'clave'] },
            { title: 'Configuración', description: 'Ajustes generales del sistema', icon: 'bi-gear', url: 'configuracion', keywords: ['sistema', 'opciones', 'preferencias'] },
            { title: 'Acerca de', description: 'Información de versión y sistema', icon: 'bi-info-circle', url: 'about', keywords: ['version', 'creditos'] },

            // --- Tickets (Soportes) ---
            { title: 'Tickets', description: 'Listado general de tickets', icon: 'bi-ticket', url: 'soportes', keywords: ['soporte', 'ayuda', 'incidentes'] },
            { title: 'Nuevo Ticket', description: 'Crear una solicitud de soporte', icon: 'bi-plus-circle', url: 'soportes/crear', keywords: ['crear ticket', 'nueva solicitud', 'reportar'] },

            // --- Equipos ---
            { title: 'Equipos', description: 'Inventario de activos informáticos', icon: 'bi-laptop', url: 'equipos', keywords: ['computadoras', 'pc', 'laptops', 'hardware'] },
            { title: 'Nuevo Equipo', description: 'Registrar un nuevo activo', icon: 'bi-plus-circle', url: 'equipos/crear', keywords: ['agregar equipo', 'alta activo'] },

            // --- Inventario (Consumibles/Periféricos) ---
            { title: 'Inventario General', description: 'Listado de items y stock', icon: 'bi-box-seam', url: 'inventario', keywords: ['stock', 'productos', 'almacen'] },
            { title: 'Nuevo Item', description: 'Agregar producto al inventario', icon: 'bi-plus-circle', url: 'inventario/crear', keywords: ['crear item', 'nuevo producto'] },
            { title: 'Inventario por Departamento', description: 'Stock distribuido por área', icon: 'bi-building', url: 'inventario/departamento', keywords: ['distribucion', 'asignacion'] },

            // --- Mantenimientos ---
            { title: 'Mantenimientos', description: 'Calendario y lista de servicios', icon: 'bi-tools', url: 'mantenimientos', keywords: ['reparaciones', 'servicios', 'preventivo'] },
            { title: 'Dashboard Mantenimiento', description: 'Métricas de estado de equipos', icon: 'bi-speedometer2', url: 'mantenimientos/dashboard', keywords: ['graficas', 'estado'] },
            { title: 'Programar Mantenimiento', description: 'Agendar nuevo servicio', icon: 'bi-calendar-plus', url: 'mantenimientos/crear', keywords: ['nuevo mantenimiento', 'agendar'] },

            // --- Recursos Humanos ---
            { title: 'Empleados', description: 'Directorio de personal', icon: 'bi-people', url: 'empleados', keywords: ['rrhh', 'staff', 'gente'] },
            { title: 'Nuevo Empleado', description: 'Registrar nuevo personal', icon: 'bi-person-plus', url: 'empleados/crear', keywords: ['contratacion', 'alta empleado'] },

            // --- Organización ---
            { title: 'Departamentos', description: 'Áreas y unidades organizativas', icon: 'bi-diagram-3', url: 'departamentos', keywords: ['organigrama', 'secciones'] },
            { title: 'Nuevo Departamento', description: 'Crear nueva área', icon: 'bi-folder-plus', url: 'departamentos/crear', keywords: ['agregar departamento'] },

            // --- Administración y Seguridad ---
            { title: 'Usuarios', description: 'Administración de accesos', icon: 'bi-person-badge', url: 'usuarios', keywords: ['cuentas', 'login', 'permisos'] },
            { title: 'Nuevo Usuario', description: 'Crear cuenta de acceso', icon: 'bi-person-plus-fill', url: 'usuarios/crear', keywords: ['registrar usuario'] },
            { title: 'Categorías', description: 'Gestión de categorías de soporte', icon: 'bi-tags', url: 'categorias', keywords: ['tipos', 'clasificacion'] },
            { title: 'Nueva Categoría', description: 'Crear categoría', icon: 'bi-plus-square', url: 'categorias/crear', keywords: ['agregar categoria'] },

            // --- Auditoría y Logs ---
            { title: 'Bitácora', description: 'Historial de acciones del sistema', icon: 'bi-journal-text', url: 'bitacora', keywords: ['audit', 'registro', 'acciones'] },
            { title: 'Logs de Sistema', description: 'Registros técnicos', icon: 'bi-file-code', url: 'logs', keywords: ['errores', 'sistema'] },

            // --- Reportes ---
            { title: 'Centro de Reportes', description: 'Todos los informes disponibles', icon: 'bi-bar-chart', url: 'reportes', keywords: ['estadisticas', 'informes', 'pdf', 'excel'] },
            { title: 'Reporte de Rendimiento', description: 'KPIs y métricas de gestión', icon: 'bi-graph-up', url: 'reportes/rendimiento', keywords: ['kpi', 'performance'] },
            { title: 'Historial de Reportes', description: 'Archivos generados anteriormente', icon: 'bi-clock-history', url: 'reportes/historial', keywords: ['anteriores', 'descargas'] }
        ],

        init() {
            this.input = document.getElementById('globalSearchInput');
            this.resultsContainer = document.getElementById('globalSearchResults');

            if (!this.input || !this.resultsContainer) return;

            this.bindEvents();
        },

        bindEvents() {
            // Document shortcuts (Ctrl+K) - Only bind once per session
            if (!this.eventsBound) {
                document.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        this.input.focus();
                        this.showResults();
                    }
                });

                // Click outside to close
                document.addEventListener('click', (e) => {
                    if (!this.input.contains(e.target) && !this.resultsContainer.contains(e.target)) {
                        this.closeResults();
                    }
                });

                this.eventsBound = true;
            }

            // Input events - Bind every time we get a new input element
            this.input.addEventListener('input', (e) => this.search(e.target.value));
            this.input.addEventListener('focus', () => this.showResults());
            this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        },

        open() {
            this.input.focus();
            this.showResults();
        },

        showResults() {
            this.isOpen = true;
            this.resultsContainer.classList.add('active');
            if (this.input.value.trim() === '') {
                this.search('');
            }
        },

        closeResults() {
            this.isOpen = false;
            this.resultsContainer.classList.remove('active');
            this.selectedIndex = -1;
        },

        search(query) {
            const q = query.toLowerCase().trim();

            if (q === '') {
                // Show core items by default
                this.filteredResults = this.navItems.slice(0, 8);
            } else {
                this.filteredResults = this.navItems.filter(item => {
                    const textMatch = item.title.toLowerCase().includes(q) ||
                        item.description.toLowerCase().includes(q);
                    const keywordMatch = item.keywords && item.keywords.some(k => k.includes(q));
                    return textMatch || keywordMatch;
                });
            }

            this.selectedIndex = -1;
            this.renderResults();
        },

        renderResults() {
            if (this.filteredResults.length === 0) {
                this.resultsContainer.innerHTML = `
                    <div class="hs-empty">
                        <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                        No se encontraron resultados para "${this.input.value}"
                    </div>
                `;
                return;
            }

            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

            let html = '<div class="hs-section-title">Resultados de Navegación</div>';

            this.filteredResults.forEach((item, index) => {
                const selectedClass = index === this.selectedIndex ? 'selected' : '';
                // Handle if url is empty (dashboard)
                const finalUrl = item.url ? (item.url.startsWith('http') ? item.url : baseUrl + item.url) : baseUrl;

                html += `
                    <a href="${finalUrl}" class="hs-result-item ${selectedClass}" data-index="${index}">
                        <div class="hs-result-icon">
                            <i class="${item.icon}"></i>
                        </div>
                        <div>
                            <p class="hs-result-title">${item.title}</p>
                            <p class="hs-result-description">${item.description}</p>
                        </div>
                    </a>
                `;
            });

            this.resultsContainer.innerHTML = html;

            // Bind hover events
            this.resultsContainer.querySelectorAll('.hs-result-item').forEach(item => {
                item.addEventListener('mouseenter', () => {
                    this.selectedIndex = parseInt(item.dataset.index);
                    this.updateSelection();
                });
            });
        },

        updateSelection() {
            this.resultsContainer.querySelectorAll('.hs-result-item').forEach((item, index) => {
                item.classList.toggle('selected', index === this.selectedIndex);
            });
        },

        handleKeydown(e) {
            if (!this.isOpen) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (this.selectedIndex < this.filteredResults.length - 1) {
                    this.selectedIndex++;
                    this.updateSelection();
                    this.scrollSelectedIntoView();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.updateSelection();
                    this.scrollSelectedIntoView();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.selectedIndex >= 0 && this.filteredResults[this.selectedIndex]) {
                    const item = this.filteredResults[this.selectedIndex];
                    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                    const finalUrl = item.url ? (item.url.startsWith('http') ? item.url : baseUrl + item.url) : baseUrl;
                    window.location.href = finalUrl;
                } else if (this.filteredResults.length > 0) {
                    // Default to first result
                    const item = this.filteredResults[0];
                    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                    const finalUrl = item.url ? (item.url.startsWith('http') ? item.url : baseUrl + item.url) : baseUrl;
                    window.location.href = finalUrl;
                }
            } else if (e.key === 'Escape') {
                this.closeResults();
                this.input.blur();
            }
        },

        scrollSelectedIntoView() {
            const selected = this.resultsContainer.querySelector('.hs-result-item.selected');
            if (selected) {
                selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }
    };
}

// Execute immediately (compatible with Turbo)
window.GlobalSearch.init();
