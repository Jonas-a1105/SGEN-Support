<script setup lang="ts">
import { computed } from 'vue';
import type { Product } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import { IconTrendingUp } from '@tabler/icons-vue';

const props = defineProps<{
    product: Product;
}>();

const emit = defineEmits<{
    (e: 'request-replenishment'): void;
    (e: 'view-distribution'): void;
}>();

const isAvailable = computed(() => (props.product.current_stock || 0) > 0);
const valuation = computed(() => {
    const stock = Number(props.product.current_stock) || 0;
    const price = Number(props.product.purchase_price) || 0;
    return (stock * price).toFixed(2);
});
</script>

<template>
    <BaseCard padding="lg" class="gauge-card-container">
        <!-- Header con Título y Estado Centralizado -->
        <div class="gauge-header-row">
            <h3 class="gauge-title">Estado del Stock</h3>
            <BaseBadge
                :variant="isAvailable ? 'success' : 'danger'"
                id="detailItemStockBadge"
            >
                {{ isAvailable ? 'Disponible' : 'Agotado' }}
            </BaseBadge>
        </div>

        <!-- Display Numérico Central Exclusivo de 48px -->
        <div class="gauge-number-box">
            <div class="gauge-big-num" id="detailItemStockBigNum">
                {{ product.current_stock }}
            </div>
            <span class="gauge-sublabel">UNIDADES DISPONIBLES</span>
        </div>

        <!-- Desglose de Metadatos de Stock -->
        <div class="gauge-meta-list">
            <div class="gauge-meta-row">
                <span class="meta-label">Mínimo Requerido</span>
                <strong id="detailItemStockMin">{{ product.minimum_stock }} u.</strong>
            </div>
            <div class="gauge-meta-row">
                <span class="meta-label">Valor de Inventario</span>
                <strong id="detailItemValuation">${{ valuation }}</strong>
            </div>
        </div>

        <!-- Acciones Base UI -->
        <div class="gauge-actions-stack">
            <BaseButton
                variant="primary"
                id="btnRequestReplenishment"
                class="w-full"
                @click="emit('request-replenishment')"
            >
                <IconTrendingUp :size="16" stroke-width="2" />
                <span>Solicitar Reposición</span>
            </BaseButton>
            <BaseButton
                variant="secondary"
                id="btnViewDistribution"
                class="w-full"
                @click="emit('view-distribution')"
            >
                Ver Distribución
            </BaseButton>
        </div>
    </BaseCard>
</template>

<style scoped>
.gauge-card-container {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.gauge-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.gauge-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.gauge-number-box {
    text-align: center;
    padding: var(--space-4) 0;
}

.gauge-big-num {
    font-size: 48px;
    font-weight: 700 !important;
    line-height: 1;
    color: var(--text);
    letter-spacing: -0.02em;
}

.gauge-sublabel {
    display: block;
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    margin-top: var(--space-1);
}

.gauge-meta-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    padding-top: var(--space-3);
}

.gauge-meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: var(--text);
}

.meta-label {
    color: var(--text-muted);
}

.gauge-actions-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    margin-top: auto;
}

.w-full {
    width: 100%;
}
</style>
