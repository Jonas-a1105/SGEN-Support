<script setup lang="ts">
import type { Equipment } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = defineProps<{
    equipment?: Equipment | null;
}>();

const emit = defineEmits<{
    (e: 'new-maintenance'): void;
}>();
</script>

<template>
    <BaseCard padding="lg" id="subpaneMaintenance" class="empty-pane-card">
        <div class="empty-icon-circle">
            <svg viewBox="0 0 24 24" class="svg-icon-large">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
            </svg>
        </div>
        <h3 class="empty-pane-title">Sin historial de mantenimiento</h3>
        <p class="empty-pane-desc">
            {{ props.equipment?.codigo_inventario ? `No hay mantenimientos preventivos o correctivos registrados para ${props.equipment.codigo_inventario}.` : 'No hay mantenimientos registrados para este equipo.' }}
        </p>
        <BaseButton
            variant="secondary"
            id="btnAddMaintenance"
            @click="emit('new-maintenance')"
        >
            + Nuevo Mantenimiento
        </BaseButton>
    </BaseCard>
</template>

<style scoped>
.empty-pane-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--space-8) var(--space-5) !important;
}

.empty-icon-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    margin-bottom: var(--space-4);
}

.svg-icon-large {
    width: 28px;
    height: 28px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.empty-pane-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 var(--space-2) 0;
}

.empty-pane-desc {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 380px;
    margin: 0 0 var(--space-5) 0;
    line-height: 1.4;
}
</style>
