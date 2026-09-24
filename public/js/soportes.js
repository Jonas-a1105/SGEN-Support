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
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(filteredResults.length / itemsPerPage) || 1;
            if (currentPage < totalPages) {
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
        if (searchInput) {
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
        }

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

        // --- Sincronización en Tiempo Real (Smart Polling) ---
        let lastUpdateCheck = Date.now();
        const pollingInterval = 20000; // 20 seconds (optimized for LAN)

        const checkUpdates = async () => {
            console.log('Checking for ticket updates...');
            try {
                // Fetch the current list again via AJAX
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTickets = doc.querySelectorAll('.helpdesk-ticket');

                if (newTickets.length > 0) {
                    processTicketUpdates(newTickets);
                }
            } catch (error) {
                console.error('Error polling updates:', error);
            }
        };

        const processTicketUpdates = (newTicketElements) => {
            let hasChanges = false;

            newTicketElements.forEach(newEl => {
                const id = newEl.getAttribute('data-id') || newEl.id;
                const oldEl = document.querySelector(`.helpdesk-ticket[data-id="${id}"], .helpdesk-ticket#${id}`);

                if (oldEl) {
                    const oldState = oldEl.getAttribute('data-estado');
                    const newState = newEl.getAttribute('data-estado');

                    if (oldState !== newState) {
                        console.log(`Ticket ${id} changed state: ${oldState} -> ${newState}`);
                        // Update the DOM element
                        oldEl.setAttribute('data-estado', newState);
                        oldEl.innerHTML = newEl.innerHTML;

                        // Update the cache
                        const cacheItem = allTicketsCache.find(item => item.el === oldEl);
                        if (cacheItem) {
                            cacheItem.estado = newState;
                            cacheItem.tecnico = newEl.getAttribute('data-tecnico');
                            cacheItem.search = newEl.getAttribute('data-search').toLowerCase();
                        }
                        hasChanges = true;
                    }
                } else {
                    // New ticket found
                    console.log(`New ticket detected: ${id}`);
                    if (window.showDesktopNotification) {
                        window.showDesktopNotification('Nuevo Ticket', `Se ha recibido el ticket #${id}`);
                    }
                    hasChanges = true;
                }
            });

            if (hasChanges) {
                applyFiltersAndRender();
                showUpdateToast();
            }
        };

        const showUpdateToast = () => {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'info',
                    title: 'Tickets actualizados automáticamente'
                });
            }
        };

        // Start polling if not already started
        if (!window._soportesPollingInterval) {
            window._soportesPollingInterval = setInterval(checkUpdates, pollingInterval);
        }

        // Initial Load
        applyFiltersAndRender();
    };

    // Clean initialization for Turbo compatibility
    // Remove previous listener if exists to avoid double binding
    if (window._soportesInitHandler) {
        document.removeEventListener('turbo:load', window._soportesInitHandler);
    }

    window._soportesInitHandler = init;
    document.addEventListener('turbo:load', init);

    // Cleanup on before-cache for Turbo
    document.addEventListener('turbo:before-cache', () => {
        if (window._soportesPollingInterval) {
            clearInterval(window._soportesPollingInterval);
            window._soportesPollingInterval = null;
        }
    });

    // Also run on first load if DOM is ready (for non-Turbo scenarios)
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // Check if we're on the soportes page
        if (document.getElementById('ticketFeed')) {
            init();
        }
    }
})();
