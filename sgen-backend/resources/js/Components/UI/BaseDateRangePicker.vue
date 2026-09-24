<script setup lang="ts">
import { computed } from 'vue';

export interface DateRange {
    start: string;
    end: string;
}

interface Props {
    modelValue?: DateRange;
    startDate?: string;
    endDate?: string;
    label?: string;
    showPresets?: boolean;
    error?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: undefined,
    startDate: '',
    endDate: '',
    label: '',
    showPresets: true,
    error: '',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: DateRange): void;
    (e: 'update:startDate', val: string): void;
    (e: 'update:endDate', val: string): void;
    (e: 'change', value: DateRange): void;
}>();

const currentRange = computed<DateRange>(() => {
    if (props.modelValue) {
        return {
            start: props.modelValue.start || '',
            end: props.modelValue.end || '',
        };
    }
    return {
        start: props.startDate || '',
        end: props.endDate || '',
    };
});

const updateStart = (val: string) => {
    const currentEnd = currentRange.value.end;
    const newRange: DateRange = {
        start: val,
        end: currentEnd && currentEnd < val ? val : currentEnd,
    };
    emit('update:modelValue', newRange);
    emit('update:startDate', val);
    emit('update:endDate', newRange.end);
    emit('change', newRange);
};

const updateEnd = (val: string) => {
    const currentStart = currentRange.value.start;
    const newRange: DateRange = {
        start: currentStart && currentStart > val ? val : currentStart,
        end: val,
    };
    emit('update:modelValue', newRange);
    emit('update:startDate', newRange.start);
    emit('update:endDate', val);
    emit('change', newRange);
};

const formatIsoDate = (d: Date): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const applyPreset = (preset: 'today' | '7days' | 'thisMonth' | 'lastMonth' | 'thisYear') => {
    const now = new Date();
    let start = new Date();
    let end = new Date();

    switch (preset) {
        case 'today':
            start = now;
            end = now;
            break;
        case '7days':
            start = new Date(now.getTime() - 6 * 24 * 60 * 60 * 1000);
            end = now;
            break;
        case 'thisMonth':
            start = new Date(now.getFullYear(), now.getMonth(), 1);
            end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            break;
        case 'lastMonth':
            start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            end = new Date(now.getFullYear(), now.getMonth(), 0);
            break;
        case 'thisYear':
            start = new Date(now.getFullYear(), 0, 1);
            end = new Date(now.getFullYear(), 11, 31);
            break;
    }

    const range: DateRange = {
        start: formatIsoDate(start),
        end: formatIsoDate(end),
    };
    emit('update:modelValue', range);
    emit('update:startDate', range.start);
    emit('update:endDate', range.end);
    emit('change', range);
};

const clearRange = () => {
    const range: DateRange = { start: '', end: '' };
    emit('update:modelValue', range);
    emit('update:startDate', '');
    emit('update:endDate', '');
    emit('change', range);
};
</script>

<template>
    <div class="base-date-range-picker">
        <div v-if="label" class="range-header-row">
            <label class="field-label">{{ label }}</label>
            <button
                v-if="currentRange.start || currentRange.end"
                type="button"
                class="range-clear-link"
                @click="clearRange"
            >
                Limpiar rango
            </button>
        </div>

        <!-- Presets quick buttons -->
        <div v-if="showPresets && !disabled" class="range-presets-bar">
            <button type="button" class="preset-btn" @click="applyPreset('today')">Hoy</button>
            <button type="button" class="preset-btn" @click="applyPreset('7days')">Últimos 7d</button>
            <button type="button" class="preset-btn" @click="applyPreset('thisMonth')">Este mes</button>
            <button type="button" class="preset-btn" @click="applyPreset('lastMonth')">Mes anterior</button>
            <button type="button" class="preset-btn" @click="applyPreset('thisYear')">Este año</button>
        </div>

        <!-- Inputs Row -->
        <div class="range-inputs-row" :class="{ 'has-error': !!error, 'is-disabled': disabled }">
            <div class="range-field-box">
                <span class="range-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </span>
                <input
                    :value="currentRange.start"
                    type="date"
                    :disabled="disabled"
                    placeholder="Desde"
                    class="range-input-control"
                    aria-label="Fecha inicial"
                    @input="updateStart(($event.target as HTMLInputElement).value)"
                />
            </div>

            <span class="range-divider" aria-hidden="true">→</span>

            <div class="range-field-box">
                <span class="range-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </span>
                <input
                    :value="currentRange.end"
                    type="date"
                    :min="currentRange.start || undefined"
                    :disabled="disabled"
                    placeholder="Hasta"
                    class="range-input-control"
                    aria-label="Fecha final"
                    @input="updateEnd(($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <span v-if="error" class="field-error-msg">{{ error }}</span>
    </div>
</template>

<style scoped>
.base-date-range-picker {
    display: flex;
    flex-direction: column;
    gap: var(--space-2, 6px);
    width: 100%;
}

.range-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.field-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.range-clear-link {
    background: transparent;
    border: none;
    color: var(--brand);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
}

.range-clear-link:hover {
    text-decoration: underline;
}

.range-presets-bar {
    display: flex;
    gap: var(--space-1, 4px);
    flex-wrap: wrap;
}

.preset-btn {
    padding: 3px 8px;
    border-radius: var(--radius-sm, 4px);
    border: 1px solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast, 0.15s ease);
    box-shadow: none !important;
}

.preset-btn:hover {
    background: var(--stroke);
    color: var(--text);
}

.range-inputs-row {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
    width: 100%;
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-sm, 6px);
    padding: 2px var(--space-2, 8px);
    box-shadow: none !important;
    transition: border-color var(--transition-fast, 0.15s ease);
}

.range-inputs-row:focus-within {
    border-color: var(--brand);
}

.range-inputs-row.has-error {
    border-color: var(--red);
}

.range-inputs-row.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: var(--bg-sub);
}

.range-field-box {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
}

.range-field-icon {
    position: absolute;
    left: var(--space-2, 6px);
    display: flex;
    align-items: center;
    color: var(--text-dim);
    pointer-events: none;
}

.range-field-icon svg {
    width: 14px;
    height: 14px;
}

.range-input-control {
    width: 100%;
    height: 36px;
    padding: 0 var(--space-2, 6px) 0 28px;
    background: transparent;
    border: none;
    outline: none;
    color: var(--text);
    font-size: 12px;
    font-family: inherit;
    box-sizing: border-box;
}

.range-input-control::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.7;
    filter: invert(0.8);
}

.range-divider {
    color: var(--text-dim);
    font-size: 14px;
    user-select: none;
}

.field-error-msg {
    font-size: 11px;
    color: var(--red);
    font-weight: 500;
}
</style>
