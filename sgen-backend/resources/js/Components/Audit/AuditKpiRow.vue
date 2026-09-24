<script setup lang="ts">
import { BaseKpiCard } from '@/Components/UI';

export interface AuditKpis {
    active_sessions: number;
    avg_duration: string;
    total_logs: number;
}

defineProps<{
    kpis: AuditKpis;
    statusFilter: string;
}>();

const emit = defineEmits<{
    (e: 'filter-status', status: 'all' | 'active' | 'ended'): void;
}>();
</script>

<template>
    <section class="kpi-row" aria-label="Indicadores de auditoría">
        <BaseKpiCard
            label="Sesiones activas"
            :value="`${kpis.active_sessions} Usuario${kpis.active_sessions === 1 ? '' : 's'}`"
            color="green"
            :clickable="true"
            :active="statusFilter === 'active'"
            @click="emit('filter-status', statusFilter === 'active' ? 'all' : 'active')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <line x1="9" y1="20" x2="15" y2="20" />
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Promedio duración"
            :value="kpis.avg_duration"
            color="blue"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Total registros"
            :value="`${kpis.total_logs} Sesiones`"
            color="purple"
            :clickable="true"
            :active="statusFilter === 'all'"
            @click="emit('filter-status', 'all')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <line x1="3" y1="6" x2="3.01" y2="6" />
                    <line x1="3" y1="12" x2="3.01" y2="12" />
                    <line x1="3" y1="18" x2="3.01" y2="18" />
                </svg>
            </template>
        </BaseKpiCard>
    </section>
</template>

<style scoped>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
</style>
