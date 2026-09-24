<script setup lang="ts">
import type { Equipment } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

defineProps<{
    equipment: Equipment;
}>();
</script>

<template>
    <BaseCard padding="lg" class="device-hero-card">
        <div class="qr-badge-corner" title="Código QR del equipo">
            <svg viewBox="0 0 24 24" class="svg-icon-standard">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
            </svg>
        </div>

        <div class="device-illustration-box">
            <svg viewBox="0 0 24 24">
                <rect x="4" y="2" width="16" height="20" rx="2" />
                <line x1="8" y1="6" x2="16" y2="6" />
                <line x1="8" y1="10" x2="16" y2="10" />
                <circle cx="12" cy="16" r="1.5" />
            </svg>
        </div>

        <BaseBadge variant="success" id="detailEquipStatusBadge" class="status-badge-margin">
            ● {{ equipment.estado || 'Disponible' }}
        </BaseBadge>

        <h2 class="device-title" id="detailEquipBrandModel">
            {{ equipment.marca }} {{ equipment.modelo }}
        </h2>

        <span class="device-category-label" id="detailEquipCategory">
            {{ equipment.tipo_equipo || equipment.tipo || 'Equipo' }}
        </span>

        <div class="device-meta-list">
            <div class="meta-key-val-row">
                <span class="meta-muted-key">Código</span>
                <BaseBadge variant="code" id="detailEquipCode">
                    {{ equipment.codigo_inventario }}
                </BaseBadge>
            </div>
            <div class="meta-key-val-row">
                <span class="meta-muted-key">Serial</span>
                <strong class="meta-primary-val" id="detailEquipSerial">
                    {{ equipment.numero_serie || 'N/A' }}
                </strong>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.device-hero-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
}

.qr-badge-corner {
    position: absolute;
    right: 16px;
    top: 16px;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--stroke-subtle);
    display: grid;
    place-items: center;
    color: var(--text-dim);
}

.svg-icon-standard {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.device-illustration-box {
    width: 90px;
    height: 90px;
    border-radius: 20px;
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    margin-bottom: var(--space-4);
}

.device-illustration-box svg {
    width: 44px;
    height: 44px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.status-badge-margin {
    margin-bottom: var(--space-2);
}

.device-title {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.25;
    margin: 0 0 var(--space-1) 0;
    color: var(--text);
}

.device-category-label {
    font-size: 12px;
    color: var(--text-muted);
}

.device-meta-list {
    width: 100%;
    margin-top: var(--space-5);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    padding-top: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.meta-key-val-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
}

.meta-muted-key {
    color: var(--text-muted);
}

.meta-primary-val {
    color: var(--text);
    font-family: var(--font-mono);
}
</style>
