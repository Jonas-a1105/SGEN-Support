<script setup lang="ts">
import { computed } from 'vue';
import type { Product, InventoryMovement } from '@/Types/inventory';
import HistoryMovementsTable from './History/HistoryMovementsTable.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

const props = defineProps<{
    item: Product;
    movimientos: InventoryMovement[];
}>();

const emit = defineEmits<{ (e: 'back'): void }>();

const itemMovements = computed(() => {
    return props.movimientos.filter((m) => m.producto_id === props.item.id || !m.producto_id);
});
</script>

<template>
    <section class="inventory-wrapper detail-view-container" id="viewItemHistory">
        <div class="detail-top-nav">
            <div>
                <button
                    class="back-link-btn history-back-btn"
                    id="btnBackFromHistory"
                    type="button"
                    @click="emit('back')"
                >
                    <span>←</span>
                    <span>Volver al Artículo</span>
                </button>
                <div class="history-hero-header">
                    <div class="history-hero-icon-circle">
                        <svg viewBox="0 0 24 24" class="svg-icon-standard">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 14 14" />
                        </svg>
                    </div>
                    <h1 class="item-hero-title">Historial: <span id="histItemName">{{ item.name }}</span></h1>
                    <BaseBadge variant="code" id="histItemCode">ID: {{ item.sku }}</BaseBadge>
                </div>
            </div>
        </div>

        <BaseCard padding="lg">
            <div class="history-panel-header">
                <div>
                    <h3 class="panel-title">Movimientos Registrados</h3>
                    <span class="history-header-sub">Listado completo de entradas, salidas y ajustes de stock.</span>
                </div>
            </div>

            <HistoryMovementsTable
                :movements="itemMovements"
                paginate
                :per-page="5"
            />
        </BaseCard>
    </section>
</template>

<style scoped>
.history-back-btn {
    margin-bottom: var(--space-2) !important;
}

.history-hero-header {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-4);
}

.history-hero-icon-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    font-size: 16px;
}

.item-hero-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.history-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: var(--space-3);
}

.panel-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.history-header-sub {
    font-size: 12px;
    color: var(--text-dim);
}
</style>
