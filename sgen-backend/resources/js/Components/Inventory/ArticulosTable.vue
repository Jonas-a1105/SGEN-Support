<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Product, InventoryPagination } from '@/Types/inventory';
import { useClientPagination } from '@/Composables/useClientPagination';
import TablePaginationFooter from './Common/TablePaginationFooter.vue';
import InventoryControlsBar from './Common/InventoryControlsBar.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = withDefaults(
    defineProps<{
        products: Product[];
        pagination: InventoryPagination;
        search?: string;
    }>(),
    {
        search: '',
    }
);

const emit = defineEmits<{
    (e: 'update:search', val: string): void;
    (e: 'open-adjust', item: Product): void;
    (e: 'view-item', item: Product): void;
}>();

const viewMode = ref<'table' | 'cards'>('table');
const isDense = ref(false);

const filteredProducts = computed(() => {
    const term = (props.search || '').trim().toLowerCase();
    if (!term) return props.products;
    return props.products.filter((p) =>
        (p.name || '').toLowerCase().includes(term) ||
        (p.sku || '').toLowerCase().includes(term) ||
        (p.category || '').toLowerCase().includes(term) ||
        (p.brand || '').toLowerCase().includes(term) ||
        (p.model || '').toLowerCase().includes(term)
    );
});

const {
    perPage,
    currentPage,
    totalPages,
    paginatedItems: paginatedProducts,
    setPage,
    setPerPage,
} = useClientPagination(filteredProducts, 10);
</script>

<template>
    <div>
        <!-- BUSCADOR GLOBAL -->
        <div class="global-search-card search-card-spaced">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
            <input
                :value="search"
                type="text"
                class="global-search-input"
                id="inventorySearchInput"
                placeholder="Buscar por código, nombre, serial, marca, modelo..."
                autocomplete="off"
                @input="emit('update:search', ($event.target as HTMLInputElement).value)"
            />
        </div>

        <!-- BARRA DE VISTA: SWITCH Y TABLA/CARDS -->
        <InventoryControlsBar
            v-model:is-dense="isDense"
            v-model:view-mode="viewMode"
            :total-count="products.length"
            total-label="Total Artículos"
        />

        <!-- TABLA DE ARTÍCULOS DE INVENTARIO -->
        <div v-if="viewMode === 'table'" class="data-container-card" id="tableWrapArticulos">
            <div class="table-responsive">
                <table class="custom-table" :class="{ dense: isDense }">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>ARTÍCULO</th>
                            <th>CATEGORÍA</th>
                            <th>UBICACIÓN</th>
                            <th>STOCK</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyArticulos">
                        <tr v-for="item in paginatedProducts" :key="item.id">
                            <td><BaseBadge variant="code">{{ item.sku }}</BaseBadge></td>
                            <td><strong class="table-item-title">{{ item.name }}</strong></td>
                            <td><BaseBadge variant="info">{{ item.category }}</BaseBadge></td>
                            <td><span class="table-item-subtitle">{{ item.location || 'Almacén Central' }}</span></td>
                            <td>
                                <div class="stock-pill" :class="item.current_stock <= item.minimum_stock ? 'stock-danger' : 'stock-green'">
                                    <span>{{ item.current_stock }}</span>
                                    <span class="stock-min-label">/ min {{ item.minimum_stock }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="table-actions-row">
                                    <button class="tbl-btn" type="button" title="Ajustar stock" @click="emit('open-adjust', item)">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                                    </button>
                                    <button class="tbl-btn" type="button" title="Ver ficha detallada" @click="emit('view-item', item)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedProducts.length === 0">
                            <td colspan="6" class="table-empty-cell">
                                No se encontraron artículos que coincidan con la búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <TablePaginationFooter
                :showing-count="paginatedProducts.length"
                :total-count="filteredProducts.length"
                item-label="items"
                :current-page="currentPage"
                :total-pages="totalPages"
                :per-page="perPage"
                @change-page="setPage"
                @change-per-page="setPerPage"
            />
        </div>

        <!-- CARDS VIEW DE ARTÍCULOS -->
        <div v-else class="cards-grid">
            <BaseCard
                v-for="item in paginatedProducts"
                :key="item.id"
                padding="md"
                class="card-item-box"
            >
                <div class="card-item-header">
                    <BaseBadge variant="code">{{ item.sku }}</BaseBadge>
                    <BaseBadge variant="info">{{ item.category }}</BaseBadge>
                </div>
                <h4 class="card-item-title">{{ item.name }}</h4>
                <div class="card-item-meta">
                    <span>{{ item.location || 'Almacén Central' }}</span>
                    <span class="stock-pill" :class="item.current_stock <= item.minimum_stock ? 'stock-danger' : 'stock-green'">
                        Stock: {{ item.current_stock }}
                    </span>
                </div>
                <div class="card-item-footer">
                    <BaseButton variant="secondary" size="sm" class="flex-1" @click="emit('open-adjust', item)">
                        Ajustar
                    </BaseButton>
                    <BaseButton variant="primary" size="sm" class="flex-1" @click="emit('view-item', item)">
                        Ver Ficha
                    </BaseButton>
                </div>
            </BaseCard>
        </div>
    </div>
</template>

<style scoped>
.search-card-spaced {
    margin-bottom: var(--space-4);
}

.table-item-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}

.table-item-subtitle {
    font-size: 12px;
    color: var(--text-muted);
}

.stock-min-label {
    font-size: 11px;
    opacity: 0.75;
    margin-left: 2px;
}

.table-actions-row {
    display: flex;
    gap: 6px;
}

.table-empty-cell {
    text-align: center;
    color: var(--text-muted);
    padding: var(--space-6);
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-4);
}

.card-item-box {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.card-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-item-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
}

.card-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-muted);
}

.card-item-footer {
    display: flex;
    gap: var(--space-2);
    margin-top: auto;
    padding-top: var(--space-2);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.flex-1 {
    flex: 1;
}
</style>
