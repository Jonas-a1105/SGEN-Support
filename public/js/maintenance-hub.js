/**
 * Maintenance Hub - Client Side Logic
 * Handles filtering and searching of maintenance records.
 */

document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.mh-tab-btn');
    const searchInput = document.getElementById('mhSearchInput');
    const items = document.querySelectorAll('.mh-list-item');
    const emptyState = document.getElementById('mhEmptyState');
    // Filter Dropdown Elements
    const filterBtn = document.getElementById('mhFilterBtn');
    const filterDropdown = document.getElementById('mhFilterDropdown');
    const checkPreventive = document.getElementById('filterPreventivo');
    const checkCorrective = document.getElementById('filterCorrectivo');

    let currentFilter = 'all';
    let searchTerm = '';

    // Initialize Filters (Tabs)
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Set filter
            currentFilter = btn.getAttribute('data-filter');
            applyFilters();
        });
    });

    // Initialize Search
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchTerm = e.target.value.toLowerCase();
            applyFilters();
        });
    }

    // Toggle Dropdown
    if (filterBtn && filterDropdown) {
        filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            filterDropdown.classList.toggle('show');
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
                filterDropdown.classList.remove('show');
            }
        });

        // Loop filters
        if (checkPreventive) checkPreventive.addEventListener('change', applyFilters);
        if (checkCorrective) checkCorrective.addEventListener('change', applyFilters);
    }

    function applyFilters() {
        let visibleCount = 0;

        const showPreventive = checkPreventive ? checkPreventive.checked : true;
        const showCorrective = checkCorrective ? checkCorrective.checked : true;

        items.forEach(item => {
            const status = item.getAttribute('data-status'); // pending, overdue, scheduled, completed
            const type = item.getAttribute('data-type'); // preventivo, correctivo
            const text = item.getAttribute('data-search').toLowerCase();

            // 1. Tab Status Filter
            let matchesStatus = false;

            if (currentFilter === 'all') {
                matchesStatus = true;
            } else if (currentFilter === 'pending') {
                // Pending tab includes overdue
                matchesStatus = (status === 'pending' || status === 'overdue');
            } else {
                matchesStatus = (status === currentFilter); // scheduled, completed
            }

            // 2. Type Filter (Checkbox)
            let matchesType = false;
            if (type === 'preventivo' && showPreventive) matchesType = true;
            if (type === 'correctivo' && showCorrective) matchesType = true;
            // Handle edge/default cases if type is missing or something else
            if (type !== 'preventivo' && type !== 'correctivo') matchesType = true;

            // 3. Search Filter
            const matchesSearch = text.includes(searchTerm);

            if (matchesStatus && matchesType && matchesSearch) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Toggle Empty State
        if (visibleCount === 0) {
            if (emptyState) emptyState.style.display = 'flex';
        } else {
            if (emptyState) emptyState.style.display = 'none';
        }
    }
});
