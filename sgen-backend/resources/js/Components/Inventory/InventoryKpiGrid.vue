<script setup lang="ts">
import { computed } from 'vue';
import { BaseKpiCard } from '@/Components/UI';

const props = defineProps<{
    kpis?: Record<string, unknown>;
}>();

const emit = defineEmits<{ (e: 'filter-low-stock'): void }>();

const totalItems = computed<number>(() => {
    const k = props.kpis;
    if (!k) return 0;
    return Number(k.total_items ?? k.totalItems ?? k.total_products ?? 0);
});

const totalUnits = computed<number>(() => {
    const k = props.kpis;
    if (!k) return 0;
    return Number(k.total_units ?? k.totalUnits ?? k.total_stock ?? 0);
});

const lowStockCount = computed<number>(() => {
    const k = props.kpis;
    if (!k) return 0;
    return Number(k.low_stock_count ?? k.lowStockCount ?? 0);
});
</script>

<template>
    <div class="kpi-row">
        <BaseKpiCard
            label="Total artículos"
            :value="totalItems"
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
            label="Stock total"
            :value="`${totalUnits} unidades`"
            color="green"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
            </template>
        </BaseKpiCard>

        <BaseKpiCard
            label="Stock bajo"
            :value="`${lowStockCount} artículos`"
            color="yellow"
            :clickable="true"
            @click="emit('filter-low-stock')"
        >
            <template #icon>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m10.29 3.86-8.6 14.86A2 2 0 0 0 3.42 22h17.16a2 2 0 0 0 1.73-3.28l-8.6-14.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </template>
        </BaseKpiCard>
    </div>
</template>

<style scoped>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-4, 14px);
    margin-bottom: var(--space-4, 16px);
}

@media (max-width: 768px) {
    .kpi-row {
        grid-template-columns: 1fr;
    }
}
</style>
