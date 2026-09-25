<script setup lang="ts">
import { computed } from 'vue';
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';
import type { FormTechnician } from './types';

const props = defineProps<{
    requester: string;
    department: string;
    selectedTechId: number | null;
    technicians?: FormTechnician[];
}>();

const emit = defineEmits<{
    (e: 'update:requester', value: string): void;
    (e: 'update:department', value: string): void;
    (e: 'update:selectedTechId', value: number | null): void;
}>();

const technicianOptions = computed(() =>
    (props.technicians || []).map((tech) => ({
        value: tech.id,
        label: tech.name,
        sublabel: `${tech.specialty} • ${tech.active_tickets} tickets activos`,
        badge: tech.active_tickets === 0 ? 'Disponible' : `${tech.active_tickets} act.`,
        badgeVariant: tech.active_tickets === 0 ? ('success' as const) : ('warning' as const),
    }))
);
</script>

<template>
    <div class="tf-section">
        <div class="tf-section-header">
            <span class="tf-section-number">3</span>
            <h3 class="tf-section-title">Asignación y Solicitante</h3>
        </div>

        <div class="tf-grid-row">
            <div class="tf-form-field">
                <label class="tf-field-label" for="solicitanteInput">Nombre del Solicitante</label>
                <input
                    id="solicitanteInput"
                    :value="requester"
                    type="text"
                    class="tf-text-input"
                    placeholder="Ej: Lic. Maria Gomez"
                    @input="emit('update:requester', ($event.target as HTMLInputElement).value)"
                />
            </div>

            <div class="tf-form-field">
                <label class="tf-field-label" for="deptoInput">Departamento</label>
                <input
                    id="deptoInput"
                    :value="department"
                    type="text"
                    class="tf-text-input"
                    placeholder="Ej: Dpto. de Administración"
                    @input="emit('update:department', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <div class="tf-form-field">
            <BaseCombobox
                :model-value="selectedTechId"
                label="Técnico Responsable Asignado"
                placeholder="Seleccionar técnico..."
                :options="technicianOptions"
                @update:model-value="(val) => emit('update:selectedTechId', val === '' || val === null || val === undefined ? null : Number(val))"
            />
        </div>
    </div>
</template>

<style scoped>
.tf-section {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.tf-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
}
.tf-section-number {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: var(--orange, #2563eb);
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 11px;
    font-weight: 700;
}
.tf-section-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}
.tf-grid-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 600px) {
    .tf-grid-row {
        grid-template-columns: 1fr;
    }
}
.tf-form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.tf-field-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted, #8e9199);
}
.tf-text-input {
    width: 100%;
    height: 42px;
    border-radius: 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text, #f4f4f6);
    font-size: 13px;
    padding: 0 14px;
    outline: none;
    box-shadow: none !important;
    transition: border-color 0.2s ease;
}
.tf-text-input:focus {
    border-color: var(--orange, #2563eb);
}
</style>
