<script setup lang="ts">
interface Props {
    modelValue: string | null | undefined;
    label?: string;
    type?: 'date' | 'datetime-local' | 'month';
    placeholder?: string;
    min?: string;
    max?: string;
    required?: boolean;
    disabled?: boolean;
    clearable?: boolean;
    error?: string;
    helpText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: '',
    type: 'date',
    placeholder: '',
    min: '',
    max: '',
    required: false,
    disabled: false,
    clearable: true,
    error: '',
    helpText: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'change', event: Event): void;
}>();

const handleInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};

const handleClear = () => {
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="base-date-picker-group">
        <label v-if="label" class="field-label">
            {{ label }}
            <span v-if="required" class="field-required">*</span>
        </label>

        <div class="date-input-wrapper" :class="{ 'has-error': !!error, 'is-disabled': disabled }">
            <span class="date-icon-left" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </span>

            <input
                :value="modelValue ?? ''"
                :type="type"
                :placeholder="placeholder"
                :min="min"
                :max="max"
                :required="required"
                :disabled="disabled"
                class="date-input-control"
                @input="handleInput"
                @change="$emit('change', $event)"
            />

            <button
                v-if="clearable && modelValue && !disabled"
                type="button"
                class="date-clear-btn"
                title="Limpiar fecha"
                aria-label="Limpiar fecha"
                @click="handleClear"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <span v-if="error" class="field-error-msg">{{ error }}</span>
        <span v-else-if="helpText" class="field-help-text">{{ helpText }}</span>
    </div>
</template>

<style scoped>
.base-date-picker-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1, 4px);
    width: 100%;
}

.field-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.field-required {
    color: var(--red);
}

.date-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    border-radius: var(--radius-sm, 6px);
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    box-shadow: none !important;
    transition: border-color var(--transition-fast, 0.15s ease);
}

.date-input-wrapper:focus-within {
    border-color: var(--brand);
}

.date-input-wrapper.has-error {
    border-color: var(--red);
}

.date-input-wrapper.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: var(--bg-sub);
}

.date-icon-left {
    position: absolute;
    left: var(--space-3, 12px);
    display: flex;
    align-items: center;
    color: var(--text-dim);
    pointer-events: none;
}

.date-icon-left svg {
    width: 16px;
    height: 16px;
}

.date-input-control {
    width: 100%;
    height: 40px;
    padding: 0 var(--space-8, 36px) 0 var(--space-8, 36px);
    background: transparent;
    border: none;
    outline: none;
    color: var(--text);
    font-size: 13px;
    font-family: inherit;
    box-sizing: border-box;
}

/* Custom styling for date inputs */
.date-input-control::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.7;
    filter: invert(0.8);
}

.date-clear-btn {
    position: absolute;
    right: var(--space-2, 8px);
    background: transparent;
    border: none;
    color: var(--text-dim);
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-pill, 9999px);
    transition: color var(--transition-fast, 0.15s ease);
}

.date-clear-btn svg {
    width: 14px;
    height: 14px;
}

.date-clear-btn:hover {
    color: var(--text);
}

.field-error-msg {
    font-size: 11px;
    color: var(--red);
    font-weight: 500;
}

.field-help-text {
    font-size: 11px;
    color: var(--text-dim);
}
</style>
