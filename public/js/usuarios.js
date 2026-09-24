/**
 * Logic for Usuarios View
 * With User Preferences Persistence
 */

(function () {
    var MODULE_NAME = 'usuarios';

    function showTableView() {
        const tView = document.getElementById('tableView');
        const cView = document.getElementById('cardsView');
        if (tView) tView.style.display = 'block';
        if (cView) cView.style.display = 'none';

        const btnTable = document.getElementById('btnTableView');
        const btnCards = document.getElementById('btnCardsView');

        if (btnTable) {
            btnTable.style.background = '#6366f1';
            btnTable.style.color = 'white';
            btnTable.style.borderColor = '#6366f1';
        }
        if (btnCards) {
            btnCards.style.background = 'white';
            btnCards.style.color = '#64748b';
            btnCards.style.borderColor = '#e2e8f0';
        }

        // Save preference
        if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'table');
    }

    function showCardsView() {
        const tView = document.getElementById('tableView');
        const cView = document.getElementById('cardsView');
        if (tView) tView.style.display = 'none';
        if (cView) cView.style.display = 'block';

        const btnTable = document.getElementById('btnTableView');
        const btnCards = document.getElementById('btnCardsView');

        if (btnCards) {
            btnCards.style.background = '#6366f1';
            btnCards.style.color = 'white';
            btnCards.style.borderColor = '#6366f1';
        }
        if (btnTable) {
            btnTable.style.background = 'white';
            btnTable.style.color = '#64748b';
            btnTable.style.borderColor = '#e2e8f0';
        }

        // Save preference
        if (window.UserPrefs) UserPrefs.set(MODULE_NAME, 'view', 'cards');
    }

    window.scrollCarousel = function (amount) {
        const grid = document.getElementById('cardsGrid');
        if (grid) grid.scrollBy({ left: amount, behavior: 'smooth' });
    };

    // Initialize Logic
    function initUsuariosView() {
        const searchInput = document.getElementById('userSearchInput');
        const container = document.getElementById('usuariosContainer');

        if (!container) return; // Only run on users page

        // Apply saved view preference
        const savedView = window.UserPrefs ? UserPrefs.get(MODULE_NAME, 'view', 'table') : 'table';
        if (savedView === 'cards') {
            showCardsView();
        } else {
            showTableView();
        }

        // Variables de Paginación
        let itemsPerPage = 10;
        if (container && container.dataset.perPage) {
            itemsPerPage = parseInt(container.dataset.perPage);
        }

        let currentPage = 1;
        let filteredIndices = [];

        // App Logic Functions (defined inside init to access variables)
        function applyFilters() {
            const term = searchInput ? searchInput.value.toLowerCase() : '';
            const rows = document.querySelectorAll('.user-row');
            const cards = document.querySelectorAll('.user-card');

            filteredIndices = [];

            // 1. Filtrar (Identify matches)
            rows.forEach((row, index) => {
                const card = cards[index];
                const text = row.textContent.toLowerCase();
                const matchesSearch = text.includes(term);

                // Ocultar inicialmente
                row.style.display = 'none';
                if (card) card.style.display = 'none';

                if (matchesSearch) {
                    filteredIndices.push(index);
                }
            });

            const totalVisible = filteredIndices.length;
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(totalVisible / itemsPerPage) || 1;

            if (currentPage > totalPages) currentPage = 1;

            // 2. Paginar
            const start = itemsPerPage === -1 ? 0 : (currentPage - 1) * itemsPerPage;
            const end = itemsPerPage === -1 ? totalVisible : start + itemsPerPage;
            const visibleIndices = filteredIndices.slice(start, end);

            visibleIndices.forEach(idx => {
                if (rows[idx]) rows[idx].style.display = '';
                if (cards[idx]) {
                    cards[idx].style.display = '';
                }
            });

            // 3. UI Updates
            const visibleCountDisplay = document.getElementById('visibleCountDisplay');
            if (visibleCountDisplay) visibleCountDisplay.textContent = visibleIndices.length;

            const totalCountDisplay = document.getElementById('totalCountDisplay');
            if (totalCountDisplay) totalCountDisplay.textContent = rows.length;

            const currentPageDisplay = document.getElementById('currentPageDisplay');
            if (currentPageDisplay) currentPageDisplay.textContent = currentPage;

            const totalPagesDisplay = document.getElementById('totalPagesDisplay');
            if (totalPagesDisplay) totalPagesDisplay.textContent = totalPages;

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

            // Hide footer if no items
            const footer = document.getElementById('paginationFooter');
            if (footer) {
                if (visibleIndices.length === 0) footer.style.display = 'none';
                else footer.style.display = 'flex';
            }
        }

        // Init selector
        const selector = document.getElementById('itemsPerPageSelector');
        if (selector) selector.value = itemsPerPage;

        // Expose pagination functions to window
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
            const totalPages = itemsPerPage === -1 ? 1 : Math.ceil(filteredIndices.length / itemsPerPage) || 1;
            if (currentPage < totalPages) {
                currentPage++;
                applyFilters();
            }
        };

        // Element-Level Idempotency
        if (searchInput) {
            searchInput.oninput = function () {
                currentPage = 1;
                applyFilters();
            };
        }

        // View Toggles Binding
        const btnTable = document.getElementById('btnTableView');
        const btnCards = document.getElementById('btnCardsView');
        if (btnTable) btnTable.onclick = showTableView;
        if (btnCards) btnCards.onclick = showCardsView;

        // Initial call
        applyFilters();
    }

    // Clean initialization for Turbo compatibility
    if (window._usuariosInitHandler) {
        document.removeEventListener('turbo:load', window._usuariosInitHandler);
    }

    window._usuariosInitHandler = initUsuariosView;
    document.addEventListener('turbo:load', initUsuariosView);

    // Also run immediately if on usuarios page
    if (document.readyState !== 'loading') {
        if (document.getElementById('usuariosContainer')) {
            initUsuariosView();
        }
    }
})();
