<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import CardProductKardex from '@/Components/Inventory/Detail/CardProductKardex.vue';
import type { Product, InventoryMovement } from '@/Types/inventory';

interface Props {
    product: Product;
    movements?: InventoryMovement[];
}

withDefaults(defineProps<Props>(), {
    movements: () => [],
});
</script>

<template>
    <AppLayout :title="`Ficha: ${product.name}`">
        <div class="detail-container">
            <Link href="/inventario" class="back-link">
                <span>←</span> Volver al catálogo de inventario
            </Link>

            <!-- Product Header & Key Info Card -->
            <BaseCard padding="lg">
                <header class="detail-header">
                    <div>
                        <BaseBadge variant="code">{{ product.sku }}</BaseBadge>
                        <h2 class="product-title">{{ product.name }}</h2>
                        <BaseBadge variant="info">{{ product.category }}</BaseBadge>
                    </div>
                    <BaseBadge :variant="product.is_low_stock ? 'danger' : 'success'" size="md">
                        {{ product.current_stock }} {{ product.unit_of_measure || 'uds' }} en almacén
                    </BaseBadge>
                </header>

                <dl class="meta-grid">
                    <div class="meta-item"><dt>Precio Compra</dt><dd>${{ Number(product.purchase_price).toFixed(2) }}</dd></div>
                    <div class="meta-item"><dt>Stock Mínimo</dt><dd>{{ product.minimum_stock }}</dd></div>
                    <div class="meta-item"><dt>Marca / Modelo</dt><dd>{{ product.brand || '-' }} / {{ product.model || '-' }}</dd></div>
                    <div class="meta-item"><dt>Ubicación</dt><dd>{{ product.location || 'Almacén Central' }}</dd></div>
                </dl>

                <div v-if="product.description" class="desc-box">
                    <h4>Descripción</h4>
                    <p>{{ product.description }}</p>
                </div>
            </BaseCard>

            <!-- Kardex Movements Section -->
            <CardProductKardex :movements="movements" />
        </div>
    </AppLayout>
</template>

<style scoped>
.detail-container {
    max-width: 1220px;
    margin: 0 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 13px;
    font-weight: 600;
    color: var(--orange);
    text-decoration: none;
    transition: opacity 0.15s ease;
}

.back-link:hover {
    opacity: 0.8;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: var(--space-4);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.product-title {
    margin: var(--space-2) 0;
    font-size: 18px !important;
    font-weight: 700 !important;
    color: var(--text);
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: var(--space-4);
    margin: var(--space-5) 0;
}

.meta-item dt {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-muted);
}

.meta-item dd {
    font-size: 14px;
    font-weight: 700;
    margin: var(--space-1) 0 0 0;
    color: var(--text);
}

.desc-box {
    background: var(--bg-sub);
    padding: var(--space-4);
    border-radius: var(--radius-md);
    border: var(--stroke-w) solid var(--stroke-subtle);
}

.desc-box h4 {
    margin: 0 0 var(--space-1) 0;
    font-size: 13px;
    color: var(--text);
}

.desc-box p {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
}
</style>
