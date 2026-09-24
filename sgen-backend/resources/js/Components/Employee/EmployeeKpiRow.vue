<script setup lang="ts">
import { BaseKpiCard } from '@/Components/UI';

export interface EmployeeKpis {
    totalEmp: number;
    activeUsers: number;
    noUsers: number;
}

defineProps<{
    kpis: EmployeeKpis;
}>();
</script>

<template>
    <section class="kpi-row" aria-label="Indicadores de personal">
        <BaseKpiCard
            label="Total empleados"
            :value="kpis.totalEmp"
            color="blue"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Usuarios activos"
            :value="kpis.activeUsers"
            color="green"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Sin usuario"
            :value="kpis.noUsers"
            color="yellow"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </template>
        </BaseKpiCard>
    </section>
</template>

<style scoped>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

@media (max-width: 768px) {
    .kpi-row {
        grid-template-columns: 1fr;
    }
}
</style>
