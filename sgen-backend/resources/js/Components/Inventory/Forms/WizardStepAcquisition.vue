<script setup lang="ts">
defineProps<{
    valorCompra: string | number;
    proveedor: string;
}>();

const emit = defineEmits<{
    (e: 'update:valorCompra', val: string): void;
    (e: 'update:proveedor', val: string): void;
}>();
</script>

<template>
    <div id="wizardStep4" class="step-pane-body">
        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="wizCost">Valor de Compra ($)</label>
                <input
                    :value="valorCompra"
                    type="number"
                    step="0.01"
                    class="form-input"
                    id="wizCost"
                    placeholder="0.00"
                    @input="emit('update:valorCompra', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="wizVendor">Proveedor</label>
                <input
                    :value="proveedor"
                    type="text"
                    class="form-input"
                    id="wizVendor"
                    placeholder="Empresa proveedora"
                    @input="emit('update:proveedor', ($event.target as HTMLInputElement).value)"
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
