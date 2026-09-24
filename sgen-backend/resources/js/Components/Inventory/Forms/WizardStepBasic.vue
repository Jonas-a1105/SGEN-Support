<script setup lang="ts">
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';

defineProps<{
    codigoInventario: string;
    numeroSerie: string;
    tipo: string;
    estado: string;
    marca: string;
    modelo: string;
}>();

const emit = defineEmits<{
    (e: 'update:codigoInventario', val: string): void;
    (e: 'update:numeroSerie', val: string): void;
    (e: 'update:tipo', val: string): void;
    (e: 'update:estado', val: string): void;
    (e: 'update:marca', val: string): void;
    (e: 'update:modelo', val: string): void;
}>();

const typeOptions = [
    { value: 'CAMARA', label: 'CAMARA' },
    { value: 'Computadora', label: 'Computadora' },
    { value: 'Impresora', label: 'Impresora' },
    { value: 'Servidor', label: 'Servidor' },
    { value: 'Otro', label: 'Otro' },
];

const statusOptions = [
    { value: 'Disponible', label: 'Disponible' },
    { value: 'En uso', label: 'En uso' },
    { value: 'Reparación', label: 'Reparación' },
    { value: 'Baja', label: 'Baja' },
];
</script>

<template>
    <div id="wizardStep1" class="step-pane-body">
        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="wizCode">
                    Código Inventario <span class="required-asterisk">*</span>
                </label>
                <input
                    :value="codigoInventario"
                    type="text"
                    class="form-input"
                    id="wizCode"
                    required
                    @input="emit('update:codigoInventario', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="wizSerial">
                    Nro de Serie <span class="required-asterisk">*</span>
                </label>
                <input
                    :value="numeroSerie"
                    type="text"
                    class="form-input"
                    id="wizSerial"
                    required
                    @input="emit('update:numeroSerie', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <div class="form-row-2">
            <BaseCombobox
                :model-value="tipo"
                label="Tipo de Equipo *"
                :options="typeOptions"
                :searchable="false"
                required
                @update:model-value="(val) => emit('update:tipo', String(val ?? ''))"
            />
            <BaseCombobox
                :model-value="estado"
                label="Estado Inicial *"
                :options="statusOptions"
                :searchable="false"
                required
                @update:model-value="(val) => emit('update:estado', String(val ?? ''))"
            />
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="wizBrand">
                    Marca <span class="required-asterisk">*</span>
                </label>
                <input
                    :value="marca"
                    type="text"
                    class="form-input"
                    id="wizBrand"
                    required
                    @input="emit('update:marca', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="wizModel">
                    Modelo <span class="required-asterisk">*</span>
                </label>
                <input
                    :value="modelo"
                    type="text"
                    class="form-input"
                    id="wizModel"
                    required
                    @input="emit('update:modelo', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.step-pane-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-row-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
    align-items: flex-end;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.required-asterisk {
    color: var(--red);
}

.form-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--radius-md);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 13px;
    font-family: var(--font-sans);
    outline: none;
    transition: border-color var(--transition-fast);
}

.form-input:focus {
    border-color: var(--orange);
}

@media (max-width: 768px) {
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
