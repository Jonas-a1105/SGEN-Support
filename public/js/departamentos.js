/**
 * Logic for Departamentos View
 * With User Preferences Persistence and Pagination
 */

(function () {
    const MODULE_NAME = 'departamentos';

    // Pagination state
    let currentPage = 1;
    let itemsPerPage = 12; // Default
    let allItems = [];
    let filteredItems = [];

    const init = () => {
        const gridView = document.getElementById('gridViewDept');
        const listView = document.getElementById('listViewDept');
        const emptyState = document.getElementById('emptyStateDept');
        const searchInput = document.getElementById('searchDepartamentos');
        const btnViewGrid = document.getElementById('btnViewGridDept');
        const btnViewList = document.getElementById('btnViewListDept');

        // Pagination elements
        const visibleCountDisplay = document.getElementById('visibleCountDisplay');
        const totalCountDisplay = document.getElementById('totalCountDisplay');
        const currentPageDisplay = document.getElementById('currentPageDisplay');
        const totalPagesDisplay = document.getElementById('totalPagesDisplay');
        const itemsPerPageSelector = document.getElementById('itemsPerPageSelector');
        const btnPrevPage = document.getElementById('btnPrevPage');
        const btnNextPage = document.getElementById('btnNextPage');

        // Helper function to get cookie value
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        // Helper function to set cookie
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
        }

        // Load saved preferences from GLOBAL cookie (consistent with other modules)
        let currentView = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'view', 'grid') : 'grid';

        // Read from global pagination cookie
        const savedPerPage = getCookie('sgen_pagination_per_page');

        // SAFE PARSE: Avoid NaN issues
        itemsPerPage = parseInt(savedPerPage);
        if (isNaN(itemsPerPage) || itemsPerPage === 0) {
            itemsPerPage = 12;
        }

        // Set selector to saved value
        if (itemsPerPageSelector) {
            itemsPerPageSelector.value = itemsPerPage.toString();
        }

        // Collect all items
        function collectItems() {
            allItems = [];
            if (gridView) {
                const cards = gridView.querySelectorAll('.dept-card');
                cards.forEach(card => {
                    allItems.push({
                        element: card,
                        listElement: null,
                        searchText: card.getAttribute('data-search') || ''
                    });
                });
            }
            if (listView) {
                const rows = listView.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    if (allItems[index]) {
                        allItems[index].listElement = row;
                    } else {
                        allItems.push({
                            element: null,
                            listElement: row,
                            searchText: row.getAttribute('data-search') || ''
                        });
                    }
                });
            }
            filteredItems = [...allItems];
        }

        // Apply saved view on init
        function applyView() {
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
        }

        applyView();

        // Toggle view
        if (btnViewGrid) {
            btnViewGrid.onclick = () => {
                currentView = 'grid';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'grid');
                applyView();
                renderPage();
            };
        }

        if (btnViewList) {
            btnViewList.onclick = () => {
                currentView = 'list';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'list');
                applyView();
                renderPage();
            };
        }

        // Search
        if (searchInput) {
            searchInput.oninput = () => {
                currentPage = 1;
                filterAndRender();
            };
        }

        // Pagination controls - use bound functions for proper removal
        if (itemsPerPageSelector) {
            // Remove any previous handler
            itemsPerPageSelector.removeEventListener('change', window._deptItemsPerPageHandler);
            window._deptItemsPerPageHandler = function () {
                const val = parseInt(this.value);
                if (!isNaN(val)) {
                    itemsPerPage = val;
                    currentPage = 1;
                    // Save to global cookie (consistent with other modules)
                    setCookie('sgen_pagination_per_page', itemsPerPage, 365);
                    renderPage();
                }
            };
            itemsPerPageSelector.addEventListener('change', window._deptItemsPerPageHandler);
        }

        if (btnPrevPage) {
            // Remove any previous handler
            btnPrevPage.removeEventListener('click', window._deptPrevPageHandler);
            window._deptPrevPageHandler = function () {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                }
            };
            btnPrevPage.addEventListener('click', window._deptPrevPageHandler);
        }

        if (btnNextPage) {
            // Remove any previous handler
            btnNextPage.removeEventListener('click', window._deptNextPageHandler);
            window._deptNextPageHandler = function () {
                const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(filteredItems.length / itemsPerPage) || 1;
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            };
            btnNextPage.addEventListener('click', window._deptNextPageHandler);
        }

        function filterAndRender() {
            const term = searchInput ? searchInput.value.toLowerCase() : '';

            if (term === '') {
                filteredItems = [...allItems];
            } else {
                filteredItems = allItems.filter(item => item.searchText.toLowerCase().includes(term));
            }

            renderPage();
        }

        function renderPage() {
            // Hide all items first
            allItems.forEach(item => {
                if (item.element) item.element.style.display = 'none';
                if (item.listElement) item.listElement.style.display = 'none';
            });

            // Calculate pagination
            const totalItems = filteredItems.length;
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalItems / itemsPerPage) || 1;

            // Ensure currentPage is within bounds
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            // Get items for current page
            let startIndex = 0;
            let endIndex = totalItems;

            if (itemsPerPage !== -1) {
                startIndex = (currentPage - 1) * itemsPerPage;
                endIndex = Math.min(startIndex + itemsPerPage, totalItems);
            }

            const pageItems = filteredItems.slice(startIndex, endIndex);

            // Show items for current page
            pageItems.forEach(item => {
                if (currentView === 'grid' && item.element) {
                    item.element.style.display = 'flex';
                } else if (currentView === 'list' && item.listElement) {
                    item.listElement.style.display = 'table-row';
                }
            });

            // Update displays
            if (visibleCountDisplay) visibleCountDisplay.textContent = pageItems.length;
            if (totalCountDisplay) totalCountDisplay.textContent = totalItems;
            if (currentPageDisplay) currentPageDisplay.textContent = currentPage;
            if (totalPagesDisplay) totalPagesDisplay.textContent = totalPages;

            // Update button states
            if (btnPrevPage) btnPrevPage.disabled = currentPage <= 1;
            if (btnNextPage) btnNextPage.disabled = currentPage >= totalPages;

            // Handle empty state
            if (emptyState) {
                if (totalItems === 0) {
                    emptyState.style.display = 'block';
                    if (gridView) gridView.style.display = 'none';
                    if (listView) listView.style.display = 'none';
                } else {
                    emptyState.style.display = 'none';
                    applyView();
                }
            }
        }

        // Initialize
        collectItems();
        filterAndRender();
    };

    // Clean initialization for Turbo compatibility
    // Remove previous listener if exists to avoid double binding
    if (window._deptInitHandler) {
        document.removeEventListener('turbo:load', window._deptInitHandler);
    }

    window._deptInitHandler = init;
    document.addEventListener('turbo:load', init);

    // Also run immediately if the script is loaded after turbo:load (e.g. direct refresh)
    if (document.readyState !== 'loading') {
        // Check if we're on the departamentos page
        if (document.getElementById('gridViewDept')) {
            init();
        }
    }
})();

