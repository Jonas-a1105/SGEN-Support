<script setup lang="ts">
interface Props {
    modelValue: string | number;
    label?: string;
    type?: string;
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    min?: number | string;
    max?: number | string;
    step?: number | string;
}

withDefaults(defineProps<Props>(), {
    type: 'text',
    placeholder: '',
    error: '',
    required: false,
    disabled: false,
});

defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();
</script>

<template>
    <div class="field-group">
        <label v-if="label" class="field-label">
            {{ label }}
            <span v-if="required" class="field-required">*</span>
        </label>
        <input
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :min="min"
            :max="max"
            :step="step"
            :class="['field-input', { 'has-error': !!error }]"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        >
        <span v-if="error" class="field-error">{{ error }}</span>
    </div>
</template>

<style scoped>
.field-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    width: 100%;
}

.field-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.field-required {
    color: var(--red);
}

.field-input {
    width: 100%;
    padding: 10px 14px;
    font-size: 13px;
    font-family: var(--font-sans);
    color: var(--text);
    background-color: transparent;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-md);
    outline: none;
    transition: border-color var(--transition-fast);
    box-shadow: none !important;
}

.field-input::placeholder {
    color: var(--text-dim);
}

.field-input:focus {
    border-color: var(--orange);
}

.field-input:disabled {
    background-color: var(--stroke-subtle);
    cursor: not-allowed;
    opacity: 0.6;
}

.field-input.has-error {
    border-color: var(--red);
}

.field-error {
    font-size: 11px;
    color: var(--red);
    font-weight: 600;
    margin-top: 2px;
}
</style>
