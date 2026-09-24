<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseEmptyState, BaseSearchToolbar } from '@/Components/UI';
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

const filteredConsumables = computed(() => {
    return props.consumibles.filter((item) => {
        const matchesCat = consumableCategory.value === 'all' || item.categoria === consumableCategory.value;
        const q = consumableSearch.value.trim().toLowerCase();
        const matchesQuery = !q || item.codigo.toLowerCase().includes(q) || item.nombre.toLowerCase().includes(q);
        return matchesCat && matchesQuery;
    });
});

const formatCurrency = (val: number): string => {
    return `$${Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};
</script>

<template>
    <div class="tab-panel-inventory">
        <BaseSearchToolbar
            v-model="consumableSearch"
            placeholder="Buscar insumos por código o nombre..."
        >
            <template #prepend>
                <div class="category-filter-select">
                    <select v-model="consumableCategory" class="filter-native-select">
                        <option value="all">Todas las Categorías</option>
                        <option v-for="cat in availableCategories" :key="cat" :value="cat">
                            {{ cat }}
                        </option>
                    </select>
                </div>
            </template>
        </BaseSearchToolbar>

        <div v-if="filteredConsumables.length > 0" class="table-card">
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>INSUMO / ARTÍCULO</th>
                            <th>CATEGORÍA</th>
                            <th class="text-center">STOCK ASIGNADO</th>
                            <th class="text-center">STOCK MÍNIMO</th>
                            <th>ESTADO</th>
                            <th class="text-right">VALOR TOTAL</th>
                            <th class="text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredConsumables" :key="item.id">
                            <td>
                                <BaseBadge variant="code" size="md">{{ item.codigo }}</BaseBadge>
                            </td>
                            <td>
                                <strong class="item-name">{{ item.nombre }}</strong>
                            </td>
                            <td>
                                <span class="role-badge">{{ item.categoria }}</span>
                            </td>
                            <td class="text-center">
                                <span class="stock-pill">{{ item.stock }} {{ item.unidad_medida }}</span>
                            </td>
                            <td class="text-center text-muted-cell">
                                {{ item.stock_minimo }} {{ item.unidad_medida }}
                            </td>
                            <td>
                                <BaseBadge :variant="item.is_low_stock ? 'danger' : 'success'" size="sm" dot>
                                    {{ item.is_low_stock ? 'Bajo Stock' : 'Disponible' }}
                                </BaseBadge>
                            </td>
                            <td class="text-right">
                                <span class="currency-text">{{ formatCurrency(item.valor_total) }}</span>
                            </td>
                            <td class="text-right">
                                <Link :href="`/inventario/${item.id}`" class="action-btn-circle" title="Ver en Inventario">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <BaseEmptyState
            v-else
            title="No hay insumos asignados"
            description="Este departamento no tiene consumibles o artículos de inventario registrados actualmente."
        />
    </div>
</template>

<style scoped>
.tab-panel-inventory {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.category-filter-select {
    display: flex;
    align-items: center;
}

.filter-native-select {
    padding: 8px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}

.table-card {
    border-radius: 8px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.detail-table th {
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--stroke-subtle);
    border-bottom: var(--stroke-w) solid var(--stroke);
    letter-spacing: 0.05em;
}

.detail-table td {
    padding: 14px 16px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.detail-table tr:last-child td {
    border-bottom: none;
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

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
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
