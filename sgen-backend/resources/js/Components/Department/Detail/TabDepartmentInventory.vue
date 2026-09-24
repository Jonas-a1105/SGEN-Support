<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseDataTable, BaseCombobox, BaseSearchToolbar } from '@/Components/UI';
import type { DataTableColumn, ComboboxOption } from '@/Components/UI';
import { formatCurrency } from '@/Utils/formatters';
import type { DepartmentConsumable } from './types';

const props = defineProps<{
    consumibles: DepartmentConsumable[];
}>();

const consumableSearch = ref('');
const consumableCategory = ref('all');

const availableCategories = computed(() => {
    const cats = new Set<string>();
    props.consumibles.forEach((c) => {
        if (c.categoria) cats.add(c.categoria);
    });
    return Array.from(cats);
});

const categoryFilterOptions = computed<ComboboxOption[]>(() => [
    { value: 'all', label: 'Todas las Categorías' },
    ...availableCategories.value.map((cat) => ({ value: cat, label: cat })),
]);

const filteredConsumables = computed(() => {
    return props.consumibles.filter((item) => {
        const matchesCat = consumableCategory.value === 'all' || item.categoria === consumableCategory.value;
        const q = consumableSearch.value.trim().toLowerCase();
        const matchesQuery = !q || item.codigo.toLowerCase().includes(q) || item.nombre.toLowerCase().includes(q);
        return matchesCat && matchesQuery;
    });
});

const columns: DataTableColumn[] = [
    { key: 'codigo', label: 'CÓDIGO', width: '130px' },
    { key: 'nombre', label: 'INSUMO / ARTÍCULO' },
    { key: 'categoria', label: 'CATEGORÍA', width: '140px' },
    { key: 'stock', label: 'STOCK ASIGNADO', align: 'center', width: '140px' },
    { key: 'stock_minimo', label: 'STOCK MÍNIMO', align: 'center', width: '130px' },
    { key: 'estado', label: 'ESTADO', width: '130px' },
    { key: 'valor_total', label: 'VALOR TOTAL', align: 'right', width: '130px' },
    { key: 'actions', label: 'ACCIONES', align: 'right', width: '90px' },
];
</script>

<template>
    <div class="tab-panel-inventory">
        <BaseSearchToolbar
            v-model="consumableSearch"
            placeholder="Buscar insumos por código o nombre..."
        >
            <template #prepend>
                <div class="category-filter-select">
                    <BaseCombobox
                        v-model="consumableCategory"
                        :options="categoryFilterOptions"
                        :searchable="false"
                    />
                </div>
            </template>
        </BaseSearchToolbar>

        <BaseDataTable
            :columns="columns"
            :data="filteredConsumables"
            empty-title="No hay insumos asignados"
            empty-description="Este departamento no tiene consumibles o artículos de inventario registrados actualmente."
        >
            <template #cell-codigo="{ row }">
                <BaseBadge variant="code" size="md">{{ row.codigo }}</BaseBadge>
            </template>

            <template #cell-nombre="{ row }">
                <strong class="item-name">{{ row.nombre }}</strong>
            </template>

            <template #cell-categoria="{ row }">
                <span class="role-badge">{{ row.categoria }}</span>
            </template>

            <template #cell-stock="{ row }">
                <span class="stock-pill">{{ row.stock }} {{ row.unidad_medida }}</span>
            </template>

            <template #cell-stock_minimo="{ row }">
                <span class="text-muted-cell">{{ row.stock_minimo }} {{ row.unidad_medida }}</span>
            </template>

            <template #cell-estado="{ row }">
                <BaseBadge :variant="row.is_low_stock ? 'danger' : 'success'" size="sm" dot>
                    {{ row.is_low_stock ? 'Bajo Stock' : 'Disponible' }}
                </BaseBadge>
            </template>

            <template #cell-valor_total="{ row }">
                <span class="currency-text">{{ formatCurrency(row.valor_total) }}</span>
            </template>

            <template #cell-actions="{ row }">
                <Link :href="`/inventario/${row.id}`" class="action-btn-circle" title="Ver en Inventario">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </Link>
            </template>
        </BaseDataTable>
    </div>
</template>

<style scoped>
.tab-panel-inventory {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.category-filter-select {
    min-width: 200px;
}

.item-name {
    font-size: 13px;
    color: var(--text);
}

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border: var(--stroke-w) solid var(--stroke);
}

.stock-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(37, 99, 235, 0.08);
    color: var(--blue, #3b82f6);
    font-weight: 600;
    font-size: 12px;
}

.text-muted-cell {
    color: var(--text-muted);
    font-size: 12px;
}

.currency-text {
    font-family: monospace;
    font-weight: 600;
    color: var(--text);
}

.action-btn-circle {
    display: inline-grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.15s ease;
    box-shadow: none !important;
}

.action-btn-circle:hover {
    color: var(--blue, #3b82f6);
    border-color: var(--blue, #3b82f6);
}

.action-btn-circle svg {
    width: 14px;
    height: 14px;
}
</style>
