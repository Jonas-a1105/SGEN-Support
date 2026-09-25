<script setup lang="ts">
import { BaseCard, BaseBadge } from '@/Components/UI';
import HistoryMovementsTable from '@/Components/Inventory/History/HistoryMovementsTable.vue';
import type { InventoryMovement } from '@/Types/inventory';

withDefaults(
    defineProps<{
        movements: InventoryMovement[];
    }>(),
    {
        movements: () => [],
    }
);
</script>

<template>
    <BaseCard padding="lg" class="kardex-card">
        <header class="kardex-header">
            <div class="kardex-title-wrap">
                <div class="kardex-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 14 14" />
                    </svg>
                </div>
                <div>
                    <div class="kardex-title-row">
                        <h3 class="kardex-title">Kardex / Historial de Movimientos</h3>
                        <BaseBadge variant="neutral">{{ movements.length }} registros</BaseBadge>
                    </div>
                    <p class="kardex-sub">Trazabilidad de auditoría: compras, consumos en mantenimiento, transferencias y bajas.</p>
                </div>
            </div>
        </header>

        <HistoryMovementsTable
            :movements="movements"
            paginate
            :per-page="10"
        />
    </BaseCard>
</template>

<style scoped>
.kardex-card {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.kardex-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--space-3);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.kardex-title-wrap {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.kardex-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-md);
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.kardex-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.kardex-title-row {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.kardex-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
}

.kardex-sub {
    margin: 2px 0 0 0;
    font-size: 12px;
    color: var(--text-muted);
}
</style>
