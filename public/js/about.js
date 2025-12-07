/**
 * About Page Logic
 * Handles tab switching for the modernization layout.
 */

const AboutPage = {
    init() {
        this.tabs = document.querySelectorAll('.nav-btn');
        this.panes = document.querySelectorAll('.tab-pane');

        this.tabs.forEach(tab => {
            tab.addEventListener('click', (e) => this.switchTab(e));
        });
    },

    switchTab(e) {
        const targetId = e.currentTarget.dataset.target;

        // Remove active class from all tabs and panes
        this.tabs.forEach(tab => tab.classList.remove('active'));
        this.panes.forEach(pane => pane.classList.remove('active'));

        // Add active class to clicked tab and target pane
        e.currentTarget.classList.add('active');
        const targetPane = document.getElementById(targetId);
        if (targetPane) {
            targetPane.classList.add('active');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AboutPage.init();
});
