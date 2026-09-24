<script setup lang="ts">
import BaseKpiCard from '@/Components/UI/BaseKpiCard.vue';

export interface CategoryKpis {
    total_activas: number;
    hardware_stat: string;
    red_stat: string;
}

defineProps<{
    kpis: CategoryKpis;
}>();
</script>

<template>
    <section class="kpi-row" aria-label="Indicadores de categorías">
        <BaseKpiCard
            label="Total categorías activas"
            :value="kpis.total_activas"
            color="brand"
            icon="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"
        />

        <BaseKpiCard
            label="Incidencias de hardware"
            :value="kpis.hardware_stat"
            color="orange"
            icon="M4 4h16v16H4z M9 9h6v6H9z"
        />

        <BaseKpiCard
            label="Conectividad & redes"
            :value="kpis.red_stat"
            color="blue"
            icon="M5 12.55a11 11 0 0 1 14.08 0 M1.42 9a16 16 0 0 1 21.16 0 M8.53 16.11a6 6 0 0 1 6.95 0"
        />
    </section>
</template>

<style scoped>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}

@media (max-width: 900px) {
    .kpi-row {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }

    .kpi-row > :last-child:nth-child(odd) {
        grid-column: span 2;
    }
}
</style>
