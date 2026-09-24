<script setup lang="ts">
import { computed } from 'vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';
import type { Equipment } from './types';

const props = defineProps<{
    modelValue: string;
    equipments?: Equipment[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const equipmentOptions = computed<ComboboxOption[]>(() => {
    return (props.equipments || []).map((eq) => ({
        value: eq.id,
        label: `${eq.model} • ${eq.code}`,
        sublabel: `${eq.type} - Depto: ${eq.department}`,
    }));
});
</script>

<template>
    <div class="form-section-card">
        <div class="card-section-header">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg>
            <span>1. Selección del Activo</span>
        </div>

        <BaseCombobox
            :model-value="modelValue"
            label="Equipo del Inventario"
            placeholder="Seleccione un equipo del inventario..."
            search-placeholder="Buscar por modelo, código, tipo o depto..."
            :options="equipmentOptions"
            :searchable="true"
            required
            help-text="Busque y seleccione el activo a registrar para servicio o mantenimiento."
            @update:model-value="(val) => emit('update:modelValue', String(val ?? ''))"
        />
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
</style>
