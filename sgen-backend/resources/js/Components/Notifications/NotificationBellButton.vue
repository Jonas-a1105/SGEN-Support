<script setup lang="ts">
defineProps<{
    unreadCount: number;
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'click'): void;
}>();
</script>

<template>
    <button
        class="notif-bell-btn"
        type="button"
        :class="{ active: isOpen }"
        :aria-expanded="isOpen"
        aria-label="Centro de Notificaciones"
        title="Centro de Notificaciones"
        @click.stop="emit('click')"
    >
        <svg
            viewBox="0 0 24 24"
            class="bell-svg"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>

        <span v-if="unreadCount > 0" class="notif-badge-pill">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
    </button>
</template>

<style scoped>
.notif-bell-btn {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-md);
    background: transparent;
    border: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text-muted);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.15s ease;
}

.notif-bell-btn:hover,
.notif-bell-btn.active {
    background: var(--bg-sub);
    color: var(--text);
    border-color: var(--stroke);
}

.bell-svg {
    width: 18px;
    height: 18px;
}

.notif-badge-pill {
    position: absolute;
    top: -4px;
    right: -4px;
    background: var(--red);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--bg);
}
</style>
