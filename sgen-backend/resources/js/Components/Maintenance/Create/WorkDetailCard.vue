<script setup lang="ts">
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';
import MaintenanceChecklistEditor from './MaintenanceChecklistEditor.vue';
import type { ChecklistTask } from './types';

defineProps<{
    tipoMantenimiento: string;
    estado: string;
    descripcion: string;
    observaciones: string;
    checklist: ChecklistTask[];
}>();

const emit = defineEmits<{
    (e: 'update:tipoMantenimiento', val: string): void;
    (e: 'update:estado', val: string): void;
    (e: 'update:descripcion', val: string): void;
    (e: 'update:observaciones', val: string): void;
    (e: 'update:checklist', val: ChecklistTask[]): void;
}>();

const statusOptions = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'en_proceso', label: 'En Progreso' },
    { value: 'programado', label: 'Programado' },
];
</script>

<template>
    <div class="form-section-card">
        <div class="card-section-header">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
            <span>2. Detalle del Trabajo</span>
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label">Tipo de Mantenimiento</label>
                <div class="segmented-control">
                    <button
                        type="button"
                        class="segment-btn"
                        :class="{ active: tipoMantenimiento === 'preventivo' }"
                        @click="emit('update:tipoMantenimiento', 'preventivo')"
                    >
                        Preventivo
                    </button>
                    <button
                        type="button"
                        class="segment-btn"
                        :class="{ active: tipoMantenimiento === 'correctivo' }"
                        @click="emit('update:tipoMantenimiento', 'correctivo')"
                    >
                        Correctivo
                    </button>
                </div>
            </div>

            <BaseCombobox
                :model-value="estado"
                label="Estado Inicial"
                :options="statusOptions"
                :searchable="false"
                @update:model-value="(val) => emit('update:estado', String(val ?? ''))"
            />
        </div>

        <div class="form-group">
            <label class="form-label">Descripción General</label>
            <textarea
                :value="descripcion"
                class="form-textarea"
                rows="3"
                placeholder="Describa el objetivo o la falla detectada..."
                required
                @input="emit('update:descripcion', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
        </div>

        <!-- CHECKLIST DE TAREAS (SRP: componente dedicado) -->
        <MaintenanceChecklistEditor
            :checklist="checklist"
            @update:checklist="(val) => emit('update:checklist', val)"
        />

        <div class="form-group">
            <label class="form-label">Observaciones Adicionales</label>
            <textarea
                :value="observaciones"
                class="form-textarea"
                rows="3"
                placeholder="Notas internas..."
                @input="emit('update:observaciones', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
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

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    align-items: flex-end;
}

.segmented-control {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 8px;
    background: var(--bg-sub);
    overflow: hidden;
    padding: 2px;
    gap: 2px;
    box-shadow: none !important;
    height: 40px;
}

.segment-btn {
    height: 100%;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.segment-btn.active {
    background: var(--bg-card);
    color: var(--text);
    border: 1px solid var(--stroke);
}

.form-textarea {
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

.form-textarea:focus {
    border-color: var(--orange);
}

@media (max-width: 768px) {
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
