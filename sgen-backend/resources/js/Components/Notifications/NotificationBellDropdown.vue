<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseBadge } from '@/Components/UI';
import { useNotifications } from '@/Composables/useNotifications';
import NotificationBellButton from './NotificationBellButton.vue';
import NotificationItemRow from './NotificationItemRow.vue';
import type { NotificationItem } from '@/Types/notification';

/**
 * NotificationBellDropdown
 * Orquestador de presentación del menú desplegable de notificaciones.
 * Delega la lógica de negocio y red a useNotifications, el botón a
 * NotificationBellButton y las filas a NotificationItemRow.
 */
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const {
    unreadCount,
    notifications,
    isLoading,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
} = useNotifications();

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchNotifications();
    }
};

const handleItemClick = async (item: NotificationItem) => {
    await markAsRead(item);
    if (item.enlace) {
        isOpen.value = false;
        router.visit(item.enlace);
    }
};

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="notif-wrapper">
        <!-- Trigger Button -->
        <NotificationBellButton
            :unread-count="unreadCount"
            :is-open="isOpen"
            @click="toggleDropdown"
        />

        <!-- Dropdown Container -->
        <Transition name="fade-scale">
            <div v-if="isOpen" class="notif-dropdown">
                <!-- Header -->
                <header class="notif-dropdown-header">
                    <div class="header-title-box">
                        <span class="header-title">Notificaciones</span>
                        <BaseBadge v-if="unreadCount > 0" variant="neutral" size="sm">
                            {{ unreadCount }} no leídas
                        </BaseBadge>
                    </div>
                    <button
                        v-if="unreadCount > 0"
                        class="btn-mark-all"
                        type="button"
                        @click="markAllAsRead"
                    >
                        Marcar todas leídas
                    </button>
                </header>

                <!-- Scroll Area -->
                <div class="notif-scroll-area">
                    <!-- Loading state -->
                    <div v-if="isLoading" class="notif-loading">
                        <span class="spinner"></span>
                        <span>Cargando notificaciones...</span>
                    </div>

                    <!-- Empty state -->
                    <div v-else-if="notifications.length === 0" class="notif-empty">
                        <div class="empty-icon-circle">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h4 class="empty-title">Todo al día</h4>
                        <p class="empty-sub">No tienes notificaciones pendientes en tu bandeja.</p>
                    </div>

                    <!-- Notifications List -->
                    <ul v-else class="notif-list">
                        <NotificationItemRow
                            v-for="item in notifications"
                            :key="item.id"
                            :item="item"
                            @click="handleItemClick"
                        />
                    </ul>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.notif-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.notif-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 360px;
    max-width: calc(100vw - 32px);
    background: var(--bg-card, #17181a);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-lg);
    box-shadow: 0 16px 40px -4px rgba(0, 0, 0, 0.45), 0 6px 16px -2px rgba(0, 0, 0, 0.25);
    z-index: 1000;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.notif-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    background: var(--bg-card, #17181a);
}

.header-title-box {
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.btn-mark-all {
    background: none;
    border: none;
    font-size: 11px;
    font-weight: 600;
    color: var(--orange);
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
}

.btn-mark-all:hover {
    text-decoration: underline;
}

.notif-scroll-area {
    max-height: 400px;
    overflow-y: auto;
    background: var(--bg-card, #17181a);
}

.notif-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 32px 16px;
    color: var(--text-muted);
    font-size: 13px;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid var(--stroke);
    border-top-color: var(--orange);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.notif-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    text-align: center;
}

.empty-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--bg-sub);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--green);
    margin-bottom: 12px;
}

.empty-icon-circle svg {
    width: 24px;
    height: 24px;
}

.empty-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 4px;
}

.empty-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

.notif-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Transitions */
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(-4px);
}

@media (max-width: 480px) {
    .notif-dropdown {
        position: fixed;
        top: 64px;
        left: 12px;
        right: 12px;
        width: auto;
    }
}
</style>
