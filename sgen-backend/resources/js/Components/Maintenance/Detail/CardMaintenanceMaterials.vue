<script setup lang="ts">
import { computed } from 'vue';
import { BaseCard, BaseBadge, BaseButton, BaseDataTable, type DataTableColumn } from '@/Components/UI';
import { formatCurrency, formatDateTime } from '@/Utils/formatters';
import type { MaintenanceMaterialItem } from '@/Types/maintenance';

const props = withDefaults(
    defineProps<{
        materiales?: MaintenanceMaterialItem[];
        isClosed?: boolean;
    }>(),
    {
        materiales: () => [],
        isClosed: false,
    }
);

const emit = defineEmits<{
    (e: 'open-modal'): void;
}>();

const columns: DataTableColumn[] = [
    { key: 'codigo', label: 'CÓDIGO', width: '130px' },
    { key: 'nombre', label: 'REPUESTO / PIEZA' },
    { key: 'cantidad', label: 'CANTIDAD', width: '110px', align: 'right' },
    { key: 'costo_unitario', label: 'COSTO UNIT.', width: '120px', align: 'right' },
    { key: 'subtotal', label: 'SUBTOTAL', width: '120px', align: 'right' },
    { key: 'fecha', label: 'FECHA REGISTRO', width: '160px' },
];

const totalCost = computed(() => {
    return (props.materiales || []).reduce((acc, m) => acc + (m.costo_total || 0), 0);
});

const totalUnits = computed(() => {
    return (props.materiales || []).reduce((acc, m) => acc + (m.cantidad || 0), 0);
});
</script>

<template>
    <BaseCard class="maint-section-card" padding="lg">
        <header class="materials-header">
            <div class="materials-header-left">
                <div class="materials-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                        <line x1="12" y1="22.08" x2="12" y2="12" />
                    </svg>
                </div>
                <div>
                    <div class="materials-title-row">
                        <h2 class="materials-title">Repuestos y Materiales</h2>
                        <BaseBadge variant="neutral">{{ materiales.length }} ítems</BaseBadge>
                    </div>
                    <p class="materials-sub">Piezas y consumibles descontados automáticamente de inventario</p>
                </div>
            </div>

            <div class="materials-header-right">
                <BaseButton
                    v-if="!isClosed"
                    variant="primary"
                    size="sm"
                    type="button"
                    @click="emit('open-modal')"
                >
                    <svg viewBox="0 0 24 24" class="btn-svg" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Consumir Material
                </BaseButton>
            </div>
        </header>

        <BaseDataTable
            :columns="columns"
            :items="materiales"
            :paginate="materiales.length > 5"
            :per-page="5"
            item-label="repuestos"
            empty-title="Sin repuestos consumidos"
            empty-subtitle="Esta orden no ha utilizado repuestos ni consumibles del almacén central."
        >
            <template #cell-codigo="{ item }">
                <BaseBadge variant="code">{{ item.item_codigo }}</BaseBadge>
            </template>

            <template #cell-nombre="{ item }">
                <strong class="item-name">{{ item.item_nombre }}</strong>
            </template>

            <template #cell-cantidad="{ item }">
                <span class="qty-badge">{{ item.cantidad }} uds</span>
            </template>

            <template #cell-costo_unitario="{ item }">
                <span class="text-mono">{{ formatCurrency(item.costo_unitario) }}</span>
            </template>

            <template #cell-subtotal="{ item }">
                <strong class="text-mono highlight-total">{{ formatCurrency(item.costo_total) }}</strong>
            </template>

            <template #cell-fecha="{ item }">
                <span class="text-muted-date">{{ formatDateTime(item.fecha) }}</span>
            </template>
        </BaseDataTable>

        <!-- Footer Summary Bar when items exist -->
        <footer v-if="materiales.length > 0" class="materials-summary-bar">
            <div class="summary-stat">
                <span class="stat-label">Unidades Totales:</span>
                <strong class="stat-value">{{ totalUnits }} uds</strong>
            </div>
            <div class="summary-stat total">
                <span class="stat-label">Costo Total Materiales:</span>
                <strong class="stat-value highlight">{{ formatCurrency(totalCost) }}</strong>
            </div>
        </footer>
    </BaseCard>
</template>

<style scoped>
.maint-section-card {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.materials-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--space-3);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    gap: var(--space-3);
    flex-wrap: wrap;
}

.materials-header-left {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.materials-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md);
    background: rgba(249, 115, 22, 0.12);
    color: var(--orange);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.materials-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.materials-title-row {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.materials-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
}

.materials-sub {
    margin: 2px 0 0 0;
    font-size: 12px;
    color: var(--text-muted);
}

.btn-svg {
    width: 15px;
    height: 15px;
    margin-right: 4px;
}

.item-name {
    color: var(--text);
    font-size: 13px;
}

.qty-badge {
    display: inline-block;
    padding: 2px 8px;
    background: rgba(37, 99, 235, 0.08);
    color: var(--blue);
    font-weight: 600;
    border-radius: var(--radius-sm);
    font-size: 12px;
}

.text-mono {
    font-family: monospace;
    font-size: 12px;
}

.highlight-total {
    color: var(--text);
}

.text-muted-date {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
}

.materials-summary-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: var(--space-6);
    padding-top: var(--space-3);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.summary-stat {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 13px;
}

.stat-label {
    color: var(--text-muted);
}

.stat-value {
    color: var(--text);
    font-weight: 700;
}

.stat-value.highlight {
    color: var(--orange);
    font-size: 15px;
}
</style>
