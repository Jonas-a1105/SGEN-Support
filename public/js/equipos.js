/**
 * Equipos View Logic
 * With User Preferences Persistence
 */

(function () {
    const MODULE_NAME = 'equipos';

    const init = () => {
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');
        const emptyState = document.getElementById('emptyState');
        const btnViewList = document.getElementById('btnViewList');
        const btnViewGrid = document.getElementById('btnViewGrid');
        const searchInput = document.getElementById('searchEquipos');
        const filterTabs = document.querySelectorAll('.filter-tab');

        // Pagination specific vars are initialized in data attributes usually
        let itemsPerPage = 10;
        const mainContainer = document.querySelector('.equipos-container');
        if (mainContainer && mainContainer.dataset.perPage) {
            itemsPerPage = parseInt(mainContainer.dataset.perPage);
        }

        // Load saved preferences
        let currentFilter = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'filter', 'todos') : 'todos';
        let currentSearch = '';
        let currentView = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'view', 'grid') : 'grid';
        let currentPage = 1;
        let filteredIndices = [];

        // SAFE PARSE: Avoid NaN issues
        const savedPerPage = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'perPage', 10) : 10;
        itemsPerPage = parseInt(savedPerPage);
        if (isNaN(itemsPerPage) || itemsPerPage === 0) {
            itemsPerPage = 10;
        }

        // Init selector
        const selector = document.getElementById('itemsPerPageSelector');
        if (selector) selector.value = itemsPerPage.toString();

        // Apply saved filter tab
        if (currentFilter !== 'todos') {
            filterTabs.forEach(t => {
                t.classList.remove('active');
                if (t.getAttribute('data-filter') === currentFilter) {
                    t.classList.add('active');
                }
            });
        }

        // Apply saved view
        if (currentView === 'list') {
            if (gridView) gridView.style.display = 'none';
            if (listView) listView.style.display = 'block';
            if (btnViewList) btnViewList.classList.add('active');
            if (btnViewGrid) btnViewGrid.classList.remove('active');
        } else {
            if (gridView) gridView.style.display = 'grid';
            if (listView) listView.style.display = 'none';
            if (btnViewGrid) btnViewGrid.classList.add('active');
            if (btnViewList) btnViewList.classList.remove('active');
        }

        // Toggle de vista
        if (btnViewGrid) {
            btnViewGrid.onclick = function () {
                currentView = 'grid';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'grid');
                if (gridView) gridView.style.display = 'grid';
                if (listView) listView.style.display = 'none';
                btnViewGrid.classList.add('active');
                btnViewList.classList.remove('active');
                applyFilters();
            };
        }

        if (btnViewList) {
            btnViewList.onclick = function () {
                currentView = 'list';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'list');
                if (gridView) gridView.style.display = 'none';
                if (listView) listView.style.display = 'block';
                btnViewList.classList.add('active');
                btnViewGrid.classList.remove('active');
                applyFilters();
            };
        }

        // Filtros de estado
        filterTabs.forEach(tab => {
            tab.onclick = function () {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', currentFilter);
                currentPage = 1;
                applyFilters();
            };
        });

        // Search
        if (searchInput) {
            searchInput.oninput = function () {
                currentSearch = this.value.toLowerCase();
                currentPage = 1;
                applyFilters();
            };
        }

        // Pagination Functions
        const btnPrev = document.getElementById('btnPrevPage');
        const btnNext = document.getElementById('btnNextPage');
        const itemsPerPageSelector = document.getElementById('itemsPerPageSelector');

        if (itemsPerPageSelector) {
            itemsPerPageSelector.removeEventListener('change', window._equiposItemsPerPageHandler);
            window._equiposItemsPerPageHandler = function () {
                const val = parseInt(this.value);
                if (!isNaN(val)) {
                    itemsPerPage = val;
                    if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'perPage', itemsPerPage);
                    currentPage = 1;
                    applyFilters();
                }
            };
            itemsPerPageSelector.addEventListener('change', window._equiposItemsPerPageHandler);
        }

        if (btnPrev) {
            btnPrev.removeEventListener('click', window._equiposPrevPageHandler);
            window._equiposPrevPageHandler = function () {
                if (currentPage > 1) {
                    currentPage--;
                    applyFilters();
                }
            };
            btnPrev.addEventListener('click', window._equiposPrevPageHandler);
        }

        if (btnNext) {
            btnNext.removeEventListener('click', window._equiposNextPageHandler);
            window._equiposNextPageHandler = function () {
                const totalFilteredCount = filteredIndices.length;
                const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalFilteredCount / itemsPerPage) || 1;
                if (currentPage < totalPages) {
                    currentPage++;
                    applyFilters();
                }
            };
            btnNext.addEventListener('click', window._equiposNextPageHandler);
        }

        // Aplicar filtros y paginación
        function applyFilters() {
            if (!gridView) return;

            // Reset indices
            filteredIndices = [];
            const cards = gridView.querySelectorAll('.equipo-card');
            const rows = listView ? listView.querySelectorAll('tbody tr') : [];

            // 1. Filtrar
            cards.forEach((card, index) => {
                const estado = card.getAttribute('data-estado');
                const text = card.textContent.toLowerCase();
                const matchesFilter = currentFilter === 'todos' || estado === currentFilter;
                const matchesSearch = text.includes(currentSearch);

                // Ocultar inicialmente
                card.style.display = 'none';
                if (rows[index]) rows[index].style.display = 'none';

                if (matchesFilter && matchesSearch) {
                    filteredIndices.push(index);
                }
            });

            const totalFilteredCount = filteredIndices.length;
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalFilteredCount / itemsPerPage) || 1;

            // Validar página actual
            if (currentPage > totalPages) currentPage = 1;

            // 2. Paginar
            const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
            const end = itemsPerPage === -1 ? totalFilteredCount : start + itemsPerPage;
            const visibleIndices = filteredIndices.slice(start, end);

            visibleIndices.forEach(idx => {
                cards[idx].style.display = 'flex';
                if (rows[idx]) rows[idx].style.display = '';
            });

            // 3. Actualizar UI
            const visibleDisplay = document.getElementById('visibleCountDisplay');
            if (visibleDisplay) visibleDisplay.textContent = visibleIndices.length;

            const totalDisplay = document.getElementById('totalCountDisplay');
            if (totalDisplay) totalDisplay.textContent = totalFilteredCount;

            const currentDisplay = document.getElementById('currentPageDisplay');
            if (currentDisplay) currentDisplay.textContent = currentPage;

            const pagesDisplay = document.getElementById('totalPagesDisplay');
            if (pagesDisplay) pagesDisplay.textContent = totalPages;

            if (btnPrev) {
                btnPrev.disabled = currentPage === 1;
                btnPrev.style.opacity = currentPage === 1 ? '0.5' : '1';
                btnPrev.style.pointerEvents = currentPage === 1 ? 'none' : 'auto';
            }

            if (btnNext) {
                btnNext.disabled = currentPage === totalPages;
                btnNext.style.opacity = currentPage === totalPages ? '0.5' : '1';
                btnNext.style.pointerEvents = currentPage === totalPages ? 'none' : 'auto';
            }

            // Mostrar/ocultar empty state
            const footer = document.getElementById('paginationFooter');

            if (totalFilteredCount === 0) {
                if (emptyState) emptyState.style.display = 'block';
                if (currentView === 'grid') gridView.style.display = 'none';
                else if (listView) listView.style.display = 'none';
                if (footer) footer.style.display = 'none';
            } else {
                if (emptyState) emptyState.style.display = 'none';
                if (currentView === 'grid') gridView.style.display = 'grid';
                else if (listView) listView.style.display = 'block';
                if (footer) footer.style.display = 'flex';
            }
        }

        // Limpiar filtros (local to init)
        const btnClear = document.querySelector('[onclick="clearFilters()"]');
        if (btnClear) {
            btnClear.removeAttribute('onclick');
            btnClear.onclick = function () {
                currentFilter = 'todos';
                currentSearch = '';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', 'todos');
                if (searchInput) searchInput.value = '';
                filterTabs.forEach(t => t.classList.remove('active'));
                if (filterTabs[0]) filterTabs[0].classList.add('active');
                currentPage = 1;
                applyFilters();
            };
        }

        // Initial call
        applyFilters();
    };

    // Clean initialization for Turbo compatibility
    if (window._equiposInitHandler) {
        document.removeEventListener('turbo:load', window._equiposInitHandler);
    }

    window._equiposInitHandler = init;
    document.addEventListener('turbo:load', init);

    // Also run immediately if on equipos page
    if (document.readyState !== 'loading') {
        if (document.getElementById('gridView')) {
            init();
        }
    }
})();
