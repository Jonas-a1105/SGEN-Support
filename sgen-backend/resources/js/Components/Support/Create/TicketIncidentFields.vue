<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();

const emit = defineEmits<{
    (e: 'update:title', value: string): void;
    (e: 'update:description', value: string): void;
}>();
</script>

<template>
    <div class="tf-section">
        <div class="tf-section-header">
            <span class="tf-section-number">2</span>
            <h3 class="tf-section-title">Descripción de la Incidencia</h3>
        </div>

        <div class="tf-form-field">
            <label class="tf-field-label" for="tituloInput">Título del Problema / Asunto *</label>
            <input
                id="tituloInput"
                :value="title"
                type="text"
                class="tf-text-input"
                placeholder="Ej: Falla de encendido en CPU de contabilidad"
                required
                @input="emit('update:title', ($event.target as HTMLInputElement).value)"
            />
        </div>

        <div class="tf-form-field">
            <label class="tf-field-label" for="descripcionInput">Detalle Técnico / Descripción *</label>
            <textarea
                id="descripcionInput"
                :value="description"
                class="tf-textarea"
                rows="4"
                placeholder="Describe detalladamente qué está fallando, comportamiento observado..."
                required
                @input="emit('update:description', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
            <span class="tf-char-hint">Mínimo 10 caracteres (actual: {{ description.length }}).</span>
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
.tf-form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.tf-field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #8e9199);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 2px;
}
.tf-text-input {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}
.tf-text-input:focus {
    border-color: var(--orange, #2563eb);
}
.tf-textarea {
    width: 100%;
    padding: 10px 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    outline: none;
    resize: vertical;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}
.tf-textarea:focus {
    border-color: var(--orange, #2563eb);
}
.tf-char-hint {
    font-size: 10px;
    color: var(--text-dim, #60636d);
}
</style>
