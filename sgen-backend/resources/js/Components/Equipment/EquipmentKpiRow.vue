<script setup lang="ts">
import { BaseKpiCard } from '@/Components/UI';

export interface EquipmentKpis {
    totalActivos: number;
    operativos: number;
    enReparacion: number;
    fueraServicio: number;
}

defineProps<{
    kpis: EquipmentKpis;
}>();
</script>

<template>
    <section class="kpi-row" aria-label="Indicadores de activos">
        <BaseKpiCard
            label="Total activos"
            :value="kpis.totalActivos"
            color="blue"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Operativos"
            :value="kpis.operativos"
            color="green"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m5 12 4 4L19 6"></path>
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="En reparación"
            :value="kpis.enReparacion"
            color="yellow"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Fuera de servicio"
            :value="kpis.fueraServicio"
            color="red"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </template>
        </BaseKpiCard>
    </section>
</template>

<style scoped>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

@media (max-width: 900px) {
    .kpi-row {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
}
</style>
