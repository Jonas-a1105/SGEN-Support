<script setup lang="ts">
import type { Product } from '@/Types/inventory';
import ItemGeneralInfoPane from './Detail/ItemGeneralInfoPane.vue';
import ItemPurchaseSpecsPane from './Detail/ItemPurchaseSpecsPane.vue';
import ItemStockStatusGauge from './Detail/ItemStockStatusGauge.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

defineProps<{
    item: Product;
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'view-distribution'): void;
    (e: 'view-history'): void;
    (e: 'open-adjust', item: Product): void;
    (e: 'edit', item: Product): void;
}>();
</script>

<template>
    <section class="inventory-wrapper detail-view-container" id="viewItemDetail">
        <div class="detail-top-nav">
            <div class="detail-nav-left">
                <button class="back-link-btn" id="btnBackFromItemDetail" type="button" @click="emit('back')">
                    <span>←</span>
                    <span>Volver al inventario</span>
                </button>
                <h1 class="item-hero-title" id="detailItemHeroName">{{ item.name }}</h1>
                <BaseBadge variant="code" id="detailItemHeroSku">ID: {{ item.sku }}</BaseBadge>
            </div>
            <div class="header-actions-row">
                <BaseButton
                    variant="outline"
                    id="btnEditItemDetail"
                    type="button"
                    @click="emit('edit', item)"
                >
                    <svg viewBox="0 0 24 24" class="svg-icon-standard">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    <span>Editar</span>
                </BaseButton>
                <BaseButton
                    variant="outline"
                    id="btnHistoryItemDetail"
                    type="button"
                    @click="emit('view-history')"
                >
                    <svg viewBox="0 0 24 24" class="svg-icon-standard">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 14 14" />
                    </svg>
                    <span>Ver Historial</span>
                </BaseButton>
                <BaseButton
                    variant="primary"
                    id="btnAdjustStockModalTrigger"
                    type="button"
                    @click="emit('open-adjust', item)"
                >
                    <svg viewBox="0 0 24 24" class="svg-icon-standard">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                        <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                    </svg>
                    <span>Ajustar Stock</span>
                </BaseButton>
            </div>
        </div>

        <div class="detail-layout-grid detail-grid-item">
            <div class="detail-column-stack">
                <ItemGeneralInfoPane :product="item" />
                <ItemPurchaseSpecsPane :product="item" />
            </div>

            <ItemStockStatusGauge
                :product="item"
                @request-replenishment="emit('open-adjust', item)"
                @view-distribution="emit('view-distribution')"
            />
        </div>
    </section>
</template>

<style scoped>
.svg-icon-standard {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.detail-grid-item {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: var(--space-5);
    align-items: start;
}

.detail-column-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

@media (max-width: 1080px) {
    .detail-grid-item {
        grid-template-columns: 1fr;
    }
}
</style>
