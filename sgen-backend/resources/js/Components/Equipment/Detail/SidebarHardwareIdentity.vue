<script setup lang="ts">
import { BaseCard } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    equipment: EquipmentDetail;
}>();
</script>

<template>
    <BaseCard title="Identificación del Activo" variant="glass" padding="md">
        <div class="spec-list-summary">
            <div class="summary-row">
                <span class="summary-label">Marca</span>
                <span class="summary-val">{{ equipment.brand || 'N/A' }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Modelo</span>
                <span class="summary-val">{{ equipment.model || 'N/A' }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Tipo</span>
                <span class="summary-val">{{ equipment.type }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Código Inventario</span>
                <span class="summary-val mono">{{ equipment.inventoryCode }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Serial</span>
                <span class="summary-val mono">{{ equipment.serialNumber || 'N/A' }}</span>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.spec-list-summary {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--space-2);
    border-bottom: 1px solid var(--stroke-subtle);
    font-size: 13px;
}

.summary-label {
    color: var(--text-dim);
    font-weight: 600;
}

.summary-val {
    color: var(--text);
    font-weight: 700;
}

.summary-val.mono {
    font-family: var(--font-mono);
}
</style>
