<script setup lang="ts">
import type { Equipment } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import { IconFileDescription } from '@tabler/icons-vue';

defineProps<{
    equipment: Equipment;
}>();
</script>

<template>
    <BaseCard padding="lg" id="subpaneAcquisition">
        <div class="panel-header-inline">
            <IconFileDescription :size="18" stroke-width="1.8" class="icon-acq-glyph" />
            <h3 class="panel-title">Adquisición y Garantía</h3>
        </div>

        <BaseCard variant="subtle" padding="md" class="acquisition-box">
            <div class="acquisition-header-row">
                <div>
                    <span class="acq-field-label">Proveedor</span>
                    <strong class="acq-field-val">{{ equipment.proveedor || '--' }}</strong>
                </div>
                <div>
                    <span class="acq-field-label">Valor de Compra</span>
                    <strong class="acq-cost-val">
                        {{ equipment.precio_compra ? ('$' + Number(equipment.precio_compra).toFixed(2)) : 'N/A' }}
                    </strong>
                </div>
            </div>

            <div class="warranty-status-box">
                {{ equipment.garantia_expira ? ('Garantía vence: ' + equipment.garantia_expira) : 'Sin información de garantía' }}
            </div>
        </BaseCard>
    </BaseCard>
</template>

<style scoped>
.panel-header-inline {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}

.icon-acq-glyph {
    font-size: 16px;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.acquisition-box {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.acquisition-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.acq-field-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 2px;
}

.acq-field-val {
    font-size: 14px;
    color: var(--text);
    font-weight: 600;
}

.acq-cost-val {
    font-size: 16px;
    color: var(--orange);
    font-weight: 700;
}

.warranty-status-box {
    padding: var(--space-2) var(--space-3);
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: var(--radius-sm);
    font-size: 12px;
    color: var(--text-muted);
}
</style>
