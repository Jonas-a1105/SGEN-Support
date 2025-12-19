/**
 * TicketTabManager - Gestión moderna de tabs en vista detalle
 * Turbo-compatible version.
 */
(function () {
    // Idempotency guard
    if (window.TICKET_TABS_LOADED) return;
    window.TICKET_TABS_LOADED = true;

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
            this.keyboardHandler = null;
        }

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

        destroy() {
            // Remove keyboard listener
            if (this.keyboardHandler) {
                document.removeEventListener('keydown', this.keyboardHandler);
            }
        }

        setupTabListeners() {
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
                tabEl.addEventListener('shown.bs.tab', (e) => {
                    const target = e.target.getAttribute('data-bs-target');
                    const tabId = target.slice(1);
                    this.onTabShown(tabId);
                });
            });
        }

        onTabShown(tabId) {
            this.storeTab(tabId);
            this.activeTab = tabId;
            this.trackTabView(tabId);

            window.dispatchEvent(new CustomEvent('ticket:tab:changed', {
                detail: { tabId }
            }));
        }

        setupKeyboardShortcuts() {
            this.keyboardHandler = (e) => {
                if (e.altKey && e.key >= '1' && e.key <= '5') {
                    const tabIds = Object.keys(this.tabs);
                    const index = parseInt(e.key) - 1;

                    if (tabIds[index]) {
                        this.switchToTab(tabIds[index]);
                        e.preventDefault();

                        if (window.ToastManager) {
                            ToastManager.info(`Tab: ${tabIds[index]}`, 'Atajo de teclado');
                        }
                    }
                }
            };

            document.addEventListener('keydown', this.keyboardHandler);
        }

        switchToTab(tabId) {
            const tabEl = document.querySelector(`[data-bs-target="#${tabId}"]`);

            if (tabEl && window.bootstrap) {
                const tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        }

        updateTabBadges() {
            const filesCount = document.querySelectorAll('#files .list-group-item').length;
            this.updateBadge('files-tab', filesCount);

            const commentsCount = document.querySelectorAll('#comments .d-flex.mb-3').length;
            this.updateBadge('comments-tab', commentsCount);
        }

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

        getStoredTab() {
            return localStorage.getItem('ticket_detail_last_tab');
        }

        storeTab(tabId) {
            localStorage.setItem('ticket_detail_last_tab', tabId);
        }

        trackTabView(tabId) {
            console.log(`📊 Tab viewed: ${tabId}`);
        }
    }

    // Expose class globally
    window.TicketTabManager = TicketTabManager;

    function initTabs() {
        if (document.getElementById('ticketTabs')) {
            // Destroy previous instance if exists
            if (window.ticketTabManager) {
                window.ticketTabManager.destroy();
            }
            window.ticketTabManager = new TicketTabManager();
            window.ticketTabManager.init();
        }
    }

    // Cleanup on navigation away
    document.addEventListener('turbo:before-cache', function () {
        if (window.ticketTabManager) {
            window.ticketTabManager.destroy();
        }
    });

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTabs);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTabs();
    }
})();
