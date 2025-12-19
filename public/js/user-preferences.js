/**
 * User Preferences Module
 * Manages persistent user preferences using localStorage
 * 
 * Usage:
 *   UserPrefs.get('equipos', 'view');         // Get preference
 *   UserPrefs.set('equipos', 'view', 'list'); // Set preference
 *   UserPrefs.getAll('equipos');              // Get all prefs for a module
 *   UserPrefs.clear('equipos');               // Clear all prefs for a module
 */

// Prevent re-declaration on Turbo navigation
window.UserPrefs = window.UserPrefs || (function () {
    const STORAGE_PREFIX = 'sgen_prefs_';

    /**
     * Get the storage key for a module
     * @param {string} module - Module name (e.g., 'equipos', 'empleados')
     * @returns {string} Full storage key
     */
    function getStorageKey(module) {
        return STORAGE_PREFIX + module;
    }

    /**
     * Get all preferences for a module
     * @param {string} module - Module name
     * @returns {object} Preferences object
     */
    function getAll(module) {
        try {
            const stored = localStorage.getItem(getStorageKey(module));
            return stored ? JSON.parse(stored) : {};
        } catch (e) {
            console.warn('UserPrefs: Error reading preferences', e);
            return {};
        }
    }

    /**
     * Get a specific preference
     * @param {string} module - Module name
     * @param {string} key - Preference key (e.g., 'view', 'filter')
     * @param {*} defaultValue - Default value if not found
     * @returns {*} Preference value or default
     */
    function get(module, key, defaultValue = null) {
        const prefs = getAll(module);
        return prefs.hasOwnProperty(key) ? prefs[key] : defaultValue;
    }

    /**
     * Set a specific preference
     * @param {string} module - Module name
     * @param {string} key - Preference key
     * @param {*} value - Value to store
     */
    function set(module, key, value) {
        try {
            const prefs = getAll(module);
            prefs[key] = value;
            localStorage.setItem(getStorageKey(module), JSON.stringify(prefs));
        } catch (e) {
            console.warn('UserPrefs: Error saving preferences', e);
        }
    }

    /**
     * Remove a specific preference
     * @param {string} module - Module name
     * @param {string} key - Preference key
     */
    function remove(module, key) {
        try {
            const prefs = getAll(module);
            delete prefs[key];
            localStorage.setItem(getStorageKey(module), JSON.stringify(prefs));
        } catch (e) {
            console.warn('UserPrefs: Error removing preference', e);
        }
    }

    /**
     * Clear all preferences for a module
     * @param {string} module - Module name
     */
    function clear(module) {
        try {
            localStorage.removeItem(getStorageKey(module));
        } catch (e) {
            console.warn('UserPrefs: Error clearing preferences', e);
        }
    }

    /**
     * Clear all SGEN preferences
     */
    function clearAll() {
        try {
            Object.keys(localStorage)
                .filter(key => key.startsWith(STORAGE_PREFIX))
                .forEach(key => localStorage.removeItem(key));
        } catch (e) {
            console.warn('UserPrefs: Error clearing all preferences', e);
        }
    }

    // Public API
    return {
        get,
        set,
        getAll,
        remove,
        clear,
        clearAll
    };
})();

// Make available globally (prevent re-declaration)
if (!window.UserPrefs) {
    window.UserPrefs = UserPrefs;
}
