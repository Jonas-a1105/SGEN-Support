<script setup lang="ts">
import { computed } from 'vue';
import type { Product, StockLocation } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

const props = withDefaults(
    defineProps<{
        item: Product;
        locations: StockLocation[];
        search?: string;
    }>(),
    {
        search: '',
    }
);

const emit = defineEmits<{
    (e: 'update:search', val: string): void;
}>();

const filteredLocations = computed(() => {
    const term = (props.search || '').trim().toLowerCase();
    if (!term) return props.locations;
    return props.locations.filter((l) =>
        (l.departamento_nombre || l.nombre || '').toLowerCase().includes(term)
    );
});
</script>

<template>
    <BaseCard padding="lg">
        <div class="distribution-header-wrap">
            <div>
                <div class="panel-header-inline">
                    <span class="icon-location-glyph">📍</span>
                    <h3 class="panel-title">Stock por Ubicación</h3>
                </div>
                <span class="distribution-header-sub">
                    Vista global de existencias en todas las sedes
                </span>
            </div>
            <div class="distribution-search-wrap">
                <input
                    :value="search"
                    type="text"
                    class="form-input distribution-search-input"
                    id="searchDistributionInput"
                    placeholder="🔍 Filtrar sede..."
                    @input="emit('update:search', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <div class="table-responsive distribution-table-spacing">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>UBICACIÓN</th>
                        <th>TIPO</th>
                        <th>DISPONIBILIDAD</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody id="tbodyDistribution">
                    <tr>
                        <td><strong>🏢 Almacén Central (Principal)</strong></td>
                        <td><BaseBadge variant="code">Central</BaseBadge></td>
                        <td><strong class="distribution-bold-stock">{{ item.current_stock }} uds</strong></td>
                        <td><BaseBadge variant="success">Principal</BaseBadge></td>
                    </tr>
                    <tr v-for="loc in filteredLocations" :key="loc.id">
                        <td>{{ loc.departamento_nombre || loc.nombre || 'Departamento Asignado' }}</td>
                        <td><BaseBadge variant="code">{{ loc.tipo || 'Sede' }}</BaseBadge></td>
                        <td><strong>{{ loc.stock_actual ?? 0 }} uds</strong></td>
                        <td>
                            <BaseBadge :variant="(loc.stock_actual ?? 0) > 0 ? 'success' : 'danger'">
                                {{ (loc.stock_actual ?? 0) > 0 ? 'Activo' : 'Agotado' }}
                            </BaseBadge>
                        </td>
                    </tr>
                    <tr v-if="filteredLocations.length === 0 && search">
                        <td colspan="4" class="distribution-empty-cell">
                            No se encontraron sedes con ese nombre.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BaseCard>
</template>

<style scoped>
.distribution-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-4);
    flex-wrap: wrap;
    gap: var(--space-3);
}

.panel-header-inline {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.icon-location-glyph {
    font-size: 16px;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.distribution-header-sub {
    font-size: 12px;
    color: var(--text-muted);
    display: block;
    margin-top: 2px;
}

.distribution-search-input {
    height: 34px !important;
    padding-left: 10px !important;
    font-size: 12px !important;
    width: 200px;
}

.distribution-table-spacing {
    margin-top: var(--space-2);
}

.distribution-bold-stock {
    color: var(--text);
}

.distribution-empty-cell {
    text-align: center;
    color: var(--text-muted);
    padding: var(--space-5);
}
</style>
