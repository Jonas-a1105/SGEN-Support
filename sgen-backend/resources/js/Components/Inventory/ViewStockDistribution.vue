<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Product, Department, StockLocation, TransferStockPayload } from '@/Types/inventory';
import DistributionKpiCard from './Distribution/DistributionKpiCard.vue';
import DistributionLocationsTable from './Distribution/DistributionLocationsTable.vue';
import DistributionTransferForm from './Distribution/DistributionTransferForm.vue';

const props = defineProps<{
    item: Product;
    departments: Department[];
    locations: StockLocation[];
}>();

const emit = defineEmits<{ (e: 'back'): void }>();

const search = ref('');
const isSubmitting = ref(false);

const handleTransferSubmit = (payload: TransferStockPayload) => {
    isSubmitting.value = true;
    router.post('/inventario/transferir', payload, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <section class="inventory-wrapper detail-view-container" id="viewStockDistribution">
        <div class="detail-top-nav">
            <div>
                <button
                    class="back-link-btn distribution-back-btn"
                    id="btnBackFromDistribution"
                    type="button"
                    @click="emit('back')"
                >
                    <span>←</span>
                    <span>Volver al detalle</span>
                </button>
                <h1 class="item-hero-title">
                    Distribución de Stock &gt; <span id="distribItemName" class="brand-accent-text">{{ item.name }}</span>
                </h1>
            </div>

            <DistributionKpiCard
                :current-stock="item.current_stock"
                :sku="item.sku"
            />
        </div>

        <div class="detail-layout-grid detail-grid-distribution">
            <DistributionLocationsTable
                :item="item"
                :locations="locations"
                :search="search"
                @update:search="search = $event"
            />

            <DistributionTransferForm
                :item="item"
                :departments="departments"
                :is-submitting="isSubmitting"
                @submit="handleTransferSubmit"
            />
        </div>
    </section>
</template>

<style scoped>
.distribution-back-btn {
    margin-bottom: var(--space-2) !important;
}

.brand-accent-text {
    color: var(--blue);
}

.detail-grid-distribution {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: var(--space-5);
    align-items: start;
}

@media (max-width: 1080px) {
    .detail-grid-distribution {
        grid-template-columns: 1fr;
    }
}
</style>
