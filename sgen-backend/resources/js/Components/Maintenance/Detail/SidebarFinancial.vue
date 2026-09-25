<script setup lang="ts">
import { computed } from 'vue';
import { BaseCard } from '@/Components/UI';
import { formatCurrency, formatDateTime } from '@/Utils/formatters';
import type { MaintenanceDetail, MaintenanceMaterialItem } from './types';

const props = withDefaults(
    defineProps<{
        maintenance: MaintenanceDetail;
        materiales?: MaintenanceMaterialItem[];
    }>(),
    {
        materiales: () => [],
    }
);

const laborCost = computed(() => Number(props.maintenance.costo || 0));

const materialsCost = computed(() => {
    return (props.materiales || []).reduce((acc, m) => {
        const subtotal = m.costo_total ?? (Number(m.cantidad || 0) * Number(m.costo_unitario || 0));
        return acc + subtotal;
    }, 0);
});

const totalConsolidatedCost = computed(() => laborCost.value + materialsCost.value);
</script>

<template>
    <BaseCard class="maint-sidebar-card" padding="lg">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            Balance de Costos &amp; Liquidación
        </h3>

        <!-- Total Consolidado -->
        <div class="maint-cost-highlight">
            <span class="cost-title">Costo Total Consolidado</span>
            <span class="cost-amount">{{ formatCurrency(totalConsolidatedCost) }}</span>
            <span class="cost-note">Servicio técnico + repuestos consumidos</span>
        </div>

        <!-- Desglose de Costos -->
        <div class="cost-breakdown-box">
            <div class="cost-row">
                <span class="cost-row-label">Mano de Obra / Honorarios:</span>
                <strong class="cost-row-value">{{ formatCurrency(laborCost) }}</strong>
            </div>
            <div class="cost-row">
                <span class="cost-row-label">Materiales / Repuestos ({{ materiales.length }}):</span>
                <strong class="cost-row-value">{{ formatCurrency(materialsCost) }}</strong>
            </div>
        </div>

        <div class="audit-list">
            <div class="audit-row">
                <span class="audit-lbl">Tipo de servicio:</span>
                <span class="audit-val capitalize">{{ maintenance.tipoMantenimiento }}</span>
            </div>
            <div class="audit-row">
                <span class="audit-lbl">Fecha de creación:</span>
                <span class="audit-val">{{ formatDateTime(maintenance.createdAt) }}</span>
            </div>
            <div class="audit-row">
                <span class="audit-lbl">Última actualización:</span>
                <span class="audit-val">{{ formatDateTime(maintenance.updatedAt) }}</span>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.maint-sidebar-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.sidebar-card-title svg {
    width: 16px;
    height: 16px;
    color: var(--blue, #3b82f6);
}

.maint-cost-highlight {
    padding: 14px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cost-title {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
}

.cost-amount {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    font-family: monospace;
}

.cost-note {
    font-size: 11px;
    color: var(--text-muted);
}

.cost-breakdown-box {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 10px 12px;
    background: var(--bg-sub);
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke-subtle);
}

.cost-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.cost-row-label {
    color: var(--text-muted);
}

.cost-row-value {
    color: var(--text);
    font-family: monospace;
}

.audit-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 12px;
}

.audit-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.audit-lbl {
    color: var(--text-muted);
}

.audit-val {
    font-weight: 600;
    color: var(--text);
}

.capitalize {
    text-transform: capitalize;
}
</style>
