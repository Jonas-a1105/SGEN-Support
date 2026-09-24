<script setup lang="ts">
import ImageDropzone from '../Common/ImageDropzone.vue';

defineProps<{
    sku: string;
    description: string;
}>();

const emit = defineEmits<{
    (e: 'update:sku', val: string): void;
    (e: 'update:description', val: string): void;
}>();
</script>

<template>
    <div class="form-column-stack">
        <ImageDropzone
            title="Subir Imagen Principal"
            subtitle="PNG, JPG hasta 5MB"
        />

        <div class="form-group">
            <label class="form-label" for="inputItemSku">
                Código SKU / Barras <span class="required-asterisk">*</span>
            </label>
            <input
                :value="sku"
                type="text"
                class="form-input"
                id="inputItemSku"
                placeholder="Auto-generar..."
                @input="emit('update:sku', ($event.target as HTMLInputElement).value)"
            />
            <span class="form-hint-text">
                ℹ Si se deja vacío, el sistema generará uno automáticamente.
            </span>
        </div>

        <div class="form-group">
            <label class="form-label" for="inputItemNotes">Descripción / Notas</label>
            <textarea
                :value="description"
                class="form-textarea"
                id="inputItemNotes"
                rows="3"
                placeholder="Detalles técnicos, compatibilidad, color..."
                @input="emit('update:description', ($event.target as HTMLTextAreaElement).value)"
            />
        </div>
    </div>
</template>

<style scoped>
.form-column-stack {
    display: flex;
    flex-direction: column;
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

.form-hint-text {
    font-size: 11px;
    color: var(--text-dim);
    margin-top: 2px;
}

.form-input,
.form-textarea {
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
.form-textarea:focus {
    border-color: var(--orange);
}
</style>
