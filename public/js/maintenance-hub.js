/**
 * Maintenance Hub - Client Side Logic
 * Handles filtering and searching of maintenance records.
 */

// Global variables for pagination
window.mhCurrentPage = 1;
window.mhItemsPerPage = parseInt(localStorage.getItem('mh_per_page')) || 10;

function mhGetFilteredItems() {
    const items = document.querySelectorAll('.mh-list-item');
    return Array.from(items).filter(item => item.dataset.filtered !== 'false');
}

function mhUpdatePagination() {
    const filteredItems = mhGetFilteredItems();
    const totalFiltered = filteredItems.length;
    const totalPages = Math.ceil(totalFiltered / window.mhItemsPerPage) || 1;

    if (window.mhCurrentPage > totalPages) window.mhCurrentPage = totalPages;
    if (window.mhCurrentPage < 1) window.mhCurrentPage = 1;

    // Update display
    const currentPageEl = document.getElementById('mhCurrentPage');
    const totalPagesEl = document.getElementById('mhTotalPages');
    const visibleCountEl = document.getElementById('mhVisibleCount');

    if (currentPageEl) currentPageEl.textContent = window.mhCurrentPage;
    if (totalPagesEl) totalPagesEl.textContent = totalPages;

    // Enable/disable buttons
    const btnPrev = document.getElementById('mhBtnPrev');
    const btnNext = document.getElementById('mhBtnNext');

    if (btnPrev) btnPrev.disabled = window.mhCurrentPage === 1;
    if (btnNext) btnNext.disabled = window.mhCurrentPage === totalPages;

    // Show/hide items based on pagination
    let visibleCount = 0;
    const start = (window.mhCurrentPage - 1) * window.mhItemsPerPage;
    const end = start + window.mhItemsPerPage;

    let filteredIndex = 0;
    filteredItems.forEach(item => {
        if (filteredIndex >= start && filteredIndex < end) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
        filteredIndex++;
    });

    if (visibleCountEl) visibleCountEl.textContent = visibleCount;
}

function mhPreviousPage() {
    if (window.mhCurrentPage > 1) {
        window.mhCurrentPage--;
        mhUpdatePagination();
    }
}

function mhNextPage() {
    const filteredItems = mhGetFilteredItems();
    const totalPages = Math.ceil(filteredItems.length / window.mhItemsPerPage);
    if (window.mhCurrentPage < totalPages) {
        window.mhCurrentPage++;
        mhUpdatePagination();
    }
}

function mhChangeItemsPerPage() {
    const select = document.getElementById('mhItemsPerPage');
    if (select) {
        window.mhItemsPerPage = parseInt(select.value);
        window.mhCurrentPage = 1;
        localStorage.setItem('mh_per_page', window.mhItemsPerPage);
        mhUpdatePagination();
    }
}

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
    const itemsPerPageSelect = document.getElementById('mhItemsPerPage');

    // Load saved filter
    let currentFilter = localStorage.getItem('mh_filter') || 'all';
    let searchTerm = '';

    // Initialize Select
    if (itemsPerPageSelect) {
        itemsPerPageSelect.value = window.mhItemsPerPage;
    }

    // Initialize Active Tab
    filterButtons.forEach(btn => {
        if (btn.getAttribute('data-filter') === currentFilter) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }

        btn.addEventListener('click', () => {
            // Update active state
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Set filter
            currentFilter = btn.getAttribute('data-filter');
            localStorage.setItem('mh_filter', currentFilter);

            // Reset page on filter change
            window.mhCurrentPage = 1;
            applyFilters();
        });
    });

    // Initialize Search
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchTerm = e.target.value.toLowerCase();
            window.mhCurrentPage = 1;
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
            // Normalize type check
            const itemType = (type || '').toLowerCase();
            if (itemType.includes('preventivo') && showPreventive) matchesType = true;
            else if (itemType.includes('correctivo') && showCorrective) matchesType = true;
            else if (!itemType.includes('preventivo') && !itemType.includes('correctivo')) matchesType = true; // Other types

            // 3. Search Filter
            const matchesSearch = text.includes(searchTerm);

            // Store filter match status for pagination
            if (matchesStatus && matchesType && matchesSearch) {
                item.dataset.filtered = 'true';
                visibleCount++;
            } else {
                item.dataset.filtered = 'false';
                item.style.display = 'none';
            }
        });

        // Apply pagination after filtering
        mhUpdatePagination();

        // Toggle Empty State
        // Only show "No results found" if we actually have items in the database (items.length > 0)
        // If items.length is 0, the PHP empty state ("Sin mantenimientos") is already visible.
        if (visibleCount === 0 && items.length > 0) {
            if (emptyState) emptyState.classList.remove('d-none');
        } else {
            if (emptyState) emptyState.classList.add('d-none');
        }
    }

    // Initial Run
    applyFilters();

    // --- TIMER WATCHING FOR LIST VIEW ---
    function initListTimers() {
        const timerElements = document.querySelectorAll('.maintenance-timer-list');

        timerElements.forEach(timerEl => {
            const startTime = new Date(timerEl.dataset.start).getTime();
            const endTime = new Date(timerEl.dataset.end).getTime();
            const status = timerEl.dataset.status;

            function updateTimer() {
                const now = Date.now();
                const listItem = timerEl.closest('.mh-list-item');

                if (status === 'pendiente') {
                    // Countdown to start
                    const remaining = startTime - now;
                    if (remaining <= 0) {
                        timerEl.textContent = 'Iniciando...';
                        return;
                    }
                    timerEl.textContent = formatTime(remaining);
                } else if (status === 'en_proceso') {
                    // Countdown to end
                    const remaining = endTime - now;
                    if (remaining <= 0) {
                        // Timer expired - mark as resolved
                        timerEl.textContent = '00:00';

                        // Hide the timer badge
                        const timerBadge = timerEl.closest('.badge');
                        if (timerBadge) timerBadge.style.display = 'none';

                        // Update status badge to "Realizado"
                        const statusBadge = listItem.querySelector('.mh-status-badge');
                        if (statusBadge) {
                            statusBadge.className = 'mh-status-badge rounded-pill bg-emerald-100 text-emerald-700 border-0 d-inline-flex align-items-center gap-2 px-3 py-2';
                            statusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Realizado';
                        }

                        // Add green checkmark to icon
                        const iconContainer = listItem.querySelector('.mh-item-icon');
                        if (iconContainer && !iconContainer.querySelector('.resolved-check')) {
                            const checkmark = document.createElement('span');
                            checkmark.className = 'resolved-check position-absolute';
                            checkmark.style = 'bottom: -2px; right: -2px; background: #10b981; border-radius: 50%; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;';
                            checkmark.innerHTML = '<i class="bi bi-check text-white" style="font-size: 10px;"></i>';
                            iconContainer.classList.add('position-relative');
                            iconContainer.appendChild(checkmark);
                        }

                        // Update data-status for filtering
                        listItem.dataset.status = 'completed';

                        return; // Stop updating
                    }
                    timerEl.textContent = formatTime(remaining);
                }
            }

            function formatTime(ms) {
                const totalSeconds = Math.floor(ms / 1000);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                if (hours > 0) {
                    return `${hours}h ${String(minutes).padStart(2, '0')}m`;
                }
                return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }

            // Update immediately
            updateTimer();
            // Update every second
            setInterval(updateTimer, 1000);
        });
    }

    // Initialize timers
    initListTimers();
});
