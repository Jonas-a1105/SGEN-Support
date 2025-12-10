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

        // Load saved preferences
        let currentView = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'view', 'grid') : 'grid';
        const savedPerPage = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'perPage', 12) : 12;
        itemsPerPage = parseInt(savedPerPage);

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
            btnViewGrid.addEventListener('click', () => {
                currentView = 'grid';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'grid');
                applyView();
                renderPage();
            });
        }

        if (btnViewList) {
            btnViewList.addEventListener('click', () => {
                currentView = 'list';
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'list');
                applyView();
                renderPage();
            });
        }

        // Search
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                currentPage = 1;
                filterAndRender();
            });
        }

        // Pagination controls
        if (itemsPerPageSelector) {
            itemsPerPageSelector.addEventListener('change', function () {
                itemsPerPage = parseInt(this.value);
                currentPage = 1;
                if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'perPage', itemsPerPage);
                renderPage();
            });
        }

        if (btnPrevPage) {
            btnPrevPage.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                }
            });
        }

        if (btnNextPage) {
            btnNextPage.addEventListener('click', () => {
                const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(filteredItems.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            });
        }

        function filterAndRender() {
            const term = searchInput ? searchInput.value.toLowerCase() : '';

            if (term === '') {
                filteredItems = [...allItems];
            } else {
                filteredItems = allItems.filter(item => item.searchText.includes(term));
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

    document.addEventListener('DOMContentLoaded', init);
})();

