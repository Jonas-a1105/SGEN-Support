<script setup lang="ts">
import type { Product } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import { IconPackage } from '@tabler/icons-vue';

defineProps<{
    product: Product;
}>();
</script>

<template>
    <BaseCard padding="lg">
        <div class="panel-header-inline">
            <IconPackage :size="18" stroke-width="1.8" class="icon-specs-glyph" />
            <h3 class="panel-title">Información General</h3>
        </div>

        <div class="info-grid-2">
            <div>
                <span class="form-label">Categoría</span>
                <div class="info-field-val" id="detailItemCategory">{{ product.category }}</div>
            </div>
            <div>
                <span class="form-label">Ubicación Actual</span>
                <div class="info-field-val" id="detailItemLocation">{{ product.location || 'Almacén Central' }}</div>
            </div>
            <div>
                <span class="form-label">Marca</span>
                <div class="info-field-val" id="detailItemBrand">{{ product.brand || 'GENERICO' }}</div>
            </div>
            <div>
                <span class="form-label">Modelo</span>
                <div class="info-field-val" id="detailItemModel">{{ product.model || 'GENERICO' }}</div>
            </div>
        </div>

        <div class="info-footer-divider">
            <span class="form-label">Unidad de Medida</span>
            <div class="info-field-val">{{ product.unit_of_measure || 'Unidad' }}</div>
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

.info-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 2px;
}

.info-field-val {
    font-size: 14px;
    color: var(--text);
    font-weight: 600;
}

.info-footer-divider {
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    margin-top: var(--space-4);
    padding-top: var(--space-3);
}

@media (max-width: 768px) {
    .info-grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>
