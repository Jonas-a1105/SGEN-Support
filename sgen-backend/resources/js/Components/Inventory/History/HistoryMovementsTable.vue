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
    { key: 'fecha', label: 'FECHA / HORA', sortable: true, width: '170px' },
    { key: 'tipo', label: 'TIPO', width: '130px' },
    { key: 'cantidad', label: 'CANTIDAD', width: '120px', align: 'right' },
    { key: 'motivo', label: 'MOTIVO / DETALLE' },
    { key: 'referencia', label: 'REFERENCIA', width: '130px' },
    { key: 'usuario', label: 'RESPONSABLE', width: '140px' },
];

const getBadgeVariant = (type?: string): 'success' | 'danger' | 'warning' | 'info' | 'code' | 'neutral' => {
    switch ((type || '').toUpperCase()) {
        case 'ENTRADA':
        case 'COMPRA':
            return 'success';
        case 'SALIDA':
        case 'BAJA':
            return 'danger';
        case 'CONSUMO':
            return 'warning';
        case 'TRANSFERENCIA':
        case 'TRASLADO':
            return 'code';
        case 'AJUSTE':
            return 'info';
        default:
            return 'neutral';
    }
};

const getQtyClass = (type?: string) => {
    switch ((type || '').toUpperCase()) {
        case 'ENTRADA':
        case 'COMPRA':
            return 'qty-text-green';
        case 'SALIDA':
        case 'BAJA':
        case 'CONSUMO':
            return 'qty-text-red';
        default:
            return 'qty-text-blue';
    }
};

const getQtyPrefix = (type?: string) => {
    switch ((type || '').toUpperCase()) {
        case 'ENTRADA':
        case 'COMPRA':
            return '+';
        case 'SALIDA':
        case 'BAJA':
        case 'CONSUMO':
            return '-';
        default:
            return '';
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
            empty-subtitle="No se encontraron registros de entradas, salidas o transferencias para este artículo."
        >
            <template #cell-fecha="{ item }">
                <span class="cell-date-text">{{ formatDateTime(item.fecha || item.created_at) }}</span>
            </template>

            <template #cell-tipo="{ item }">
                <BaseBadge :variant="getBadgeVariant(item.tipo_movimiento || item.tipo)">
                    {{ (item.tipo_movimiento || item.tipo || 'AJUSTE').toUpperCase() }}
                </BaseBadge>
            </template>

            <template #cell-cantidad="{ item }">
                <strong :class="getQtyClass(item.tipo_movimiento || item.tipo)">
                    {{ getQtyPrefix(item.tipo_movimiento || item.tipo) }}{{ item.cantidad }} uds
                </strong>
            </template>

            <template #cell-motivo="{ item }">
                <div class="cell-motivo-wrap">
                    <span class="motivo-text">{{ item.motivo || 'Movimiento de inventario' }}</span>
                    <div v-if="item.origen_departamento_nombre || item.destino_departamento_nombre || item.origen || item.destino" class="transfer-route-badge">
                        <span>{{ item.origen_departamento_nombre || item.origen || 'Almacén Central' }}</span>
                        <span class="route-arrow">➔</span>
                        <span>{{ item.destino_departamento_nombre || item.destino || 'Destino' }}</span>
                    </div>
                </div>
            </template>

            <template #cell-referencia="{ item }">
                <span v-if="item.referencia_id" class="ref-badge">
                    {{ item.referencia_tipo ? `${item.referencia_tipo.toUpperCase()} #${item.referencia_id}` : `Ref #${item.referencia_id}` }}
                </span>
                <span v-else class="text-muted">—</span>
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

.cell-date-text {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
}

.cell-motivo-wrap {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.motivo-text {
    font-size: 13px;
    line-height: 1.4;
}

.transfer-route-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    background: var(--bg-sub);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    width: fit-content;
    border: var(--stroke-w) solid var(--stroke-subtle);
}

.route-arrow {
    color: var(--orange);
    font-weight: 700;
}

.ref-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: var(--radius-sm);
    background: rgba(249, 115, 22, 0.1);
    color: var(--orange);
    font-size: 11px;
    font-weight: 600;
}

.text-muted {
    color: var(--text-muted);
}
</style>
