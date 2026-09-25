<script setup lang="ts">
import { computed } from 'vue';
import type { Product, StockLocation } from '@/Types/inventory';
import { BaseBadge, BaseCard, BaseDataTable, type DataTableColumn } from '@/Components/UI';
import { IconMapPin } from '@tabler/icons-vue';

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

const columns: DataTableColumn[] = [
    { key: 'ubicacion', label: 'UBICACIÓN', sortable: true },
    { key: 'tipo', label: 'TIPO', width: '130px' },
    { key: 'stock', label: 'DISPONIBILIDAD', width: '150px' },
    { key: 'estado', label: 'ESTADO', width: '120px' },
];

const tableItems = computed(() => {
    const list: Array<{
        id: number | string;
        ubicacion: string;
        tipo: string;
        stock: number;
        isPrimary?: boolean;
    }> = [];

    const term = (props.search || '').trim().toLowerCase();

    if (!term || 'almacén central (principal)'.includes(term)) {
        list.push({
            id: 'central',
            ubicacion: 'Almacén Central (Principal)',
            tipo: 'Central',
            stock: props.item.current_stock,
            isPrimary: true,
        });
    }

    props.locations.forEach((loc) => {
        const name = loc.departamento_nombre || loc.nombre || 'Departamento Asignado';
        if (!term || name.toLowerCase().includes(term)) {
            list.push({
                id: loc.id,
                ubicacion: name,
                tipo: loc.tipo || 'Sede',
                stock: loc.stock_actual ?? 0,
                isPrimary: false,
            });
        }
    });

    return list;
});
</script>

<template>
    <BaseCard padding="lg">
        <div class="distribution-header-wrap">
            <div>
                <div class="panel-header-inline">
                    <IconMapPin :size="18" stroke-width="1.8" class="icon-location-glyph" />
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
                    placeholder="Filtrar sede..."
                    @input="emit('update:search', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <BaseDataTable
            :columns="columns"
            :items="tableItems"
            empty-title="No se encontraron sedes"
            empty-subtitle="No hay ubicaciones que coincidan con la búsqueda."
        >
            <template #cell-ubicacion="{ item: row }">
                <strong v-if="row.isPrimary">{{ row.ubicacion }}</strong>
                <span v-else>{{ row.ubicacion }}</span>
            </template>

            <template #cell-tipo="{ item: row }">
                <BaseBadge variant="code">{{ row.tipo }}</BaseBadge>
            </template>

            <template #cell-stock="{ item: row }">
                <strong>{{ row.stock }} uds</strong>
            </template>

            <template #cell-estado="{ item: row }">
                <BaseBadge v-if="row.isPrimary" variant="success">Principal</BaseBadge>
                <BaseBadge v-else :variant="row.stock > 0 ? 'success' : 'danger'">
                    {{ row.stock > 0 ? 'Activo' : 'Agotado' }}
                </BaseBadge>
            </template>
        </BaseDataTable>
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
</style>
