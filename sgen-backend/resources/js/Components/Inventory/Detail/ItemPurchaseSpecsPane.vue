<script setup lang="ts">
import type { Product } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import {
    IconReceipt,
    IconCalendar,
    IconBuildingStore,
    IconCurrencyDollar,
    IconShieldCheck,
} from '@tabler/icons-vue';

defineProps<{
    product: Product;
}>();
</script>

<template>
    <BaseCard padding="lg">
        <div class="panel-header-inline">
            <IconReceipt :size="18" stroke-width="1.8" class="icon-specs-glyph" />
            <h3 class="panel-title">Detalles de Compra y Garantía</h3>
        </div>

        <div class="purchase-grid-cards">
            <div class="spec-block">
                <div class="spec-icon-box">
                    <IconCalendar :size="16" stroke-width="2" />
                </div>
                <div>
                    <span class="acq-field-label">Fecha Compra</span>
                    <strong class="spec-field-val">N/A</strong>
                </div>
            </div>
            <div class="spec-block">
                <div class="spec-icon-box">
                    <IconBuildingStore :size="16" stroke-width="2" />
                </div>
                <div>
                    <span class="acq-field-label">Proveedor</span>
                    <strong class="spec-field-val">--</strong>
                </div>
            </div>
            <div class="spec-block">
                <div class="spec-icon-box">
                    <IconCurrencyDollar :size="16" stroke-width="2" />
                </div>
                <div>
                    <span class="acq-field-label">Costo</span>
                    <strong class="spec-field-val">
                        ${{ Number(product.purchase_price || 0).toFixed(2) }}
                    </strong>
                </div>
            </div>
            <div class="spec-block">
                <div class="spec-icon-box">
                    <IconShieldCheck :size="16" stroke-width="2" />
                </div>
                <div>
                    <span class="acq-field-label">Garantía</span>
                    <strong class="spec-field-val">No aplica</strong>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.panel-header-inline {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}

.icon-specs-glyph {
    font-size: 16px;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.purchase-grid-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-3);
}

.spec-block {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    border-radius: var(--card-radius);
    padding: var(--space-3) var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.spec-icon-box {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--bg-card);
    display: grid;
    place-items: center;
    font-size: 16px;
    flex-shrink: 0;
}

.acq-field-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 2px;
}

.spec-field-val {
    font-size: 14px;
    color: var(--text);
    font-weight: 600;
}

@media (max-width: 768px) {
    .purchase-grid-cards {
        grid-template-columns: 1fr;
    }
}
</style>
