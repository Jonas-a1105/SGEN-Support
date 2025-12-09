/**
 * Dashboard Logic
 */

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

// Initialize on load if needed
document.addEventListener('DOMContentLoaded', () => {
    // Optional: Auto-refresh charts or data here
});
