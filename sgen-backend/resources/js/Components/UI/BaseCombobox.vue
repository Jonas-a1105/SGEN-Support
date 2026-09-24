<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useClickOutside } from '@/Composables/useClickOutside';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import type { BadgeVariant } from '@/Utils/badgeVariants';

export interface ComboboxOption {
    value: string | number;
    label: string;
    sublabel?: string;
    badge?: string;
    badgeVariant?: BadgeVariant;
    icon?: string;
    disabled?: boolean;
}

interface Props {
    modelValue: string | number | null | undefined;
    options: (ComboboxOption | string | number)[];
    label?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    searchable?: boolean;
    clearable?: boolean;
    disabled?: boolean;
    required?: boolean;
    error?: string;
    helpText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: '',
    placeholder: 'Seleccione una opción...',
    searchPlaceholder: 'Buscar...',
    searchable: true,
    clearable: false,
    disabled: false,
    required: false,
    error: '',
    helpText: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number | null): void;
    (e: 'change', option: ComboboxOption | null): void;
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const comboboxRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const focusedIndex = ref(-1);

const normalizedOptions = computed<ComboboxOption[]>(() => {
    return props.options.map(opt => {
        if (typeof opt === 'object' && opt !== null) {
            return opt as ComboboxOption;
        }
        return {
            value: opt,
            label: String(opt),
        };
    });
});

const filteredOptions = computed<ComboboxOption[]>(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return normalizedOptions.value;
    return normalizedOptions.value.filter(opt =>
        opt.label.toLowerCase().includes(q) ||
        (opt.sublabel && opt.sublabel.toLowerCase().includes(q)) ||
        (opt.badge && opt.badge.toLowerCase().includes(q))
    );
});

