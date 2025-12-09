/**
 * Logic for Departamentos View
 */

(function () {
    const init = () => {
        const gridView = document.getElementById('gridViewDept');
        const listView = document.getElementById('listViewDept');
        const emptyState = document.getElementById('emptyStateDept');
        const searchInput = document.getElementById('searchDepartamentos');
        const btnViewGrid = document.getElementById('btnViewGridDept');
        const btnViewList = document.getElementById('btnViewListDept');

        let currentView = 'grid';

        // Toggle view
        if (btnViewGrid) {
            btnViewGrid.addEventListener('click', () => {
                if (gridView) gridView.style.display = 'grid';
                if (listView) listView.style.display = 'none';
                btnViewGrid.classList.add('active');
                if (btnViewList) btnViewList.classList.remove('active');
                currentView = 'grid';
                filterItems();
            });
        }

        if (btnViewList) {
            btnViewList.addEventListener('click', () => {
                if (gridView) gridView.style.display = 'none';
                if (listView) listView.style.display = 'block';
                if (btnViewList) btnViewList.classList.add('active');
                if (btnViewGrid) btnViewGrid.classList.remove('active');
                currentView = 'list';
                filterItems();
            });
        }

        // Search
        if (searchInput) {
            searchInput.addEventListener('input', filterItems);
        }

        function filterItems() {
            if (!searchInput || !gridView || !listView) return;

            const term = searchInput.value.toLowerCase();
            let hasMatches = false;

            if (currentView === 'grid') {
                const cards = gridView.querySelectorAll('.dept-card');
                cards.forEach(card => {
                    const name = card.getAttribute('data-search');
                    if (name && name.includes(term)) {
                        card.style.display = 'flex';
                        hasMatches = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
            } else {
                const rows = listView.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const name = row.getAttribute('data-search');
                    if (name && name.includes(term)) {
                        row.style.display = 'table-row';
                        hasMatches = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (emptyState) {
                if (!hasMatches) {
                    emptyState.style.display = 'block';
                    if (currentView === 'grid') gridView.style.display = 'none';
                    else listView.style.display = 'none';
                } else {
                    emptyState.style.display = 'none';
                    if (currentView === 'grid') gridView.style.display = 'grid';
                    else listView.style.display = 'block';
                }
            }
        }
    };
    init();
})();
