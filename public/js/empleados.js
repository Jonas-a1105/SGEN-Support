/**
 * Logic for Empleados View
 * With User Preferences Persistence
 */

(function () {
    const MODULE_NAME = 'empleados';

    const init = () => {
        const gridView = document.getElementById('gridViewEmp');
        const listView = document.getElementById('listViewEmp');
        const emptyState = document.getElementById('emptyStateEmp');
        const btnViewList = document.getElementById('btnViewListEmp');
        const btnViewGrid = document.getElementById('btnViewGridEmp');
        const searchInput = document.getElementById('searchEmpleados');
        const filterBtns = document.querySelectorAll('.dept-filter-btn');

        // Load saved preferences
        let currentFilter = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'filter', 'todos') : 'todos';
        let currentSearch = '';
        let currentView = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'view', 'grid') : 'grid';

        // Apply saved filter on init
        if (currentFilter !== 'todos') {
            filterBtns.forEach(b => {
                b.classList.remove('active');
                if (b.getAttribute('data-dept') === currentFilter) {
                    b.classList.add('active');
                }
            });
        }

        // Apply saved view on init
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
                if (btnViewList) btnViewList.classList.remove('active');
                applyFilters();
            };
        }

        if (btnViewList) {
            btnViewList.onclick = function () {
                currentView = 'list';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'list');
                if (gridView) gridView.style.display = 'none';
                if (listView) listView.style.display = 'block';
                if (btnViewList) btnViewList.classList.add('active');
                if (btnViewGrid) btnViewGrid.classList.remove('active');
                applyFilters();
            };
        }

        // Filtros de departamento
        filterBtns.forEach(btn => {
            btn.onclick = function () {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-dept');
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', currentFilter);
                applyFilters();
            };
        });

        // Filtrar por dept desde badge
        window.filterByDept = function (dept) {
            currentFilter = dept;
            if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', dept);
            filterBtns.forEach(b => {
                b.classList.remove('active');
                if (b.getAttribute('data-dept') === dept) {
                    b.classList.add('active');
                }
            });
            applyFilters();
        };

        // Búsqueda
        if (searchInput) {
            searchInput.oninput = function () {
                currentSearch = this.value.toLowerCase();
                applyFilters();
            };
        }

        // Variables de Paginación
        let itemsPerPage = 10;
        const savedPerPage = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'perPage', 10) : 10;

        // SAFE PARSE
        itemsPerPage = parseInt(savedPerPage);
        if (isNaN(itemsPerPage) || itemsPerPage === 0) {
            itemsPerPage = 10;
        }

        let currentPage = 1;
        let filteredIndices = [];

        // Init selector
        const selector = document.getElementById('itemsPerPageSelector');
        if (selector) selector.value = itemsPerPage.toString();

        const btnPrev = document.getElementById('btnPrevPage');
        const btnNext = document.getElementById('btnNextPage');

        if (selector) {
            selector.removeEventListener('change', window._empleadosItemsPerPageHandler);
            window._empleadosItemsPerPageHandler = function () {
                const val = parseInt(this.value);
                if (!isNaN(val)) {
                    itemsPerPage = val;
                    if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'perPage', itemsPerPage);
                    currentPage = 1;
                    applyFilters();
                }
            };
            selector.addEventListener('change', window._empleadosItemsPerPageHandler);
        }

        if (btnPrev) {
            btnPrev.removeEventListener('click', window._empleadosPrevPageHandler);
            window._empleadosPrevPageHandler = function () {
                if (currentPage > 1) {
                    currentPage--;
                    applyFilters();
                }
            };
            btnPrev.addEventListener('click', window._empleadosPrevPageHandler);
        }

        if (btnNext) {
            btnNext.removeEventListener('click', window._empleadosNextPageHandler);
            window._empleadosNextPageHandler = function () {
                const totalVisible = filteredIndices.length;
                const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;
                if (currentPage < totalPages) {
                    currentPage++;
                    applyFilters();
                }
            };
            btnNext.addEventListener('click', window._empleadosNextPageHandler);
        }

        // Aplicar filtros
        function applyFilters() {
            if (!gridView || !listView) return;

            // Reset indices
            filteredIndices = [];
            const cards = gridView.querySelectorAll('.empleado-card');
            const rows = listView.querySelectorAll('tbody tr');

            // 1. Filtrar (Identify matches)
            cards.forEach((card, index) => {
                const dept = card.getAttribute('data-dept');
                const searchText = card.getAttribute('data-search') ? card.getAttribute('data-search').toLowerCase() : '';
                const matchesFilter = currentFilter === 'todos' || dept === currentFilter;
                const matchesSearch = searchText.includes(currentSearch);

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

            // 2. Paginar (Show slice)
            const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
            const end = itemsPerPage === -1 ? totalFilteredCount : start + itemsPerPage;
            const visibleIndices = filteredIndices.slice(start, end);

            visibleIndices.forEach(idx => {
                cards[idx].style.display = 'flex';
                if (rows[idx]) rows[idx].style.display = '';
            });

            // 3. Actualizar UI
            const visibleCountDisplay = document.getElementById('visibleCountDisplay');
            const totalCountDisplay = document.getElementById('totalCountDisplay');
            if (visibleCountDisplay) visibleCountDisplay.textContent = visibleIndices.length;
            if (totalCountDisplay) totalCountDisplay.textContent = totalFilteredCount;

            const currentPageDisplay = document.getElementById('currentPageDisplay');
            const totalPagesDisplay = document.getElementById('totalPagesDisplay');
            if (currentPageDisplay) currentPageDisplay.textContent = currentPage;
            if (totalPagesDisplay) totalPagesDisplay.textContent = totalPages;

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

            // Empty state
            const footer = document.getElementById('paginationFooter');
            if (visibleIndices.length === 0) {
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
        const btnClear = document.querySelector('[onclick="clearFiltersEmp()"]');
        if (btnClear) {
            btnClear.removeAttribute('onclick');
            btnClear.onclick = function () {
                currentFilter = 'todos';
                currentSearch = '';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'filter', 'todos');
                if (searchInput) searchInput.value = '';
                filterBtns.forEach(b => {
                    b.classList.remove('active');
                    if (b.getAttribute('data-dept') === 'todos') {
                        b.classList.add('active');
                    }
                });
                currentPage = 1;
                applyFilters();
            };
        }

        // Initial call
        applyFilters();

        // Copiar email (keep global if needed, or local)
        window.copyEmail = function (email, element) {
            if (!email) return;

            navigator.clipboard.writeText(email).then(() => {
                const originalHTML = element.innerHTML;
                const copyIcon = element.querySelector('.copy-icon');
                if (copyIcon) {
                    copyIcon.outerHTML = '<span class="copied-feedback"><i class="bi bi-check"></i> Copiado</span>';
                }

                setTimeout(() => {
                    element.innerHTML = originalHTML;
                }, 2000);
            });
        };
    };

    // Clean initialization for Turbo compatibility
    if (window._empleadosInitHandler) {
        document.removeEventListener('turbo:load', window._empleadosInitHandler);
    }

    window._empleadosInitHandler = init;
    document.addEventListener('turbo:load', init);

    // Also run immediately if on empleados page
    if (document.readyState !== 'loading') {
        if (document.getElementById('gridViewEmp')) {
            init();
        }
    }
})();
