/**
 * Soportes View Logic - Optimized 2025
 */

(function () {
    const init = () => {
        const tabs = document.querySelectorAll('.helpdesk-tab');
        const searchInput = document.getElementById('searchTickets');
        const ticketElements = document.querySelectorAll('.helpdesk-ticket');
        const ticketFeed = document.getElementById('ticketFeed');

        // Cache DOM data to avoid continuous layout thrashing
        // We Map over the NodeList once to create a pure JS array for filtering
        const allTicketsCache = Array.from(ticketElements).map((el, index) => ({
            el: el,
            estado: el.getAttribute('data-estado'),
            tecnico: el.getAttribute('data-tecnico'),
            search: el.getAttribute('data-search').toLowerCase(),
            index: index
        }));

        let currentFilter = 'todos';
        let currentSearch = '';
        let debounceTimer;

        // Variables de Paginación
        let itemsPerPage = 10;
        if (ticketFeed && ticketFeed.dataset.perPage) {
            itemsPerPage = parseInt(ticketFeed.dataset.perPage);
        }

        let currentPage = 1;
        let filteredResults = []; // Stores the cached objects that match

        // Init selector
        const selector = document.getElementById('itemsPerPageSelector');
        if (selector) selector.value = itemsPerPage;

        // --- Global Functions for Pagination Buttons ---
        window.changeClientItemsPerPage = function (val) {
            itemsPerPage = parseInt(val);
            if (window.PaginationPrefs) PaginationPrefs.set(itemsPerPage);
            else {
                const expires = new Date();
                expires.setFullYear(expires.getFullYear() + 1);
                document.cookie = 'sgen_pagination_per_page=' + itemsPerPage + ';expires=' + expires.toUTCString() + ';path=/';
            }
            currentPage = 1;
            applyFiltersAndRender();
        };

        window.prevPage = function () {
            if (currentPage > 1) {
                currentPage--;
                renderCurrentPage();
            }
        };

        window.nextPage = function () {
            const totalPages = Math.ceil(filteredResults.length / itemsPerPage);
            if ((itemsPerPage === -1 && currentPage === 1) || (itemsPerPage !== -1 && currentPage < totalPages)) {
                currentPage++;
                renderCurrentPage();
            }
        };

        // --- Event Listeners ---

        // Filter Tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                currentPage = 1;
                applyFiltersAndRender();
            });
        });

        // Search Input (Debounced)
        searchInput.addEventListener('input', function () {
            const val = this.value.toLowerCase();
            clearTimeout(debounceTimer);

            // Wait 300ms before filtering to stop UI "freezing" on fast typing
            debounceTimer = setTimeout(() => {
                currentSearch = val;
                currentPage = 1;
                applyFiltersAndRender();
            }, 300);
        });

        // --- Core Logic ---

        /**
         * Filters the cached array (pure JS, fast) and then calls render
         */
        function applyFiltersAndRender() {
            // Filter in memory, not DOM
            filteredResults = allTicketsCache.filter(item => {
                let matchesFilter = false;

                if (currentFilter === 'todos') {
                    matchesFilter = true;
                } else if (currentFilter === 'mis_tickets') {
                    matchesFilter = item.tecnico === 'mio';
                } else {
                    matchesFilter = item.estado === currentFilter;
                }

                if (!matchesFilter) return false;

                return item.search.includes(currentSearch);
            });

            renderCurrentPage();
        }

        /**
         * Writes to DOM based on pagination state
         */
        function renderCurrentPage() {
            const totalVisible = filteredResults.length;
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;

            if (currentPage > totalPages) currentPage = 1;

            // Determine slice range
            const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
            const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;

            // Identify which elements should be shown
            // We create a Set of indices for O(1) lookups
            const indicesToShow = new Set(filteredResults.slice(start, end).map(x => x.index));

            // Batch DOM updates:
            // We iterate ALL elements and set display based on the Set check.
            // This is reasonably fast because we only touch style.display.
            // For huge lists (1000+), we might want to use virtual scrolling, but for <500 this is fine.
            allTicketsCache.forEach(item => {
                const shouldShow = indicesToShow.has(item.index);
                // Only touch DOM if state changes to avoid unnecessary reflows (browser usually handles this, but good practice)
                const newDisplay = shouldShow ? 'flex' : 'none';
                if (item.el.style.display !== newDisplay) {
                    item.el.style.display = newDisplay;
                }
            });

            // Update UI Counters
            updatePaginationUI(totalVisible, totalPages);
        }

        function updatePaginationUI(totalVisible, totalPages) {
            const elVisible = document.getElementById('visibleCountDisplay');
            const elTotal = document.getElementById('totalCountDisplay');
            const elCurrent = document.getElementById('currentPageDisplay');
            const elPages = document.getElementById('totalPagesDisplay');
            const btnPrev = document.getElementById('btnPrevPage');
            const btnNext = document.getElementById('btnNextPage');
            const footer = document.getElementById('paginationFooter');

            if (elVisible) elVisible.textContent = totalVisible;
            if (elTotal) elTotal.textContent = allTicketsCache.length;
            if (elCurrent) elCurrent.textContent = currentPage;
            if (elPages) elPages.textContent = totalPages;

            if (btnPrev) {
                const disabled = currentPage === 1;
                btnPrev.disabled = disabled;
                btnPrev.style.opacity = disabled ? '0.5' : '1';
                btnPrev.style.pointerEvents = disabled ? 'none' : 'auto';
            }

            if (btnNext) {
                const disabled = currentPage === totalPages;
                btnNext.disabled = disabled;
                btnNext.style.opacity = disabled ? '0.5' : '1';
                btnNext.style.pointerEvents = disabled ? 'none' : 'auto';
            }

            if (footer) footer.style.display = 'flex';
        }

        // Initial Load
        applyFiltersAndRender();
    };

    // Execute immediately if body is ready (Turbo/Script at end of body)
    init();
})();
