<script setup lang="ts">
import { computed } from 'vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';
import type { Technician } from './types';

const props = defineProps<{
    tecnicoId: string;
    costo: string;
    technicians?: Technician[];
}>();

const emit = defineEmits<{
    (e: 'update:tecnicoId', val: string): void;
    (e: 'update:costo', val: string): void;
}>();

const technicianOptions = computed<ComboboxOption[]>(() => {
    return (props.technicians || []).map((tech) => ({
        value: tech.id,
        label: tech.name,
        sublabel: 'Soporte TI',
    }));
});
</script>

<template>
    <div class="form-section-card">
        <div class="card-section-header">
            <svg viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                <line x1="12" y1="22.08" x2="12" y2="12" />
            </svg>
            <span>4. Recursos</span>
        </div>

        <BaseCombobox
            :model-value="tecnicoId"
            label="Asignado a (Técnico)"
            placeholder="Seleccionar técnico..."
            search-placeholder="Buscar técnico..."
            :options="technicianOptions"
            :searchable="true"
            clearable
            @update:model-value="(val) => emit('update:tecnicoId', String(val ?? ''))"
        />

        <div class="form-group">
            <label class="form-label">Costo Estimado ($)</label>
            <input
                :value="costo"
                type="number"
                class="form-input"
                step="0.01"
                min="0"
                placeholder="0.00"
                @input="emit('update:costo', ($event.target as HTMLInputElement).value)"
            />
        </div>
    </div>
</template>

<style scoped>
.form-section-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: none !important;
}

.card-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: 14px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.card-section-header svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    color: var(--orange);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.form-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--radius-sm, 6px);
    border: 1px solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.form-input:focus {
    border-color: var(--orange);
}
</style>
