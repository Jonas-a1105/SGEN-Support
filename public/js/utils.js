/**
 * SGEN Support - Utility Classes
 * Optimizaciones de rendimiento inspiradas en TaskFlow
 */

// ============================================================================
// CACHE MANAGER - Gestión inteligente de caché con localStorage
// ============================================================================
class CacheManager {
    static prefix = 'sgen_cache_';

    /**
     * Guardar datos en caché
     * @param {string} key - Clave única para los datos
     * @param {any} data - Datos a cachear (se serializan automáticamente)
     * @param {number} ttl - Time to live en milisegundos (default: 5 min)
     */
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

    /**
     * Recuperar datos del caché
     * @param {string} key - Clave de los datos
     * @returns {any|null} Los datos cacheados o null si no existen/expiraron
     */
    static get(key) {
        try {
            const cacheKey = this.prefix + key;
            const itemStr = localStorage.getItem(cacheKey);

            if (!itemStr) return null;

            const item = JSON.parse(itemStr);

            // Verificar si expiró
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

    /**
     * Eliminar un item del caché
     * @param {string} key - Clave a eliminar
     */
    static remove(key) {
        const cacheKey = this.prefix + key;
        localStorage.removeItem(cacheKey);
    }

    /**
     * Limpiar todo el caché de SGEN
     */
    static clear() {
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith(this.prefix)) {
                localStorage.removeItem(key);
            }
        });
    }

    /**
     * Obtener estadísticas del caché
     */
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

// ============================================================================
// MODAL MANAGER - Gestión centralizada de modales Bootstrap 5
// ============================================================================
class ModalManager {
    static instances = new Map();

    /**
     * Abrir un modal 
     * @param {string} modalId - ID del modal (sin #)
     * @param {object} options - Opciones del modal
     * @returns {Promise} Promesa que resuelve cuando el modal se muestra
     */
    static open(modalId, options = {}) {
        return new Promise((resolve) => {
            const modalEl = document.getElementById(modalId);

            if (!modalEl) {
                console.error(`Modal ${modalId} no encontrado`);
                resolve(null);
                return;
            }

            // Obtener o crear instancia de Bootstrap Modal
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) {
                modal = new bootstrap.Modal(modalEl, options.config || {});
                this.instances.set(modalId, modal);
            }

            // Callbacks
            if (options.onShown) {
                modalEl.addEventListener('shown.bs.modal', options.onShown, { once: true });
            }

            if (options.onHidden) {
                modalEl.addEventListener('hidden.bs.modal', options.onHidden, { once: true });
            }

            // Resolver cuando se muestre
            modalEl.addEventListener('shown.bs.modal', () => {
                resolve(modal);
            }, { once: true });

            modal.show();
        });
    }

    /**
     * Cerrar un modal
     * @param {string} modalId - ID del modal (sin #)
     * @returns {Promise} Promesa que resuelve cuando el modal se cierra
     */
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

    /**
     * Cerrar todos los modales abiertos
     */
    static closeAll() {
        this.instances.forEach((modal, modalId) => {
            modal.hide();
        });
    }

    /**
     * Verificar si un modal está abierto
     * @param {string} modalId - ID del modal
     */
    static isOpen(modalId) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return false;

        return modalEl.classList.contains('show');
    }
}

// ============================================================================
// TOAST MANAGER - Sistema de notificaciones modernas
// ============================================================================
class ToastManager {
    static container = null;
    static toastCount = 0;
    static defaultDuration = 5000;
    static defaultPosition = 'top-end';

    /**
     * Inicializar el contenedor de toasts
     */
    static init() {
        if (this.container) return;

        this.container = document.createElement('div');
        this.container.className = `toast-container position-fixed ${this.defaultPosition} p-3`;
        this.container.style.zIndex = '9999';
        document.body.appendChild(this.container);
    }

    /**
     * Mostrar un toast
     * @param {string} message - Mensaje a mostrar
     * @param {object} options - Opciones de configuración
     */
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

        // Eliminar del DOM cuando se oculte
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });

        return toastId;
    }

    /**
     * Toast de éxito
     */
    static success(message, title = '¡Éxito!') {
        return this.show(message, { type: 'success', title });
    }

    /**
     * Toast de error
     */
    static error(message, title = 'Error') {
        return this.show(message, { type: 'error', title });
    }

    /**
     * Toast de advertencia
     */
    static warning(message, title = 'Advertencia') {
        return this.show(message, { type: 'warning', title });
    }

    /**
     * Toast de información
     */
    static info(message, title = '') {
        return this.show(message, { type: 'info', title });
    }

    /**
     * Eliminar un toast específico
     */
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

    /**
     * Eliminar todos los toasts
     */
    static clearAll() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}

// Hacer las clases disponibles globalmente
window.CacheManager = CacheManager;
window.ModalManager = ModalManager;
window.ToastManager = ToastManager;

// Log de inicialización
console.log('✅ SGEN Utils loaded:', {
    CacheManager: 'ready',
    ModalManager: 'ready',
    ToastManager: 'ready'
});
