<script setup lang="ts">
import { BaseKpiCard } from '@/Components/UI';
import type { DashboardKpis } from '@/types/DashboardMetrics';

defineProps<{
    kpis: DashboardKpis;
}>();

const emit = defineEmits<{
    (e: 'select-kpi', type: string): void;
}>();
</script>

<template>
    <div class="kpi-grid" aria-label="Indicadores generales del panel">
        <BaseKpiCard
            label="Total equipos"
            :value="kpis.total_equipos"
            :subtext="kpis.cambio_equipos"
            trend="up"
            color="blue"
            :clickable="true"
            @click="emit('select-kpi', 'equipos')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="10" height="14" rx="1.5" />
                    <rect x="15" y="4" width="6" height="16" rx="1.5" />
                    <path d="M6 9h4M6 12h4M17 8h2M17 11h2" />
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Pendientes"
            :value="kpis.tickets_pendientes"
            :subtext="kpis.cambio_pendientes"
            trend="down"
            color="yellow"
            :clickable="true"
            @click="emit('select-kpi', 'pendientes')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24">
                    <path d="M7 4h10M7 20h10M8 4v4c0 1.5 1.8 2.8 4 4 2.2-1.2 4-2.5 4-4V4M8 20v-4c0-1.5 1.8-2.8 4-4 2.2 1.2 4 2.5 4 4v4" />
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="En proceso"
            :value="kpis.tickets_en_proceso"
            :subtext="kpis.cambio_proceso"
            trend="up"
            color="purple"
            :clickable="true"
            @click="emit('select-kpi', 'proceso')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19 12a7 7 0 0 0-.12-1.3l2-1.55-2-3.46-2.25.9A7.1 7.1 0 0 0 14.4 5L14 2h-4l-.4 3a7.1 7.1 0 0 0-2.23 1.6l-2.25-.9-2 3.46 2 1.55A7 7 0 0 0 5 12c0 .44.04.87.12 1.3l-2 1.55 2 3.46 2.25-.9A7.1 7.1 0 0 0 9.6 19l.4 3h4l.4-3a7.1 7.1 0 0 0 2.23-1.6l2.25.9 2-3.46-2-1.55c.08-.42.12-.86.12-1.3z" />
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Resueltos"
            :value="kpis.tickets_resueltos"
            :subtext="kpis.cambio_resueltos"
            trend="up"
            color="green"
            :clickable="true"
            @click="emit('select-kpi', 'resueltos')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path d="m8 12 2.6 2.6L16.5 9" />
                </svg>
            </template>
        </BaseKpiCard>
    </div>
</template>

<style scoped>
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 900px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
}
</style>