const selectedOption = computed<ComboboxOption | undefined>(() => {
    return normalizedOptions.value.find(opt => opt.value === props.modelValue);
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        focusedIndex.value = -1;
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
};

const closeDropdown = () => {
    isOpen.value = false;
    searchQuery.value = '';
    focusedIndex.value = -1;
};

const selectOption = (opt: ComboboxOption) => {
    if (opt.disabled) return;
    emit('update:modelValue', opt.value);
    emit('change', opt);
    closeDropdown();
};

const handleClear = (e: Event) => {
    e.stopPropagation();
    emit('update:modelValue', null);
    emit('change', null);
};

useClickOutside(comboboxRef, () => {
    if (isOpen.value) {
        closeDropdown();
    }
});

const handleKeyDown = (e: KeyboardEvent) => {
    if (!isOpen.value) {
        if (e.key === 'Enter' || e.key === 'ArrowDown' || e.key === ' ') {
            e.preventDefault();
            toggleDropdown();
        }
        return;
    }

    if (e.key === 'Escape') {
        e.preventDefault();
        closeDropdown();
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (focusedIndex.value < filteredOptions.value.length - 1) {
            focusedIndex.value++;
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (focusedIndex.value > 0) {
            focusedIndex.value--;
        }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (focusedIndex.value >= 0 && focusedIndex.value < filteredOptions.value.length) {
            selectOption(filteredOptions.value[focusedIndex.value]);
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div ref="comboboxRef" class="base-combobox-wrapper">
        <label v-if="label" class="field-label">
            {{ label }}
            <span v-if="required" class="field-required">*</span>
        </label>

        <!-- Trigger Button -->
        <div
            class="combobox-trigger"
            :class="{
                'is-open': isOpen,
                'has-error': !!error,
                'is-disabled': disabled,
            }"
            tabindex="0"
            role="combobox"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
            @click="toggleDropdown"
        >
            <div class="trigger-selection">
                <template v-if="selectedOption">
                    <span v-if="selectedOption.icon" class="selection-icon">
                        <i :class="selectedOption.icon" />
                    </span>
                    <span class="selection-label">{{ selectedOption.label }}</span>
                    <BaseBadge
                        v-if="selectedOption.badge"
                        :variant="selectedOption.badgeVariant || 'neutral'"
                        size="sm"
                    >
                        {{ selectedOption.badge }}
                    </BaseBadge>
                    <span v-if="selectedOption.sublabel" class="selection-sublabel">
                        ({{ selectedOption.sublabel }})
                    </span>
                </template>
                <span v-else class="trigger-placeholder">{{ placeholder }}</span>
            </div>

            <div class="trigger-actions">
                <button
                    v-if="clearable && modelValue !== null && modelValue !== undefined && !disabled"
                    type="button"
                    class="btn-clear"
                    aria-label="Limpiar selección"
                    @click="handleClear"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <div class="trigger-arrow" :class="{ 'arrow-up': isOpen }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Floating Popover Window -->
        <Transition name="combobox-pop">
            <div
                v-if="isOpen"
                class="combobox-popover"
                role="listbox"
            >
                <!-- Search bar in popover -->
                <div v-if="searchable" class="popover-search-wrap">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        class="popover-search-input"
                        :placeholder="searchPlaceholder"
                        @click.stop
                    />
                </div>

                <!-- Options List -->
                <div class="popover-options-list">
                    <template v-if="filteredOptions.length > 0">
                        <div
                            v-for="(opt, idx) in filteredOptions"
                            :key="String(opt.value)"
                            class="combobox-option-row"
                            :class="{
                                'is-selected': opt.value === modelValue,
                                'is-focused': idx === focusedIndex,
                                'is-disabled': opt.disabled,
                            }"
                            role="option"
                            :aria-selected="opt.value === modelValue"
                            @click="selectOption(opt)"
                            @mouseenter="focusedIndex = idx"
                        >
                            <div class="option-content-left">
                                <span v-if="opt.icon" class="opt-icon">
                                    <i :class="opt.icon" />
                                </span>
                                <div class="opt-text-block">
                                    <span class="opt-label">{{ opt.label }}</span>
                                    <span v-if="opt.sublabel" class="opt-sublabel">{{ opt.sublabel }}</span>
                                </div>
                            </div>

                            <div class="option-content-right">
                                <BaseBadge
                                    v-if="opt.badge"
                                    :variant="opt.badgeVariant || 'neutral'"
                                    size="sm"
                                >
                                    {{ opt.badge }}
                                </BaseBadge>

                                <!-- Checkmark for selected item -->
                                <svg
                                    v-if="opt.value === modelValue"
                                    class="opt-check-icon"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                        </div>
                    </template>

                    <div v-else class="popover-empty-notice">
                        No se encontraron opciones para "{{ searchQuery }}"
                    </div>
                </div>
            </div>
        </Transition>

        <span v-if="error" class="field-error-msg">{{ error }}</span>
        <span v-else-if="helpText" class="field-help-text">{{ helpText }}</span>
    </div>
</template>

<style scoped>
.base-combobox-wrapper {
    position: relative;
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

.combobox-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 40px;
    padding: 0 var(--space-3, 12px);
    border-radius: var(--radius-sm, 6px);
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    cursor: pointer;
    box-shadow: none !important;
    user-select: none;
    transition: border-color var(--transition-fast, 0.15s ease);
}

.combobox-trigger:hover:not(.is-disabled) {
    border-color: var(--stroke-hover);
}

.combobox-trigger.is-open {
    border-color: var(--brand);
}

.combobox-trigger.has-error {
    border-color: var(--red);
}

.combobox-trigger.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: var(--bg-sub);
}

.trigger-selection {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.selection-icon {
    color: var(--brand);
}

.selection-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.selection-sublabel {
    font-size: 12px;
    color: var(--text-dim);
}

.trigger-placeholder {
    font-size: 13px;
    color: var(--text-dim);
}

.trigger-actions {
    display: flex;
    align-items: center;
    gap: var(--space-2, 6px);
}

.btn-clear {
    background: transparent;
    border: none;
    color: var(--text-dim);
    cursor: pointer;
    padding: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-pill, 9999px);
    transition: color var(--transition-fast, 0.15s ease);
}

.btn-clear svg {
    width: 13px;
    height: 13px;
}

.btn-clear:hover {
    color: var(--text);
}

.trigger-arrow {
    display: flex;
    align-items: center;
    color: var(--text-dim);
    transition: transform var(--transition-fast, 0.15s ease);
}

.trigger-arrow svg {
    width: 16px;
    height: 16px;
}

.trigger-arrow.arrow-up {
    transform: rotate(180deg);
}

/* Floating Popover */
.combobox-popover {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 1050;
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-md, 8px);
    box-shadow: none !important;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.popover-search-wrap {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
    padding: var(--space-2, 8px) var(--space-3, 12px);
    border-bottom: 1px solid var(--stroke-subtle);
    background: var(--bg-sub);
}

.search-icon {
    width: 14px;
    height: 14px;
    color: var(--text-dim);
    flex-shrink: 0;
}

.popover-search-input {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    color: var(--text);
    font-size: 12px;
    font-family: inherit;
}

.popover-options-list {
    max-height: 220px;
    overflow-y: auto;
    padding: var(--space-1, 4px);
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.combobox-option-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-3, 12px);
    padding: 8px 10px;
    border-radius: var(--radius-sm, 6px);
    cursor: pointer;
    transition: background var(--transition-fast, 0.15s ease);
}

.combobox-option-row:hover,
.combobox-option-row.is-focused {
    background: var(--stroke-subtle);
}

.combobox-option-row.is-selected {
    background: rgba(var(--brand-rgb, 59, 130, 246), 0.12);
}

.combobox-option-row.is-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.option-content-left {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
}

.opt-text-block {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.opt-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.opt-sublabel {
    font-size: 11px;
    color: var(--text-dim);
}

.option-content-right {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
}

.opt-check-icon {
    width: 15px;
    height: 15px;
    color: var(--brand);
}

.popover-empty-notice {
    padding: var(--space-4, 16px);
    text-align: center;
    font-size: 12px;
    color: var(--text-dim);
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

/* Transitions */
.combobox-pop-enter-active,
.combobox-pop-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.combobox-pop-enter-from {
    opacity: 0;
    transform: translateY(-4px);
}

.combobox-pop-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
