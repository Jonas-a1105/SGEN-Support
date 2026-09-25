<script setup lang="ts">
import { formatDateTime } from '@/Utils/formatters';
import type { NotificationItem } from '@/Types/notification';

const props = defineProps<{
    item: NotificationItem;
}>();

const emit = defineEmits<{
    (e: 'click', item: NotificationItem): void;
}>();

const getIconClass = (tipo?: string): string => {
    const t = (tipo || '').toLowerCase();
    if (t.includes('ticket') || t.includes('soporte')) return 'icon-blue';
    if (t.includes('inventario') || t.includes('stock')) return 'icon-orange';
    if (t.includes('mantenimiento') || t.includes('cmms')) return 'icon-purple';
    if (t.includes('danger') || t.includes('error')) return 'icon-red';
    return 'icon-brand';
};
</script>

<template>
    <li
        class="notif-item-row"
        :class="{ unread: !item.leido, clickable: !!item.enlace }"
        @click="emit('click', item)"
    >
        <div class="notif-icon-circle" :class="getIconClass(item.tipo)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path v-if="item.tipo?.includes('ticket')" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <path v-else-if="item.tipo?.includes('inventario')" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <circle v-else cx="12" cy="12" r="10"></circle>
                <line v-if="!item.tipo?.includes('inventario')" x1="12" y1="8" x2="12" y2="12"></line>
                <line v-if="!item.tipo?.includes('inventario')" x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>

        <div class="notif-body">
            <div class="notif-title-row">
                <span class="notif-title">{{ item.titulo || 'Notificación del Sistema' }}</span>
                <span v-if="!item.leido" class="unread-dot" title="No leído"></span>
            </div>
            <p class="notif-message">{{ item.mensaje }}</p>
            <span class="notif-date">{{ formatDateTime(item.created_at) }}</span>
        </div>
    </li>
</template>

<style scoped>
.notif-item-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--stroke-subtle);
    transition: background 0.15s ease;
    cursor: default;
}

.notif-item-row.clickable {
    cursor: pointer;
}

.notif-item-row:hover {
    background: var(--bg-sub);
}

.notif-item-row.unread {
    background: rgba(37, 99, 235, 0.04);
}

.notif-item-row.unread:hover {
    background: rgba(37, 99, 235, 0.08);
}

.notif-icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.notif-icon-circle svg {
    width: 16px;
    height: 16px;
}

.icon-blue {
    background: rgba(37, 99, 235, 0.12);
    color: #2563eb;
}

.icon-orange {
    background: rgba(249, 115, 22, 0.12);
    color: #ea580c;
}

.icon-purple {
    background: rgba(147, 51, 234, 0.12);
    color: #9333ea;
}

.icon-red {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
}

.icon-brand {
    background: rgba(100, 116, 139, 0.12);
    color: var(--text-muted);
}

.notif-body {
    flex: 1;
    min-width: 0;
}

.notif-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 3px;
}

.notif-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #2563eb;
    flex-shrink: 0;
}

.notif-message {
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.4;
    margin: 0 0 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notif-date {
    font-size: 10px;
    color: var(--text-dim);
    font-weight: 500;
}
</style>
