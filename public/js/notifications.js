/**
 * Notification Center
 * Handles logic for the modern notification dropdown with Real-Time Polling.
 */

if (!window.NotificationCenter) {
    window.NotificationCenter = {
        isOpen: false,
        activeTab: 'all', // 'all', 'unread', 'alerts'
        notifications: [],
        lastReadIds: new Set(), // Track IDs we've seen to avoid re-alerting
        pollingInterval: null,
        audioCtx: null, // For generating sounds

        init() {
            this.fetchData();
            this.startPolling();
            this.attachGlobalListeners();
            this.initAudio();

            // Sync UI if we are on the config page
            if (document.getElementById('panel-notifications')) {
                NotificationConfig.loadToUI();
            }
        },

        initAudio() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) this.audioCtx = new AudioContext();
            } catch (e) {
                console.warn('AudioContext not supported');
            }
        },

        playSound(type = 'default') {
            if (!this.audioCtx) return;
            const prefs = NotificationConfig.load();
            if (prefs.sound === 'silent') return;

            // Simple "Ding" using Oscillator
            const oscillator = this.audioCtx.createOscillator();
            const gainNode = this.audioCtx.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioCtx.destination);

            if (type === 'important') {
                oscillator.type = 'square';
                oscillator.frequency.setValueAtTime(440, this.audioCtx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(880, this.audioCtx.currentTime + 0.1);
            } else {
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(800, this.audioCtx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(400, this.audioCtx.currentTime + 0.1);
            }

            // Sutil volume
            const vol = prefs.sound === 'sutil' ? 0.05 : 0.1;
            gainNode.gain.setValueAtTime(vol, this.audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioCtx.currentTime + 0.3);

            oscillator.start();
            oscillator.stop(this.audioCtx.currentTime + 0.3);
        },

        startPolling() {
            if (this.pollingInterval) clearInterval(this.pollingInterval);
            // Poll every 10 seconds for "Real-Time" feel
            this.pollingInterval = setInterval(() => {
                this.fetchData(true);
            }, 10000);
        },

        async fetchData(silent = false) {
            try {
                const baseUrl = window.BASE_URL || '/';
                const response = await fetch(`${baseUrl}api/notifications`);
                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                // Detection of NEW items for Alerts
                if (!silent && this.notifications.length > 0) {
                    this.checkForNewItems(data);
                }

                // Initialize ID tracking on first load
                if (this.notifications.length === 0 && data.length > 0) {
                    data.forEach(n => this.lastReadIds.add(n.id));
                }

                this.notifications = data;
                this.render();
            } catch (error) {
                console.warn('Error fetching notifications:', error);
                if (!silent) this.render();
            }
        },

        checkForNewItems(newData) {
            // Find new, unread items that we haven't seen before
            const newItems = newData.filter(n =>
                !n.read &&
                !this.lastReadIds.has(n.id) &&
                NotificationConfig.shouldShow(n) // Only alert if category is subscribed
            );

            if (newItems.length > 0) {
                newItems.forEach(n => this.lastReadIds.add(n.id));

                // Check if we should interrupt (DND)
                if (NotificationConfig.shouldInterrupt()) {
                    // Play Sound
                    this.playSound(newItems.some(n => n.type === 'danger') ? 'important' : 'default');

                    // Desktop Notification
                    const prefs = NotificationConfig.load();
                    if (prefs.channels.desktop) {
                        newItems.forEach(n => this.showDesktopNotification(n));
                    }
                }

                // Animate Bell
                const bell = document.querySelector('#notificationBtn i');
                if (bell) {
                    bell.classList.remove('animate-wiggle');
                    void bell.offsetWidth; // trigger reflow
                    bell.classList.add('animate-wiggle');
                }
            }
        },

        showDesktopNotification(n) {
            if (!("Notification" in window)) return;

            if (Notification.permission === "granted") {
                new Notification(n.title, { body: n.message, icon: 'public/img/icon.png' });
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(permission => {
                    if (permission === "granted") {
                        new Notification(n.title, { body: n.message, icon: 'public/img/icon.png' });
                    }
                });
            }
        },

        // Toggle Dropdown
        toggle() {
            this.isOpen = !this.isOpen;
            const dropdown = document.getElementById('notificationDropdown');
            const btn = document.getElementById('notificationBtn');

            if (this.isOpen) {
                dropdown.classList.add('show');
                btn.classList.add('active');
                // Resume audio context if suspended (browser autoplay policy)
                if (this.audioCtx && this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                this.fetchData(true);
            } else {
                dropdown.classList.remove('show');
                btn.classList.remove('active');
            }
        },

        // Tabs
        setTab(tab) {
            this.activeTab = tab;
            this.renderList();
            this.renderTabs();
        },

        // Actions
        async markAsRead(id) {
            this.notifications = this.notifications.map(n =>
                n.id === id ? { ...n, read: true } : n
            );
            this.render();

            try {
                const baseUrl = window.BASE_URL || '/';
                await fetch(`${baseUrl}api/notifications/mark-read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
            } catch (error) {
                console.error('Error marking as read:', error);
            }

            const notif = this.notifications.find(n => n.id === id);
            if (notif && notif.link && notif.link !== '#') {
                window.location.href = notif.link;
            }
        },

        async markAllRead() {
            this.notifications = this.notifications.map(n => ({ ...n, read: true }));
            this.render();
            // Implement API call if needed
        },

        delete(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
            this.render();
        },

        // Render Logic
        render() {
            this.renderBadge();
            this.renderList();
            this.renderTabs();
        },

        renderBadge() {
            // Only count unread visible by current filters
            const unreadCount = this.notifications.filter(n => !n.read && NotificationConfig.shouldShow(n)).length;
            const badgeContainer = document.getElementById('notificationBadgeContainer');

            if (badgeContainer) {
                if (unreadCount > 0) {
                    badgeContainer.innerHTML = `
                        <span class="notification-ping"></span>
                        <span class="notification-badge">${unreadCount}</span>
                    `;
                } else {
                    badgeContainer.innerHTML = '';
                }
            }
        },

        renderTabs() {
            const tabs = ['all', 'unread', 'alerts'];
            const labels = { all: 'Todas', unread: 'No leídas', alerts: 'Alertas' };

            const container = document.getElementById('notificationTabs');
            if (!container) return;

            container.innerHTML = tabs.map(tab => `
                <button 
                    onclick="event.stopPropagation(); NotificationCenter.setTab('${tab}')"
                    class="notification-tab ${this.activeTab === tab ? 'active' : ''}"
                >
                    ${labels[tab]}
                </button>
            `).join('');
        },

        renderList() {
            const listContainer = document.getElementById('notificationList');
            if (!listContainer) return;

            // Apply Filters (Category + Tab)
            let filtered = this.notifications.filter(n => NotificationConfig.shouldShow(n));

            if (this.activeTab === 'unread') filtered = filtered.filter(n => !n.read);
            if (this.activeTab === 'alerts') filtered = filtered.filter(n => n.type === 'warning' || n.type === 'danger');

            if (filtered.length === 0) {
                listContainer.innerHTML = `
                    <div class="notification-empty">
                        <div class="notification-empty-icon">
                            <i class="bi bi-bell" style="font-size: 1.5rem; color: #cbd5e1;"></i>
                        </div>
                        <p style="margin:0; font-weight:500; font-size:0.875rem;">Estás al día</p>
                        <p style="margin:0; font-size:0.75rem;">No hay notificaciones nuevas</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = filtered.map(n => this.buildItemHTML(n)).join('');
        },

        buildItemHTML(n) {
            let iconClass = 'bi-info-circle';
            let bgClass = '#e0e7ff';
            let textClass = '#6366f1';

            switch (n.type) {
                case 'warning':
                case 'danger':
                    iconClass = 'bi-exclamation-triangle';
                    bgClass = '#fef2f2';
                    textClass = '#ef4444';
                    break;
                case 'success':
                    iconClass = 'bi-check2-circle';
                    bgClass = '#ecfdf5';
                    textClass = '#10b981';
                    break;
                case 'info':
                default:
                    iconClass = 'bi-info-circle';
                    bgClass = '#eff6ff';
                    textClass = '#3b82f6';
                    break;
            }

            return `
                <li class="notification-item ${n.read ? 'read' : 'unread'}" onclick="event.stopPropagation(); NotificationCenter.markAsRead(${n.id})">
                    <div class="notification-avatar">
                        <div class="avatar-circle" style="background-color: ${bgClass}; color: ${textClass};">
                            <i class="bi ${iconClass}"></i>
                        </div>
                    </div>
                    <div class="notification-content">
                        <div class="notification-row-top">
                            <h4 class="notification-item-title">${n.title}</h4>
                            <span class="notification-time">${n.time}</span>
                        </div>
                        <p class="notification-message">${n.message}</p>
                    </div>
                    
                    ${!n.read ? '<span class="notification-unread-dot"></span>' : ''}
                </li>
            `;
        },

        attachGlobalListeners() {
            document.addEventListener('click', (e) => {
                const wrapper = document.querySelector('.notification-wrapper');
                if (this.isOpen && wrapper && !wrapper.contains(e.target)) {
                    this.toggle();
                }
            });
        }
    };
}

// Auto Init
window.NotificationCenter.init();

/**
 * Notification Preferences Logic
 */
const NotificationConfig = {
    defaults: {
        channels: {
            email: true,
            push: true,
            desktop: false
        },
        categories: {
            tickets: true,
            inventory: true,
            maintenance: true,
            system: true
        },
        dnd: {
            enabled: false,
            start: '22:00',
            end: '07:00'
        },
        sound: 'default'
    },

    load() {
        const stored = localStorage.getItem('sgen_notif_prefs');
        return stored ? { ...this.defaults, ...JSON.parse(stored) } : this.defaults;
    },

    save(prefs, silent = false) {
        localStorage.setItem('sgen_notif_prefs', JSON.stringify(prefs));
        if (window.NotificationCenter) {
            window.NotificationCenter.refreshPreferences();
        }

        // Request Desktop Permission if enabled
        if (prefs.channels.desktop && "Notification" in window && Notification.permission !== "granted") {
            Notification.requestPermission();
        }

        if (!silent) {
            alert('Preferencias guardadas correctamente.');
        }
    },

    // Populate the UI checkboes based on saved prefs
    loadToUI() {
        const prefs = this.load();
        const form = document.getElementById('panel-notifications');
        if (!form) return;

        // Set Checkboxes
        const setCheck = (selector, val) => {
            const el = form.querySelector(selector);
            if (el) el.checked = val;
        };

        setCheck('[data-pref="email_notifications"]', prefs.channels.email);
        setCheck('[data-pref="push_notifications"]', prefs.channels.push);
        setCheck('[data-pref="desktop_notifications"]', prefs.channels.desktop);

        setCheck('[data-pref="cat_tickets"]', prefs.categories.tickets);
        setCheck('[data-pref="cat_inventario"]', prefs.categories.inventory);
        setCheck('[data-pref="cat_mantenimientos"]', prefs.categories.maintenance);
        setCheck('[data-pref="cat_sistema"]', prefs.categories.system);

        setCheck('[data-pref="dnd_mode"]', prefs.dnd.enabled);

        const startInput = form.querySelector('[data-pref="dnd_start"]');
        const endInput = form.querySelector('[data-pref="dnd_end"]');
        if (startInput) startInput.value = prefs.dnd.start;
        if (endInput) endInput.value = prefs.dnd.end;
    },

    shouldShow(notif) {
        const prefs = this.load();
        let catKey = 'system';
        if (notif.category === 'Soporte') catKey = 'tickets';
        if (notif.category === 'Inventario') catKey = 'inventory';
        if (notif.category === 'Mantenimiento') catKey = 'maintenance';

        return !!prefs.categories[catKey];
    },

    shouldInterrupt() {
        const prefs = this.load();
        if (!prefs.dnd.enabled) return true;

        const now = new Date();
        const currentMins = now.getHours() * 60 + now.getMinutes();
        const [sh, sm] = prefs.dnd.start.split(':').map(Number);
        const [eh, em] = prefs.dnd.end.split(':').map(Number);
        const startMins = sh * 60 + sm;
        const endMins = eh * 60 + em;

        if (startMins > endMins) {
            return (currentMins >= startMins || currentMins <= endMins) ? false : true;
        } else {
            return (currentMins >= startMins && currentMins <= endMins) ? false : true;
        }
    }
};

window.saveNotificationPreferences = function (silent = false) {
    const form = document.getElementById('panel-notifications');
    if (!form) return;

    const prefs = NotificationConfig.load();

    // Channels
    prefs.channels.email = form.querySelector('[data-pref="email_notifications"]')?.checked ?? false;
    prefs.channels.push = form.querySelector('[data-pref="push_notifications"]')?.checked ?? false;
    prefs.channels.desktop = form.querySelector('[data-pref="desktop_notifications"]')?.checked ?? false;

    // Categories
    prefs.categories.tickets = form.querySelector('[data-pref="cat_tickets"]')?.checked ?? false;
    prefs.categories.inventory = form.querySelector('[data-pref="cat_inventario"]')?.checked ?? false;
    prefs.categories.maintenance = form.querySelector('[data-pref="cat_mantenimientos"]')?.checked ?? false;
    prefs.categories.system = form.querySelector('[data-pref="cat_sistema"]')?.checked ?? false;

    // DND
    prefs.dnd.enabled = form.querySelector('[data-pref="dnd_mode"]')?.checked ?? false;

    const startInput = form.querySelector('[data-pref="dnd_start"]');
    const endInput = form.querySelector('[data-pref="dnd_end"]');
    if (startInput) prefs.dnd.start = startInput.value;
    if (endInput) prefs.dnd.end = endInput.value;

    NotificationConfig.save(prefs, silent);
};

// Bind to main config form submit
document.addEventListener('DOMContentLoaded', () => {
    const configForm = document.getElementById('configForm');
    if (configForm) {
        configForm.addEventListener('submit', () => {
            if (window.saveNotificationPreferences) {
                window.saveNotificationPreferences(true);
            }
        });
    }

    // Also trigger load inputs on DOM ready
    if (document.getElementById('panel-notifications')) {
        NotificationConfig.loadToUI();
    }
});

// UI Helpers
window.switchNotifTab = function (tab) {
    const indInbox = document.getElementById('inbox-tab-indicator');
    const indSettings = document.getElementById('settings-tab-indicator');

    if (indInbox) indInbox.style.display = tab === 'inbox' ? 'block' : 'none';
    if (indSettings) indSettings.style.display = tab === 'settings' ? 'block' : 'none';

    const inboxTab = document.getElementById('notif-tab-inbox');
    const settingsTab = document.getElementById('notif-tab-settings');

    if (tab === 'inbox') {
        if (inboxTab) inboxTab.classList.add('active');
        if (settingsTab) settingsTab.classList.remove('active');
        const view = document.getElementById('notif-inbox-view');
        if (view) view.style.display = 'block';
        const settings = document.getElementById('notif-settings-view');
        if (settings) settings.style.display = 'none';
    } else {
        if (inboxTab) inboxTab.classList.remove('active');
        if (settingsTab) settingsTab.classList.add('active');
        const view = document.getElementById('notif-inbox-view');
        if (view) view.style.display = 'none';
        const settings = document.getElementById('notif-settings-view');
        if (settings) settings.style.display = 'block';
    }
};

window.NotificationCenter.fetchData = async function (silent = false) {
    // Override to ensure we use local filtering always
    try {
        const hasOriginal = window.NotificationCenter && window.NotificationCenter.notifications;
    } catch (e) { }
};

window.NotificationCenter.refreshPreferences = function () {
    this.fetchData(true);
};
