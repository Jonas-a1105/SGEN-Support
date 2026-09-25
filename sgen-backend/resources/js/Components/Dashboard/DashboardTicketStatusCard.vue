<script setup lang="ts">
import { ref, computed } from 'vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import type { StatusTicketItem } from '@/Types/DashboardMetrics';

const props = defineProps<{
    pending: StatusTicketItem[];
    inProcess: StatusTicketItem[];
}>();

const emit = defineEmits<{
    (e: 'view-all'): void;
    (e: 'open-ticket', id: number): void;
}>();

const currentTab = ref<'pending' | 'process'>('pending');

const activeList = computed(() => {
    return currentTab.value === 'pending' ? props.pending : props.inProcess;
});

const emptyMessage = computed(() => {
    return currentTab.value === 'pending'
        ? '¡No hay tickets pendientes!'
        : '¡No hay tickets en proceso!';
});
</script>

<template>
    <BaseCard class="tickets-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Estado de Tickets</h2>
            <span class="subtle">Flujo operativo diario</span>
        </div>

        <div class="tabbar">
            <button
                type="button"
                class="inner-tab"
                :class="{ active: currentTab === 'pending' }"
                @click="currentTab = 'pending'"
            >
                Pendientes
                <span class="count" :class="pending.length > 0 ? 'orange' : 'gray'">
                    {{ pending.length }}
                </span>
            </button>
            <button
                type="button"
                class="inner-tab"
                :class="{ active: currentTab === 'process' }"
                @click="currentTab = 'process'"
            >
                En proceso
                <span class="count" :class="inProcess.length > 0 ? 'orange' : 'gray'">
                    {{ inProcess.length }}
                </span>
            </button>
        </div>

        <div v-if="activeList.length === 0" class="empty-state">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24">
                    <path d="m5 12 4 4L19 6" />
                </svg>
            </div>
            <div class="empty-text">{{ emptyMessage }}</div>
        </div>

        <div v-else class="tickets-list">
            <div
                v-for="item in activeList"
                :key="item.id"
                class="ticket-row"
                @click="emit('open-ticket', item.id)"
            >
                <div class="ticket-info">
                    <span class="ticket-id">#{{ item.id }}</span>
                    <span class="ticket-name">{{ item.titulo }}</span>
                </div>
                <span class="ticket-priority" :class="item.prioridad">
                    {{ item.prioridad }}
                </span>
            </div>
        </div>

        <div class="panel-link-wrap">
            <button type="button" class="panel-link" @click="emit('view-all')">
                Ver todos los tickets
            </button>
        </div>
    </BaseCard>
</template>

<style scoped>
.tickets-card {
    display: flex;
    flex-direction: column;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 18px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.subtle {
    font-size: 13px;
    color: var(--text-muted);
}

.tabbar {
    display: flex;
    gap: 22px;
    padding: 0 18px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.inner-tab {
    position: relative;
    padding: 12px 0;
    font-size: 14px;
    color: var(--text-muted);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: color var(--transition-normal);
    display: inline-flex;
    align-items: center;
}

.inner-tab.active {
    color: var(--text);
    font-weight: 600;
}

.inner-tab.active::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: -2px;
    height: 2px;
    background: var(--orange);
    border-radius: 2px 2px 0 0;
}

.count {
    font-size: 11px;
    margin-left: 6px;
    display: inline-grid;
    place-items: center;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    padding: 0 5px;
}

.count.orange {
    background: var(--accent-soft);
    color: var(--orange);
}

.count.gray {
    background: var(--stroke-subtle);
    color: var(--text-muted);
}

.empty-state {
    margin: 16px 18px;
    height: 148px;
    border: var(--stroke-w) dashed var(--stroke);
    border-radius: var(--radius-lg);
    background: transparent;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--text-muted);
}

.empty-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgb(116, 179, 0);
    display: grid;
    place-items: center;
    color: #ffffff;
}

.empty-icon svg {
    width: 20px;
    height: 20px;
    stroke: #ffffff;
    fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.empty-text {
    font-size: 14px;
    color: var(--text-muted);
}

.tickets-list {
    padding: 8px 18px;
    min-height: 148px;
}

.ticket-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    cursor: pointer;
}

.ticket-row:last-child {
    border-bottom: none;
}

.ticket-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.ticket-id {
    font-size: 12px;
    color: var(--orange);
    font-weight: 700;
}

.ticket-name {
    font-size: 13px;
    color: var(--text);
}

.ticket-priority {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: var(--radius-pill);
    text-transform: capitalize;
}

.panel-link-wrap {
    padding: 13px 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.panel-link {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 13px;
    padding-bottom: 2px;
    border-bottom: 1.5px solid transparent;
    cursor: pointer;
    transition: color 0.22s ease, border-bottom-color 0.22s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.panel-link:hover {
    color: var(--orange);
    border-bottom-color: var(--orange);
}
</style>
