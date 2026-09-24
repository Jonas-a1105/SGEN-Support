<script setup lang="ts">
import type { Equipment } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

defineProps<{
    equipment: Equipment;
}>();

const emit = defineEmits<{
    (e: 'change-location'): void;
}>();
</script>

<template>
    <BaseCard padding="lg" class="device-location-card">
        <div class="device-section-label">
            <svg viewBox="0 0 24 24" class="svg-icon-standard">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
            </svg>
            <span>Ubicación Actual</span>
        </div>

        <div class="device-location-stack">
            <div class="device-location-row">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="20" rx="2" />
                        <line x1="9" y1="22" x2="9" y2="2" />
                        <line x1="15" y1="22" x2="15" y2="2" />
                    </svg>
                </div>
                <div class="location-details">
                    <span class="device-sublabel">Departamento</span>
                    <strong class="device-strong-text" id="detailEquipDept">
                        {{ equipment.departamento_nombre || 'No Asignado' }}
                    </strong>
                    <span class="device-dim-text">
                        {{ equipment.ubicacion_fisica || 'INVENTARIO DE INFORMATICA' }}
                    </span>
                </div>
            </div>

            <div class="device-location-row">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="location-details">
                    <span class="device-sublabel">Asignado a</span>
                    <span class="device-user-name" id="detailEquipUser">
                        {{ equipment.empleado_nombre ? (equipment.empleado_nombre + ' ' + (equipment.empleado_apellido || '')) : '-- Sin asignar --' }}
                    </span>
                </div>
            </div>

            <BaseButton
                variant="secondary"
                id="btnChangeLocation"
                class="w-full btn-change-loc"
                @click="emit('change-location')"
            >
                Cambiar Ubicación / Asignación
            </BaseButton>
        </div>
    </BaseCard>
</template>

<style scoped>
.device-location-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.device-section-label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    margin-bottom: var(--space-4);
}

.svg-icon-standard {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.device-location-stack {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.device-location-row {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.spec-icon-box {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: var(--text-muted);
    flex-shrink: 0;
}

.spec-icon-box svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.location-details {
    display: flex;
    flex-direction: column;
}

.device-sublabel {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 2px;
}

.device-strong-text {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}

.device-dim-text {
    font-size: 11px;
    color: var(--text-dim);
    margin-top: 2px;
}

.device-user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.w-full {
    width: 100%;
}

.btn-change-loc {
    margin-top: var(--space-2);
}
</style>
