<script setup lang="ts">
import BaseCard from '@/Components/UI/BaseCard.vue';

defineProps<{
    currentStock: number;
    sku: string;
}>();
</script>

<template>
    <BaseCard padding="sm" rounded="card" class="stock-kpi-card">
        <div class="stock-kpi-icon-square">
            <svg viewBox="0 0 24 24">
                <polygon points="12 2 2 7 12 12 22 7 12 2" />
                <polyline points="2 17 12 22 22 17" />
                <polyline points="2 12 12 17 22 12" />
            </svg>
        </div>
        <div>
            <span class="stock-kpi-micro-label">TOTAL GLOBAL</span>
            <strong class="stock-kpi-total-val" id="distribGlobalTotal">{{ currentStock }}</strong>
        </div>
        <div class="stock-kpi-ref-box">
            <span class="stock-kpi-micro-label">ID Ref</span>
            <strong class="stock-kpi-ref-code" id="distribRefCode">{{ sku }}</strong>
        </div>
    </BaseCard>
</template>

<style scoped>
.stock-kpi-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.stock-kpi-icon-square {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-md);
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.stock-kpi-icon-square svg {
    width: 22px;
    height: 22px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.stock-kpi-micro-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    line-height: 1;
    margin-bottom: 2px;
}

.stock-kpi-total-val {
    font-size: 18px;
    color: var(--text);
    font-weight: 700;
}

.stock-kpi-ref-box {
    margin-left: auto;
    text-align: right;
}

.stock-kpi-ref-code {
    font-size: 13px;
    font-family: var(--font-mono);
    color: var(--text-muted);
}
</style>
