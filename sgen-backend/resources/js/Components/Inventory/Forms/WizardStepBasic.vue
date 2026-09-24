<script setup lang="ts">
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
            <div class="form-group">
                <label class="form-label" for="wizType">
                    Tipo de Equipo <span class="required-asterisk">*</span>
                </label>
                <select
                    :value="tipo"
                    class="form-select"
                    id="wizType"
                    @change="emit('update:tipo', ($event.target as HTMLSelectElement).value)"
                >
                    <option value="CAMARA">CAMARA</option>
                    <option value="Computadora">Computadora</option>
                    <option value="Impresora">Impresora</option>
                    <option value="Servidor">Servidor</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="wizStatus">
                    Estado Inicial <span class="required-asterisk">*</span>
                </label>
                <select
                    :value="estado"
                    class="form-select"
                    id="wizStatus"
                    @change="emit('update:estado', ($event.target as HTMLSelectElement).value)"
                >
                    <option value="Disponible">Disponible</option>
                    <option value="En uso">En uso</option>
                    <option value="Reparación">Reparación</option>
                    <option value="Baja">Baja</option>
                </select>
            </div>
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

.form-input,
.form-select {
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

.form-input:focus,
.form-select:focus {
    border-color: var(--orange);
}

@media (max-width: 768px) {
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
