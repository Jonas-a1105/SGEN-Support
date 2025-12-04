/**
 * TicketTabManager - Gestión moderna de tabs en vista detalle
 * 
 * Features:
 * - Remember last visited tab (localStorage)
 * - Keyboard shortcuts (Alt+1 to Alt+5)
 * - Smooth transitions
 * - Mobile responsive
 * - Tab analytics tracking
 */

class TicketTabManager {
    constructor() {
        this.tabs = {
            'info': { key: '1', icon: 'bi-ticket-detailed' },
            'files': { key: '2', icon: 'bi-paperclip' },
            'comments': { key: '3', icon: 'bi-chat-dots' },
            'notes': { key: '4', icon: 'bi-clipboard-check' },
            'signature': { key: '5', icon: 'bi-pen' }
        };

        this.activeTab = this.getStoredTab() || 'info';
        this.init();
    }

    /**
     * Initialize tab manager
     */
    init() {
        // Restore last visited tab
        this.switchToTab(this.activeTab);

        // Setup event listeners
        this.setupTabListeners();
        this.setupKeyboardShortcuts();

        // Update badge counts
        this.updateTabBadges();

        console.log('✅ TicketTabManager initialized');
    }

    /**
     * Setup tab click listeners
     */
    setupTabListeners() {
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', (e) => {
                const target = e.target.getAttribute('data-bs-target');
                const tabId = target.slice(1); // Remove #

                this.onTabShown(tabId);
            });
        });
    }

    /**
     * Handle tab shown event
     */
    onTabShown(tabId) {
        // Store in localStorage
        this.storeTab(tabId);

        // Update active tab
        this.activeTab = tabId;

        // Analytics (optional)
        this.trackTabView(tabId);

        // Trigger custom event
        window.dispatchEvent(new CustomEvent('ticket:tab:changed', {
            detail: { tabId }
        }));
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Alt + 1-5 para cambiar tabs
            if (e.altKey && e.key >= '1' && e.key <= '5') {
                const tabIds = Object.keys(this.tabs);
                const index = parseInt(e.key) - 1;

                if (tabIds[index]) {
                    this.switchToTab(tabIds[index]);
                    e.preventDefault();

                    // Visual feedback
                    ToastManager.info(`Tab: ${tabIds[index]}`, 'Atajo de teclado');
                }
            }
        });
    }

    /**
     * Switch to specific tab
     */
    switchToTab(tabId) {
        const tabEl = document.querySelector(`[data-bs-target="#${tabId}"]`);

        if (tabEl) {
            const tab = new bootstrap.Tab(tabEl);
            tab.show();
        }
    }

    /**
     * Update badge counts on tabs
     */
    updateTabBadges() {
        // Files count
        const filesCount = document.querySelectorAll('#files .list-group-item').length;
        this.updateBadge('files-tab', filesCount);

        // Comments count
        const commentsCount = document.querySelectorAll('#comments .d-flex.mb-3').length;
        this.updateBadge('comments-tab', commentsCount);
    }

    /**
     * Update a specific tab badge
     */
    updateBadge(tabId, count) {
        const tabEl = document.getElementById(tabId);
        if (!tabEl) return;

        let badge = tabEl.querySelector('.badge');

        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge bg-secondary ms-1';
                tabEl.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    /**
     * Get stored tab from localStorage
     */
    getStoredTab() {
        return localStorage.getItem('ticket_detail_last_tab');
    }

    /**
     * Store current tab in localStorage
     */
    storeTab(tabId) {
        localStorage.setItem('ticket_detail_last_tab', tabId);
    }

    /**
     * Track tab view (analytics)
     */
    trackTabView(tabId) {
        // Optional: Send to analytics
        console.log(`📊 Tab viewed: ${tabId}`);

        // Could integrate with Google Analytics, Mixpanel, etc.
        // gtag('event', 'tab_view', { tab_name: tabId });
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ticketTabs')) {
        window.ticketTabManager = new TicketTabManager();
    }
});
