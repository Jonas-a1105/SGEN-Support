<script setup lang="ts">
import { BaseBadge } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    equipment: EquipmentDetail;
}>();

const formatCurrency = (val: number | null): string => {
    if (val === null || val === undefined) return 'N/A';
    return '$' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getWarrantyProgressClass = (pct: number): string => {
    const rounded = Math.round(Math.min(100, Math.max(0, pct)) / 5) * 5;
    return `progress-pct-${rounded}`;
};
</script>

<template>
    <div class="tab-panel-purchase">
        <div class="purchase-details-card">
            <div class="purchase-top-row">
                <div class="vendor-box">
                    <span class="purchase-label">PROVEEDOR</span>
                    <h4 class="vendor-name">{{ equipment.supplier || 'No registrado' }}</h4>
                </div>
                <div class="price-box">
                    <span class="purchase-label">VALOR DE COMPRA</span>
                    <h3 class="price-val">{{ formatCurrency(equipment.purchaseValue) }}</h3>
                </div>
            </div>

            <div class="warranty-card-section">
                <div class="warranty-header-row">
                    <span class="warranty-title">Estado de la Garantía</span>
                    <BaseBadge
                        :variant="equipment.warrantyStatus === 'active' ? 'success' : equipment.warrantyStatus === 'warning' ? 'warning' : 'danger'"
                        size="md"
                        dot
                    >
                        {{ equipment.warrantyStatus === 'active' ? 'Garantía Vigente' : equipment.warrantyStatus === 'warning' ? 'Por Vencer' : 'Garantía Expirada' }}
                    </BaseBadge>
                </div>

                <div v-if="equipment.warranty" class="warranty-progress-wrap">
                    <div class="progress-track">
                        <div
                            class="progress-fill"
                            :class="[getWarrantyProgressClass(equipment.warrantyPercent), `warranty-${equipment.warrantyStatus}`]"
                        ></div>
                    </div>
                    <div class="warranty-dates-row">
                        <span>Adquisición: {{ equipment.purchaseDate || 'N/A' }}</span>
                        <span v-if="equipment.warrantyRemaining">
                            Restante: {{ equipment.warrantyRemaining }}
                        </span>
                        <span>Vencimiento: {{ equipment.warranty }}</span>
                    </div>
                </div>

                <div v-else class="no-warranty-notice">
                    Sin fecha de vencimiento de garantía registrada en el sistema.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.purchase-details-card {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.purchase-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--space-5);
    border-bottom: 1px solid var(--stroke-subtle);
}

.purchase-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: var(--text-dim);
}

.vendor-name {
    margin: var(--space-1) 0 0 0;
    font-size: 18px;
    color: var(--text);
}

.price-val {
    margin: var(--space-1) 0 0 0;
    font-size: 24px;
    font-weight: 800;
    color: var(--brand);
    font-family: var(--font-mono);
}

.warranty-card-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.warranty-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.warranty-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
}

.warranty-progress-wrap {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.progress-track {
    height: 10px;
    background: var(--stroke);
    border-radius: var(--radius-pill);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: var(--radius-pill);
    transition: width 0.3s ease;
}

.warranty-active {
    background: var(--brand);
}

.warranty-warning {
    background: var(--yellow);
}

.warranty-expired {
    background: var(--red);
}

/* Progress width classes */
.progress-pct-0 { width: 0%; }
.progress-pct-5 { width: 5%; }
.progress-pct-10 { width: 10%; }
.progress-pct-15 { width: 15%; }
.progress-pct-20 { width: 20%; }
.progress-pct-25 { width: 25%; }
.progress-pct-30 { width: 30%; }
.progress-pct-35 { width: 35%; }
.progress-pct-40 { width: 40%; }
.progress-pct-45 { width: 45%; }
.progress-pct-50 { width: 50%; }
.progress-pct-55 { width: 55%; }
.progress-pct-60 { width: 60%; }
.progress-pct-65 { width: 65%; }
.progress-pct-70 { width: 70%; }
.progress-pct-75 { width: 75%; }
.progress-pct-80 { width: 80%; }
.progress-pct-85 { width: 85%; }
.progress-pct-90 { width: 90%; }
.progress-pct-95 { width: 95%; }
.progress-pct-100 { width: 100%; }

.warranty-dates-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-dim);
}

.no-warranty-notice {
    padding: var(--space-4);
    background: var(--bg-sub);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-size: 13px;
    text-align: center;
}
</style>
