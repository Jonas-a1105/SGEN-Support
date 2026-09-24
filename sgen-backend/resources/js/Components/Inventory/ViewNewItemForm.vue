<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import NewItemIdentification from './Forms/NewItemIdentification.vue';
import NewItemTechSpecs from './Forms/NewItemTechSpecs.vue';
import NewItemStockControl from './Forms/NewItemStockControl.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const emit = defineEmits<{ (e: 'back'): void; (e: 'saved'): void }>();

const form = useForm({
    sku: '',
    name: '',
    category: 'Consumibles',
    unit_of_measure: 'Unidad',
    brand: '',
    model: '',
    initial_stock: 0,
    minimum_stock: 5,
    purchase_price: 0.0,
    vendor: '',
    purchase_date: '',
    warranty_expiration: '',
    location: 'Almacén Central',
    description: '',
});

const submit = () => {
    form.post('/inventario', {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('back');
        },
    });
};
</script>

<template>
    <section class="inventory-wrapper detail-view-container" id="viewNewItemForm">
        <BaseCard padding="lg">
            <div class="module-header form-header-bordered">
                <div class="module-title-wrap">
                    <div class="module-icon-box new-item-icon-box">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="module-title">Registrar Nuevo Artículo</h1>
                        <p class="module-subtitle">Agrega un nuevo producto al catálogo global.</p>
                    </div>
                </div>
                <div class="header-actions-row">
                    <BaseButton
                        variant="secondary"
                        id="btnCancelNewItem"
                        type="button"
                        @click="emit('back')"
                    >
                        Cancelar
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        id="btnSaveNewItem"
                        type="button"
                        :disabled="form.processing"
                        :loading="form.processing"
                        @click="submit"
                    >
                        <span>✓</span>
                        <span>Guardar Producto</span>
                    </BaseButton>
                </div>
            </div>

            <form id="formCreateItem" class="form-grid-layout" @submit.prevent="submit">
                <NewItemIdentification
                    :sku="form.sku"
                    :description="form.description"
                    @update:sku="form.sku = $event"
                    @update:description="form.description = $event"
                />

                <div class="form-column-stack-spacious">
                    <NewItemTechSpecs
                        :name="form.name"
                        :category="form.category"
                        :unit-of-measure="form.unit_of_measure"
                        :brand="form.brand"
                        :model="form.model"
                        @update:name="form.name = $event"
                        @update:category="form.category = $event"
                        @update:unit-of-measure="form.unit_of_measure = $event"
                        @update:brand="form.brand = $event"
                        @update:model="form.model = $event"
                    />

                    <NewItemStockControl
                        :initial-stock="form.initial_stock"
                        :minimum-stock="form.minimum_stock"
                        :purchase-price="form.purchase_price"
                        :vendor="form.vendor"
                        :purchase-date="form.purchase_date"
                        :warranty-expiration="form.warranty_expiration"
                        :location="form.location"
                        @update:initial-stock="form.initial_stock = $event"
                        @update:minimum-stock="form.minimum_stock = $event"
                        @update:purchase-price="form.purchase_price = $event"
                        @update:vendor="form.vendor = $event"
                        @update:purchase-date="form.purchase_date = $event"
                        @update:warranty-expiration="form.warranty_expiration = $event"
                    />
                </div>
            </form>
        </BaseCard>
    </section>
</template>

<style scoped>
.form-header-bordered {
    margin-bottom: var(--space-5) !important;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle) !important;
    padding-bottom: var(--space-4) !important;
}

.new-item-icon-box {
    background: var(--blue) !important;
    color: #ffffff !important;
}

.form-grid-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: var(--space-6);
    align-items: start;
}

.form-column-stack-spacious {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

@media (max-width: 1080px) {
    .form-grid-layout {
        grid-template-columns: 1fr;
    }
}
</style>
