<script setup lang="ts">
import type { ReportScope } from './types';

defineProps<{
    modelValue: ReportScope;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: ReportScope): void;
}>();
</script>

<template>
    <div class="report-scope-tabs" role="tablist">
        <button
            type="button"
            class="scope-tab-btn"
            :class="{ active: modelValue === 'soportes' }"
            @click="emit('update:modelValue', 'soportes')"
        >
            Soportes
        </button>
        <button
            type="button"
            class="scope-tab-btn"
            :class="{ active: modelValue === 'inventario' }"
            @click="emit('update:modelValue', 'inventario')"
        >
            Inventario
        </button>
        <button
            type="button"
            class="scope-tab-btn"
            :class="{ active: modelValue === 'mantenimiento' }"
            @click="emit('update:modelValue', 'mantenimiento')"
        >
            Mantenimiento
        </button>
        <button
            type="button"
            class="scope-tab-btn"
            :class="{ active: modelValue === 'rendimiento' }"
            @click="emit('update:modelValue', 'rendimiento')"
        >
            Rendimiento
        </button>
    </div>
</template>

<style scoped>
.report-scope-tabs {
    display: inline-flex;
    align-items: center;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    border-radius: 12px;
    padding: 3px;
    gap: 3px;
    box-shadow: none !important;
}

.scope-tab-btn {
    height: 32px;
    padding: 0 14px;
    border-radius: 9px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
    white-space: nowrap;
    box-shadow: none !important;
}

.scope-tab-btn.active {
    background: var(--bg-card);
    color: var(--text);
}
</style>
