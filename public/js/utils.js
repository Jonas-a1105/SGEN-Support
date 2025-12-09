/**
 * SGEN Support - Utility Classes
 * Optimizaciones de rendimiento inspiradas en TaskFlow
 */

// ============================================================================
// CACHE MANAGER - Gestión inteligente de caché con localStorage
// ============================================================================
if (!window.CacheManager) {
    class CacheManager {
        static prefix = 'sgen_cache_';

        static set(key, data, ttl = 300000) {
            try {
                const cacheKey = this.prefix + key;
                const item = {
                    data,
                    expires: Date.now() + ttl,
                    timestamp: Date.now()
                };
                localStorage.setItem(cacheKey, JSON.stringify(item));
                return true;
            } catch (error) {
                console.error('Error guardando en caché:', error);
                return false;
            }
        }

        static get(key) {
            try {
                const cacheKey = this.prefix + key;
                const itemStr = localStorage.getItem(cacheKey);

                if (!itemStr) return null;

                const item = JSON.parse(itemStr);

                if (Date.now() > item.expires) {
                    this.remove(key);
                    return null;
                }

                return item.data;
            } catch (error) {
                console.error('Error recuperando del caché:', error);
                return null;
            }
        }

        static remove(key) {
            const cacheKey = this.prefix + key;
            localStorage.removeItem(cacheKey);
        }

        static clear() {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith(this.prefix)) {
                    localStorage.removeItem(key);
                }
            });
        }

        static stats() {
            const keys = Object.keys(localStorage);
            const cacheKeys = keys.filter(k => k.startsWith(this.prefix));

            let totalSize = 0;
            let validItems = 0;
            let expiredItems = 0;

            cacheKeys.forEach(key => {
                const itemStr = localStorage.getItem(key);
                totalSize += itemStr.length;

                try {
                    const item = JSON.parse(itemStr);
                    if (Date.now() > item.expires) {
                        expiredItems++;
                    } else {
                        validItems++;
                    }
                } catch (e) {
                    expiredItems++;
                }
            });

            return {
                totalItems: cacheKeys.length,
                validItems,
                expiredItems,
                sizeKB: (totalSize / 1024).toFixed(2)
            };
        }
    }
    window.CacheManager = CacheManager;
}

// ============================================================================
// MODAL MANAGER - Gestión centralizada de modales Bootstrap 5
// ============================================================================
if (!window.ModalManager) {
    class ModalManager {
        static instances = new Map();

        static open(modalId, options = {}) {
            return new Promise((resolve) => {
                const modalEl = document.getElementById(modalId);

                if (!modalEl) {
                    console.error(`Modal ${modalId} no encontrado`);
                    resolve(null);
                    return;
                }

                let modal = bootstrap.Modal.getInstance(modalEl);
                if (!modal) {
                    modal = new bootstrap.Modal(modalEl, options.config || {});
                    this.instances.set(modalId, modal);
                }

                if (options.onShown) {
                    modalEl.addEventListener('shown.bs.modal', options.onShown, { once: true });
                }

                if (options.onHidden) {
                    modalEl.addEventListener('hidden.bs.modal', options.onHidden, { once: true });
                }

                modalEl.addEventListener('shown.bs.modal', () => {
                    resolve(modal);
                }, { once: true });

                modal.show();
            });
        }

        static close(modalId) {
            return new Promise((resolve) => {
                const modalEl = document.getElementById(modalId);

                if (!modalEl) {
                    resolve();
                    return;
                }

                const modal = bootstrap.Modal.getInstance(modalEl);

                if (!modal) {
                    resolve();
                    return;
                }

                modalEl.addEventListener('hidden.bs.modal', () => {
                    resolve();
                }, { once: true });

                modal.hide();
            });
        }

        static closeAll() {
            this.instances.forEach((modal, modalId) => {
                modal.hide();
            });
        }

        static isOpen(modalId) {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) return false;

            return modalEl.classList.contains('show');
        }
    }
    window.ModalManager = ModalManager;
}

// ============================================================================
// TOAST MANAGER - Sistema de notificaciones modernas
// ============================================================================
if (!window.ToastManager) {
    class ToastManager {
        static container = null;
        static toastCount = 0;
        static defaultDuration = 5000;
        static defaultPosition = 'top-end';

        static init() {
            this.container = document.querySelector('.toast-container');
            if (this.container) return;

            this.container = document.createElement('div');
            this.container.className = `toast-container position-fixed ${this.defaultPosition} p-3`;
            this.container.style.zIndex = '9999';
            document.body.appendChild(this.container);
        }

        static show(message, options = {}) {
            this.init();

            const {
                type = 'info',
                title = '',
                duration = this.defaultDuration,
                icon = true,
                closeable = true
            } = options;

            const toastId = `toast-${this.toastCount++}`;

            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };

            const bgColors = {
                success: 'bg-success',
                error: 'bg-danger',
                warning: 'bg-warning',
                info: 'bg-primary'
            };

            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white ${bgColors[type] || bgColors.info} border-0`;
            toast.id = toastId;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            let iconHTML = '';
            if (icon) {
                iconHTML = `<i class="bi ${icons[type] || icons.info} me-2"></i>`;
            }

            let titleHTML = '';
            if (title) {
                titleHTML = `<strong class="me-auto">${iconHTML}${title}</strong>`;
            } else {
                titleHTML = iconHTML;
            }

            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${titleHTML}
                        ${title ? '<div class="mt-1">' + message + '</div>' : message}
                    </div>
                    ${closeable ? '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' : ''}
                </div>
            `;

            this.container.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast, {
                autohide: duration > 0,
                delay: duration
            });

            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });

            return toastId;
        }

        static success(message, title = '¡Éxito!') {
            return this.show(message, { type: 'success', title });
        }

        static error(message, title = 'Error') {
            return this.show(message, { type: 'error', title });
        }

        static warning(message, title = 'Advertencia') {
            return this.show(message, { type: 'warning', title });
        }

        static info(message, title = '') {
            return this.show(message, { type: 'info', title });
        }

        static remove(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                const bsToast = bootstrap.Toast.getInstance(toast);
                if (bsToast) {
                    bsToast.hide();
                } else {
                    toast.remove();
                }
            }
        }

        static clearAll() {
            if (this.container) {
                this.container.innerHTML = '';
            }
        }
    }
    window.ToastManager = ToastManager;
}

// Log de inicialización
console.log('✅ SGEN Utils loaded:', {
    CacheManager: 'ready',
    ModalManager: 'ready',
    ToastManager: 'ready'
});
