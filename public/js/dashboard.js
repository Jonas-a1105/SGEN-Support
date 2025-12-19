/**
 * Dashboard Logic
 * Turbo-compatible version
 */

(function () {
    // Idempotency guard for Turbo
    if (window.DASHBOARD_LOADED) return;
    window.DASHBOARD_LOADED = true;

    function showTab(tabName) {
        const listPending = document.getElementById('listPending');
        const listProcessing = document.getElementById('listProcessing');
        const tabPending = document.getElementById('tabPending');
        const tabProcessing = document.getElementById('tabProcessing');

        if (!listPending || !listProcessing || !tabPending || !tabProcessing) return;

        // Hide all
        listPending.style.display = 'none';
        listProcessing.style.display = 'none';

        // Deactivate buttons
        tabPending.classList.remove('active');
        tabProcessing.classList.remove('active');

        // Show Target
        if (tabName === 'pending') {
            listPending.style.display = 'block';
            tabPending.classList.add('active');
        } else {
            listProcessing.style.display = 'block';
            tabProcessing.classList.add('active');
        }
    }

    // Expose to global scope for onclick handlers
    window.showTab = showTab;

    function initDashboard() {
        // Dashboard initialization logic (if needed)
    }

    // Run on Turbo navigation
    document.addEventListener('turbo:load', initDashboard);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initDashboard();
    }
})();
