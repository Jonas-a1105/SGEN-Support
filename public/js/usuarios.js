/**
 * Logic for Usuarios View
 */

function showTableView() {
    document.getElementById('tableView').style.display = 'block';
    document.getElementById('cardsView').style.display = 'none';
    document.getElementById('btnTableView').style.background = '#6366f1';
    document.getElementById('btnTableView').style.color = 'white';
    document.getElementById('btnTableView').style.borderColor = '#6366f1';
    document.getElementById('btnCardsView').style.background = 'white';
    document.getElementById('btnCardsView').style.color = '#64748b';
    document.getElementById('btnCardsView').style.borderColor = '#e2e8f0';
}

function showCardsView() {
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('cardsView').style.display = 'block';
    document.getElementById('btnCardsView').style.background = '#6366f1';
    document.getElementById('btnCardsView').style.color = 'white';
    document.getElementById('btnCardsView').style.borderColor = '#6366f1';
    document.getElementById('btnTableView').style.background = 'white';
    document.getElementById('btnTableView').style.color = '#64748b';
    document.getElementById('btnTableView').style.borderColor = '#e2e8f0';
}

function scrollCarousel(amount) {
    document.getElementById('cardsGrid').scrollBy({ left: amount, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('userSearchInput');
    const container = document.getElementById('usuariosContainer');

    // Variables de Paginación
    let itemsPerPage = 10;
    if (container && container.dataset.perPage) {
        itemsPerPage = parseInt(container.dataset.perPage);
    }

    let currentPage = 1;
    let filteredIndices = []; // Indices de items que coinciden con filtro

    // Init selector
    const selector = document.getElementById('itemsPerPageSelector');
    if (selector) selector.value = itemsPerPage;

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

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentPage = 1; // Reset page on search
            applyFilters();
        });
    }

    // Aplicar filtros
    function applyFilters() {
        const term = searchInput ? searchInput.value.toLowerCase() : '';
        const rows = document.querySelectorAll('.user-row');
        const cards = document.querySelectorAll('.user-card');

        filteredIndices = [];

        // 1. Filtrar (Identify matches)
        // Usamos rows como referencia para indices, cards deben coincidir en cantidad
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

        // 3. UI
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

            // Also update the empty state if needed, though this view doesn't have an explicit one, just hides table rows
        }
    }

    // Initial call
    applyFilters();
});
