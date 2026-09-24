<script setup lang="ts">
import { BaseBadge, BaseDataTable, type DataTableColumn } from '@/Components/UI';
import { formatDateTime } from '@/Utils/formatters';
import type { InventoryMovement } from '@/Types/inventory';

withDefaults(
    defineProps<{
        movements: InventoryMovement[];
        paginate?: boolean;
        perPage?: number;
    }>(),
    {
        paginate: false,
        perPage: 10,
    }
);

const columns: DataTableColumn[] = [
    { key: 'fecha', label: 'FECHA', sortable: true },
    { key: 'tipo', label: 'TIPO', width: '130px' },
    { key: 'cantidad', label: 'CANTIDAD', width: '130px' },
    { key: 'motivo', label: 'MOTIVO' },
    { key: 'usuario', label: 'USUARIO', width: '140px' },
];

const getBadgeVariant = (type: string): 'success' | 'danger' | 'info' | 'neutral' => {
    switch (type.toUpperCase()) {
        case 'ENTRADA': return 'success';
        case 'SALIDA': return 'danger';
        case 'TRANSFERENCIA': return 'info';
        default: return 'neutral';
    }
};

const getQtyClass = (type: string) => {
    switch (type.toUpperCase()) {
        case 'ENTRADA': return 'qty-text-green';
        case 'SALIDA': return 'qty-text-red';
        default: return 'qty-text-blue';
    }
};
</script>

<template>
    <div class="history-movements-table-wrap">
        <BaseDataTable
            :columns="columns"
            :items="movements"
            :paginate="paginate"
            :per-page="perPage"
            item-label="movimientos"
            empty-title="No hay movimientos registrados"
            empty-subtitle="No se encontraron registros de entradas o salidas para este artículo."
        >
            <template #cell-fecha="{ item }">
                <span>{{ item.created_at ? formatDateTime(item.created_at) : 'Reciente' }}</span>
            </template>

            <template #cell-tipo="{ item }">
                <BaseBadge :variant="getBadgeVariant(item.tipo)">
                    {{ item.tipo }}
                </BaseBadge>
            </template>

            <template #cell-cantidad="{ item }">
                <strong :class="getQtyClass(item.tipo)">
                    {{ item.tipo.toUpperCase() === 'ENTRADA' ? '+' : (item.tipo.toUpperCase() === 'SALIDA' ? '-' : '') }}{{ item.cantidad }} uds
                </strong>
            </template>

            <template #cell-motivo="{ item }">
                <span>{{ item.motivo || 'Sin motivo especificado' }}</span>
            </template>

            <template #cell-usuario="{ item }">
                <BaseBadge variant="code">{{ item.usuario_nombre || 'Sistema' }}</BaseBadge>
            </template>
        </BaseDataTable>
    </div>
</template>

<style scoped>
.qty-text-green { color: var(--green); }
.qty-text-red { color: var(--red); }
.qty-text-blue { color: var(--blue); }
</style>
