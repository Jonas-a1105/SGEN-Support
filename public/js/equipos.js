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

        // Init selector
        const selector = document.getElementById('itemsPerPageSelector');
        if (selector) selector.value = itemsPerPage;

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
            btnViewGrid.addEventListener('click', function () {
                currentView = 'grid';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'grid');
                if (gridView) gridView.style.display = 'grid';
                if (listView) listView.style.display = 'none';
                btnViewGrid.classList.add('active');
                btnViewList.classList.remove('active');
                applyFilters();
            });
        }

        if (btnViewList) {
            btnViewList.addEventListener('click', function () {
                currentView = 'list';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'list');
                if (gridView) gridView.style.display = 'none';
                if (listView) listView.style.display = 'block';
                btnViewList.classList.add('active');
                btnViewGrid.classList.remove('active');
                applyFilters();
            });
        }

        // Filtros de estado
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', currentFilter);
                currentPage = 1;
                applyFilters();
            });
        });

        // Search
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentSearch = this.value.toLowerCase();
                currentPage = 1;
                applyFilters();
            });
        }

        // Pagination Functions
        window.changeClientItemsPerPage = function (val) {
            itemsPerPage = parseInt(val);
            if (window.PaginationPrefs) PaginationPrefs.set(itemsPerPage);
            else {
                const expires = new Date();
                expires.setFullYear(expires.getFullYear() + 1);
                document.cookie = 'sgen_pagination_per_page=' + itemsPerPage + ';expires=' + expires.toUTCString() + ';path=/';
            }
            currentPage = 1;
            applyFilters();
        };

        window.prevPage = function () {
            if (currentPage > 1) {
                currentPage--;
                applyFilters();
            }
        };
        window.nextPage = function () {
            const totalPages = Math.ceil(filteredIndices.length / itemsPerPage);
            if ((itemsPerPage === -1 && currentPage === 1) || (itemsPerPage !== -1 && currentPage < totalPages)) {
                currentPage++;
                applyFilters();
            }
        };

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

            const totalVisible = filteredIndices.length;
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;

            // Validar página actual
            if (currentPage > totalPages) currentPage = 1;

            // 2. Paginar
            const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
            const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;
            const visibleIndices = filteredIndices.slice(start, end);

            visibleIndices.forEach(idx => {
                cards[idx].style.display = 'flex';
                if (rows[idx]) rows[idx].style.display = '';
            });

            // 3. Actualizar UI
            const visibleDisplay = document.getElementById('visibleCountDisplay');
            if (visibleDisplay) visibleDisplay.textContent = totalVisible;

            const totalDisplay = document.getElementById('totalCountDisplay');
            if (totalDisplay) totalDisplay.textContent = cards.length;

            const currentDisplay = document.getElementById('currentPageDisplay');
            if (currentDisplay) currentDisplay.textContent = currentPage;

            const pagesDisplay = document.getElementById('totalPagesDisplay');
            if (pagesDisplay) pagesDisplay.textContent = totalPages;

            const btnPrev = document.getElementById('btnPrevPage');
            const btnNext = document.getElementById('btnNextPage');

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

            if (totalVisible === 0) {
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

        // Limpiar filtros
        window.clearFilters = function () {
            currentFilter = 'todos';
            currentSearch = '';
            if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', 'todos');
            if (searchInput) searchInput.value = '';
            filterTabs.forEach(t => t.classList.remove('active'));
            if (filterTabs[0]) filterTabs[0].classList.add('active');
            currentPage = 1;
            applyFilters();
        };

        // Initial call
        applyFilters();

        // Delete Logic - Handled by SimpleDeleteModal (inline)

    };
    init();
})();

