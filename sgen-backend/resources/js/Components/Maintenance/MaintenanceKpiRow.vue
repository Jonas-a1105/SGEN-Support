<script setup lang="ts">
import { computed } from 'vue';
import { BaseKpiCard } from '@/Components/UI';
import type { MaintenanceKpis } from './types';

const props = defineProps<{
    kpis: MaintenanceKpis;
}>();

const healthPercent = computed(() => {
    if (!props.kpis.total || props.kpis.total === 0) return '100%';
    const pct = ((props.kpis.total - props.kpis.pending) / props.kpis.total) * 100;
    return `${Math.max(0, Math.min(100, Math.round(pct)))}%`;
});
</script>

<template>
    <section class="kpi-row" aria-label="Indicadores de mantenimiento">
        <!-- KPI 1: Salud de la flota -->
        <BaseKpiCard
            label="Salud de la flota"
            :value="healthPercent"
            subtext="Operativa"
            color="green"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </template>
        </BaseKpiCard>

        <!-- KPI 2: Mantenimientos al día -->
        <BaseKpiCard
            label="Mantenimientos al día"
            :value="kpis.completed"
            :subtext="`${kpis.pending} pendientes`"
            color="green"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </template>
        </BaseKpiCard>

        <!-- KPI 3: Próximos 7 días -->
        <BaseKpiCard
            label="Próximos 7 días"
            :value="kpis.upcoming"
            subtext="Agendados"
            color="blue"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
            </template>
        </BaseKpiCard>
    </section>
</template>

<style scoped>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

@media (max-width: 900px) {
    .kpi-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .kpi-row {
        grid-template-columns: 1fr;
    }
}
</style>
