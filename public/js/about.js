/**
 * About Page Logic
 * Turbo-compatible version.
 */
(function () {
    // Idempotency guard
    if (window.ABOUT_PAGE_LOADED) return;
    window.ABOUT_PAGE_LOADED = true;

    window.AboutPage = {
        tabs: null,
        panes: null,

        init() {
            this.tabs = document.querySelectorAll('.nav-btn');
            this.panes = document.querySelectorAll('.tab-pane');

            if (!this.tabs.length) return;

            this.tabs.forEach(tab => {
                tab.addEventListener('click', (e) => this.switchTab(e));
            });
        },

        switchTab(e) {
            const targetId = e.currentTarget.dataset.target;

            this.tabs.forEach(tab => tab.classList.remove('active'));
            this.panes.forEach(pane => pane.classList.remove('active'));

            e.currentTarget.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        }
    };

    function initAboutPage() {
        if (document.querySelector('.nav-btn')) {
            window.AboutPage.init();
        }
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initAboutPage);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initAboutPage();
    }
})();
