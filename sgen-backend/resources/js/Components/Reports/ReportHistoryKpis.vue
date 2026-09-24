<script setup lang="ts">
import { BaseKpiCard } from '@/Components/UI';

defineProps<{
    totalCount: number;
    pdfCount: number;
    excelCount: number;
    todayCount: number;
}>();
</script>

<template>
    <div class="kpi-row-4cols" aria-label="Indicadores de reportes">
        <!-- TOTAL -->
        <BaseKpiCard
            label="Total reportes"
            :value="totalCount"
            color="blue"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
            </template>
        </BaseKpiCard>

        <!-- PDFS -->
        <BaseKpiCard
            label="PDFs"
            :value="pdfCount"
            color="red"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
            </template>
        </BaseKpiCard>

        <!-- EXCEL -->
        <BaseKpiCard
            label="Excel/CSV"
            :value="excelCount"
            color="green"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                    <line x1="3" y1="9" x2="21" y2="9" />
                    <line x1="3" y1="15" x2="21" y2="15" />
                    <line x1="9" y1="3" x2="9" y2="21" />
                    <line x1="15" y1="3" x2="15" y2="21" />
                </svg>
            </template>
        </BaseKpiCard>

        <!-- HOY -->
        <BaseKpiCard
            label="Hoy"
            :value="todayCount"
            color="yellow"
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
    </div>
</template>

<style scoped>
.kpi-row-4cols {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 980px) {
    .kpi-row-4cols {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
}
</style>
