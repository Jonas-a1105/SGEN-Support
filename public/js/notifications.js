/**
 * Notification Center
 * Handles logic for the modern notification dropdown.
 */

const NotificationCenter = {
    isOpen: false,
    activeTab: 'all', // 'all', 'unread', 'alerts'
    notifications: [],

    // Mock Data Initialization
    initData() {
        this.notifications = [
            {
                id: 1,
                type: 'alert',
                title: 'SLA en Riesgo',
                message: 'El ticket #402 (Mousepack) está a 30 min de vencer.',
                time: 'Hace 5 min',
                read: false,
                priority: 'high'
            },
            {
                id: 2,
                type: 'mention',
                title: 'Carlos te mencionó',
                message: '@Admin revisar el stock de la sucursal norte antes de cerrar.',
                time: 'Hace 42 min',
                read: false,
                userAvatar: 'CJ'
            },
            {
                id: 3,
                type: 'system',
                title: 'Reporte Mensual Listo',
                message: 'El análisis de inventario de Agosto está disponible para descarga.',
                time: 'Hace 2 horas',
                read: true,
                fileType: 'PDF'
            },
            {
                id: 4,
                type: 'update',
                title: 'Stock Actualizado',
                message: 'Recepción de mercadería completada en Almacén Central.',
                time: 'Ayer',
                read: true
            }
        ];
    },

    init() {
        this.initData();
        this.render();
        this.attachGlobalListeners();
    },

    // Toggle Dropdown
    toggle() {
        this.isOpen = !this.isOpen;
        const dropdown = document.getElementById('notificationDropdown');
        const btn = document.getElementById('notificationBtn');

        if (this.isOpen) {
            dropdown.classList.add('show');
            btn.classList.add('active');
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
    markAsRead(id) {
        this.notifications = this.notifications.map(n =>
            n.id === id ? { ...n, read: true } : n
        );
        this.render(); // Re-render to update badges/styles
    },

    markAllRead() {
        this.notifications = this.notifications.map(n => ({ ...n, read: true }));
        this.render();
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
        const unreadCount = this.notifications.filter(n => !n.read).length;
        const badgeContainer = document.getElementById('notificationBadgeContainer');
        const bellIcon = document.querySelector('#notificationBtn i');

        if (unreadCount > 0) {
            badgeContainer.innerHTML = `
                <span class="notification-ping"></span>
                <span class="notification-badge">${unreadCount}</span>
            `;
            if (bellIcon) bellIcon.classList.add('animate-wiggle');
        } else {
            badgeContainer.innerHTML = '';
            if (bellIcon) bellIcon.classList.remove('animate-wiggle');
        }
    },

    renderTabs() {
        const tabs = ['all', 'unread', 'alerts'];
        const labels = { all: 'Todas', unread: 'No leídas', alerts: 'Alertas' };

        const container = document.getElementById('notificationTabs');
        if (!container) return;

        container.innerHTML = tabs.map(tab => `
            <button 
                onclick="NotificationCenter.setTab('${tab}')"
                class="notification-tab ${this.activeTab === tab ? 'active' : ''}"
            >
                ${labels[tab]}
            </button>
        `).join('');
    },

    renderList() {
        const listContainer = document.getElementById('notificationList');
        if (!listContainer) return;

        let filtered = this.notifications;
        if (this.activeTab === 'unread') filtered = filtered.filter(n => !n.read);
        if (this.activeTab === 'alerts') filtered = filtered.filter(n => n.type === 'alert');

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
        // Icon Config
        let iconClass = 'bi-file-text';
        let bgClass = '#e0e7ff'; // indigo-50
        let textClass = '#6366f1'; // indigo-500

        switch (n.type) {
            case 'alert':
                iconClass = 'bi-exclamation-triangle';
                bgClass = '#fef2f2'; // red-50
                textClass = '#ef4444'; // red-500
                break;
            case 'mention':
                iconClass = 'bi-chat-square-text';
                bgClass = '#eff6ff'; // blue-50
                textClass = '#3b82f6'; // blue-500
                break;
            case 'update':
                iconClass = 'bi-check2-circle';
                bgClass = '#ecfdf5'; // emerald-50
                textClass = '#10b981'; // emerald-500
                break;
        }

        // Avatar HTML
        let avatarHTML = '';
        if (n.userAvatar) {
            avatarHTML = `
                <div class="avatar-circle" style="background-color: #e2e8f0; color: #475569;">
                    ${n.userAvatar}
                </div>
            `;
        } else {
            avatarHTML = `
                <div class="avatar-circle" style="background-color: ${bgClass}; color: ${textClass};">
                    <i class="bi ${iconClass}"></i>
                </div>
            `;
        }

        return `
            <li class="notification-item ${n.read ? 'read' : 'unread'}" onclick="NotificationCenter.markAsRead(${n.id})">
                <div class="notification-avatar">
                    ${avatarHTML}
                </div>
                <div class="notification-content">
                    <div class="notification-row-top">
                        <h4 class="notification-item-title">${n.title}</h4>
                        <span class="notification-time">${n.time}</span>
                    </div>
                    <p class="notification-message">${n.message}</p>
                    ${(n.priority === 'high' && !n.read) ? `
                        <div class="priority-badge">
                            <i class="bi bi-clock"></i> Acción Requerida
                        </div>
                    ` : ''}
                </div>
                
                <button 
                    class="notification-delete-btn" 
                    title="Eliminar"
                    onclick="event.stopPropagation(); NotificationCenter.delete(${n.id})"
                >
                    <i class="bi bi-trash"></i>
                </button>
                
                ${!n.read ? '<span class="notification-unread-dot"></span>' : ''}
            </li>
        `;
    },

    attachGlobalListeners() {
        // Close on click outside
        document.addEventListener('click', (e) => {
            const wrapper = document.querySelector('.notification-wrapper');
            if (this.isOpen && wrapper && !wrapper.contains(e.target)) {
                this.toggle();
            }
        });
    }
};

// Auto Init
document.addEventListener('DOMContentLoaded', () => {
    NotificationCenter.init();
});
