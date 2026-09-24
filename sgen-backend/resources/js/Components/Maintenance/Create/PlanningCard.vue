<script setup lang="ts">
import BaseDatePicker from '@/Components/UI/BaseDatePicker.vue';
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';

defineProps<{
    fecha: string;
    duracion: number;
    frecuencia: string;
    proximaFecha: string;
}>();

const emit = defineEmits<{
    (e: 'update:fecha', val: string): void;
    (e: 'update:duracion', val: number): void;
    (e: 'update:frecuencia', val: string): void;
    (e: 'update:proximaFecha', val: string): void;
}>();

const frequencyOptions = [
    { value: 'unica', label: 'Una sola vez' },
    { value: 'mensual', label: 'Mensual' },
    { value: 'trimestral', label: 'Trimestral' },
    { value: 'semestral', label: 'Semestral' },
    { value: 'anual', label: 'Anual' },
];
</script>

<template>
    <div class="form-section-card">
        <div class="card-section-header">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            <span>3. Planificación</span>
        </div>

        <BaseDatePicker
            :model-value="fecha"
            type="datetime-local"
            label="Fecha y Hora Programada"
            required
            @update:model-value="(val) => emit('update:fecha', val)"
        />

        <div class="form-group">
            <label class="form-label">Duración Estimada (minutos)</label>
            <input
                :value="duracion"
                type="number"
                class="form-input"
                min="5"
                step="5"
                required
                @input="emit('update:duracion', Number(($event.target as HTMLInputElement).value))"
            />
            <span class="helper-text">Tiempo estimado para la ejecución de la tarea.</span>
        </div>

        <BaseCombobox
            :model-value="frecuencia"
            label="Recurrencia (Próxima Fecha Auto.)"
            :options="frequencyOptions"
            :searchable="false"
            @update:model-value="(val) => emit('update:frecuencia', String(val))"
        />

        <BaseDatePicker
            :model-value="proximaFecha"
            type="date"
            label="Próxima Fecha (Estimada)"
            @update:model-value="(val) => emit('update:proximaFecha', val)"
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
    letter-spacing: 0.05em;
}

.card-section-header svg {
    width: 18px;
    height: 18px;
    stroke: var(--orange);
    fill: none;
    stroke-width: 2;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.form-input {
    width: 100%;
    height: 42px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    color: var(--text);
    padding: 0 14px;
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    border-color: var(--orange);
}

.helper-text {
    font-size: 11px;
    color: var(--text-dim);
}
</style>
