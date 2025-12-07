/**
 * Toast Notification System
 * Ported from React/Tailwind to Vanilla JS/CSS
 */

const Toast = {
    containerId: 'toast-container',
    duration: 4000,

    init() {
        if (!document.getElementById(this.containerId)) {
            const container = document.createElement('div');
            container.id = this.containerId;
            document.body.appendChild(container); // Appending to body, usually fits well with fixed positioning
        }
    },

    /**
     * Show a toast
     * @param {string} type 'success' | 'error' | 'neutral'
     * @param {string} message 
     * @param {function} undoAction Optional callback
     */
    show(type, message, undoAction = null) {
        this.init();
        const container = document.getElementById(this.containerId);

        // Icons map (Bootstrap Icons)
        const icons = {
            success: 'bi-check-circle',
            error: 'bi-exclamation-circle', // AlertCircle equivalent
            neutral: 'bi-info-circle'
        };
        const iconClass = icons[type] || icons.success;

        // Create Element
        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;

        // Structure
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="bi ${iconClass}" style="font-size: 1.25rem;"></i>
            </div>
            <div class="toast-content">
                <p class="toast-message">${message}</p>
            </div>
            ${undoAction ?
                `<button class="toast-undo-btn">
                    <i class="bi bi-arrow-counterclockwise"></i> UNDO
                </button>` :
                `<button class="toast-close">
                   <i class="bi bi-x-lg"></i>
                </button>`
            }
            <div class="toast-progress-track">
                <div class="toast-progress-bar" style="width: 100%;"></div>
            </div>
        `;

        // Event Handling
        const removeToast = () => {
            toast.classList.remove('show');
            toast.classList.add('removing');
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300); // Wait for transition
        };

        // Attach Click Events
        if (undoAction) {
            const undoBtn = toast.querySelector('.toast-undo-btn');
            undoBtn.addEventListener('click', () => {
                undoAction();
                removeToast();
            });
        } else {
            const closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', removeToast);
        }

        // Animation Entry
        // Use requestAnimationFrame to ensure browser registers initial state before adding 'show'
        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.add('show');

            // Start Progress Bar
            const progressBar = toast.querySelector('.toast-progress-bar');
            // Force reflow
            void progressBar.offsetWidth;
            progressBar.style.transition = `width ${this.duration}ms linear`;
            progressBar.style.width = '0%';
        });

        // Auto Dismiss
        setTimeout(() => {
            removeToast();
        }, this.duration);
    },

    success(message) {
        this.show('success', message);
    },

    error(message) {
        this.show('error', message);
    },

    info(message) {
        this.show('neutral', message);
    }
};

// Expose globally
window.Toast = Toast;
