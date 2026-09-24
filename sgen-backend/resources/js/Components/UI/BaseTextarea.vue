<script setup lang="ts">
interface Props {
    modelValue: string;
    label?: string;
    placeholder?: string;
    error?: string;
    rows?: number;
    disabled?: boolean;
    required?: boolean;
    maxlength?: number;
}

withDefaults(defineProps<Props>(), {
    label: '',
    placeholder: '',
    error: '',
    rows: 3,
    disabled: false,
    required: false,
});

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="field-group">
        <div v-if="label || maxlength" class="label-row">
            <label v-if="label" class="field-label">
                {{ label }}
                <span v-if="required" class="field-required">*</span>
            </label>
            <span v-if="maxlength" class="char-count">
                {{ (modelValue || '').length }}/{{ maxlength }}
            </span>
        </div>
        <textarea
            :value="modelValue"
            :placeholder="placeholder"
            :rows="rows"
            :disabled="disabled"
            :required="required"
            :maxlength="maxlength"
            :class="['field-textarea', { 'has-error': !!error }]"
            @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
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

.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.char-count {
    font-size: 11px;
    color: var(--text-dim);
}

.field-textarea {
    width: 100%;
    padding: 10px 14px;
    font-size: 13px;
    font-family: var(--font-sans);
    color: var(--text);
    background-color: transparent;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-md);
    outline: none;
    resize: vertical;
    min-height: 80px;
    transition: border-color var(--transition-fast);
    box-shadow: none !important;
}

.field-textarea::placeholder {
    color: var(--text-dim);
}

.field-textarea:focus {
    border-color: var(--orange);
}

.field-textarea:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.field-textarea.has-error {
    border-color: var(--red);
}

.field-error {
    font-size: 11px;
    color: var(--red);
}
</style>
