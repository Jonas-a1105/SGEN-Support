<script setup lang="ts">
defineProps<{
    procesador: string;
    memoriaRam: string;
    almacenamiento: string;
    sistemaOperativo: string;
}>();

const emit = defineEmits<{
    (e: 'update:procesador', val: string): void;
    (e: 'update:memoriaRam', val: string): void;
    (e: 'update:almacenamiento', val: string): void;
    (e: 'update:sistemaOperativo', val: string): void;
}>();
</script>

<template>
    <div id="wizardStep2" class="step-pane-body">
        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="wizCpu">Procesador</label>
                <input
                    :value="procesador"
                    type="text"
                    class="form-input"
                    id="wizCpu"
                    placeholder="Ej: Intel Core i5 / N/A"
                    @input="emit('update:procesador', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="wizRam">Memoria RAM</label>
                <input
                    :value="memoriaRam"
                    type="text"
                    class="form-input"
                    id="wizRam"
                    placeholder="Ej: 8GB DDR4 / N/A"
                    @input="emit('update:memoriaRam', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="wizStorage">Almacenamiento</label>
                <input
                    :value="almacenamiento"
                    type="text"
                    class="form-input"
                    id="wizStorage"
                    placeholder="Ej: SSD 256GB / MicroSD"
                    @input="emit('update:almacenamiento', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="wizOs">Sistema Operativo / Firmware</label>
                <input
                    :value="sistemaOperativo"
                    type="text"
                    class="form-input"
                    id="wizOs"
                    placeholder="Ej: Windows 11 Pro / Firmware v2.1"
                    @input="emit('update:sistemaOperativo', ($event.target as HTMLInputElement).value)"
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
