<script setup lang="ts">
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

        <div class="form-group">
            <label class="form-label">Fecha y Hora Programada</label>
            <input
                :value="fecha"
                type="datetime-local"
                class="form-input"
                required
                @input="emit('update:fecha', ($event.target as HTMLInputElement).value)"
            />
        </div>

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

        <div class="form-group">
            <label class="form-label">Recurrencia (Próxima Fecha Auto.)</label>
            <select
                :value="frecuencia"
                class="form-select"
                @change="emit('update:frecuencia', ($event.target as HTMLSelectElement).value)"
            >
                <option value="unica">Una sola vez</option>
                <option value="mensual">Mensual</option>
                <option value="trimestral">Trimestral</option>
                <option value="semestral">Semestral</option>
                <option value="anual">Anual</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Próxima Fecha (Estimada)</label>
            <input
                :value="proximaFecha"
                type="date"
                class="form-input"
                @input="emit('update:proximaFecha', ($event.target as HTMLInputElement).value)"
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
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.02em;
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.form-input:focus,
.form-select:focus {
    border-color: var(--orange);
}

.helper-text {
    font-size: 11px;
    color: var(--text-dim);
    margin-top: 4px;
    line-height: 1.4;
}
</style>
