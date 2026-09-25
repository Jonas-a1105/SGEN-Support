<script setup lang="ts">
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import type { RecentActivityItem } from '@/Types/DashboardMetrics';

defineProps<{
    activities: RecentActivityItem[];
}>();

const emit = defineEmits<{
    (e: 'view-logs'): void;
    (e: 'create-ticket'): void;
}>();
</script>

<template>
    <BaseCard class="overview-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Actividad Reciente</h2>
            <BaseButton variant="outline" size="sm" @click="emit('view-logs')">
                Ver registros
            </BaseButton>
        </div>
        <div class="activity-list">
            <div v-for="item in activities" :key="item.id" class="activity-row">
                <div class="activity-info">
                    <div class="activity-name">{{ item.title }}</div>
                    <div class="subtle">{{ item.time_ago }}</div>
                </div>
                <div class="activity-badge" :class="item.type">
                    {{ item.badge }}
                </div>
            </div>
        </div>
        <div class="action-footer">
            <BaseButton
                variant="primary"
                size="md"
                class="btn-full"
                @click="emit('create-ticket')"
            >
                Registrar nuevo ticket
            </BaseButton>
        </div>
    </BaseCard>
</template>

<style scoped>
.overview-card {
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

.activity-list {
    padding: 8px 18px 16px;
    flex: 1;
}

.activity-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.activity-row:last-child {
    border-bottom: none;
}

.activity-info {
    min-width: 0;
}

.activity-name {
    font-size: 13px;
    color: var(--text);
    font-weight: 500;
}

.subtle {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}

.activity-badge {
    font-size: 12px;
    font-weight: 700;
    color: var(--orange);
    padding: 3px 8px;
    border-radius: var(--radius-sm);
    background: var(--accent-soft);
}

.activity-badge.info {
    color: var(--blue);
    background: rgba(37, 99, 235, 0.1);
}

.activity-badge.neutral {
    color: var(--text-muted);
    background: var(--stroke-subtle);
}

.action-footer {
    padding: 0 18px 18px;
}

.btn-full {
    width: 100%;
}
</style>
