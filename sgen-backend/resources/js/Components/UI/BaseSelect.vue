<script setup lang="ts">
import { computed } from 'vue';

export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue: string | number | null | undefined;
    label?: string;
    options: (SelectOption | string | number)[];
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    required?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    label: '',
    placeholder: '',
    error: '',
    disabled: false,
    required: false,
    size: 'md',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
    (e: 'change', event: Event): void;
}>();

const normalizedOptions = computed<SelectOption[]>(() => {
    return props.options.map(opt => {
        if (typeof opt === 'object' && opt !== null) {
            return opt as SelectOption;
        }
        return {
            value: opt,
            label: String(opt),
        };
    });
});

const handleChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    emit('update:modelValue', target.value);
    emit('change', e);
};
</script>

<template>
    <div class="field-group">
        <label v-if="label" class="field-label">
            {{ label }}
            <span v-if="required" class="field-required">*</span>
        </label>
        <div class="select-wrapper">
            <select
                :value="modelValue ?? ''"
                :disabled="disabled"
                :required="required"
                :class="['field-select', `size-${size}`, { 'has-error': !!error }]"
                @change="handleChange"
            >
                <option v-if="placeholder" value="" disabled :selected="!modelValue">
                    {{ placeholder }}
                </option>
                <option
                    v-for="opt in normalizedOptions"
                    :key="String(opt.value)"
                    :value="opt.value"
                    :disabled="opt.disabled"
                >
                    {{ opt.label }}
                </option>
            </select>
            <div class="select-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
        </div>
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

.select-wrapper {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
}

.field-select {
    width: 100%;
    padding: 9px 36px 9px 14px;
    font-size: 13px;
    font-family: var(--font-sans);
    color: var(--text);
    background-color: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-md);
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    transition: border-color var(--transition-fast);
    box-shadow: none !important;
}

.field-select.size-sm {
    padding: 6px 30px 6px 10px;
    font-size: 12px;
}

.field-select.size-lg {
    padding: 12px 40px 12px 16px;
    font-size: 14px;
}

.field-select:focus {
    border-color: var(--orange);
}

.field-select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.field-select.has-error {
    border-color: var(--red);
}

.select-arrow {
    position: absolute;
    right: 12px;
    pointer-events: none;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
}

.select-arrow svg {
    width: 14px;
    height: 14px;
}

.field-error {
    font-size: 11px;
    color: var(--red);
}
</style>
