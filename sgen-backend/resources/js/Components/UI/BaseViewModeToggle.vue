<script setup lang="ts">
interface Props {
    modelValue: 'grid' | 'table';
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:modelValue', mode: 'grid' | 'table'): void;
}>();
</script>

<template>
    <div class="view-mode-toggle-group" role="group" aria-label="Modo de visualización">
        <button
            class="view-mode-btn"
            :class="{ active: modelValue === 'grid' }"
            @click="emit('update:modelValue', 'grid')"
            type="button"
            title="Vista en tarjetas cuadrícula"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
        </button>
        <button
            class="view-mode-btn"
            :class="{ active: modelValue === 'table' }"
            @click="emit('update:modelValue', 'table')"
            type="button"
            title="Vista en lista / tabla"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
        </button>
    </div>
</template>

<style scoped>
.view-mode-toggle-group {
    display: flex;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    overflow: hidden;
    background: var(--bg-sub);
}

.view-mode-btn {
    background: var(--bg-sub);
    border: none;
    color: var(--text-muted);
    width: 32px;
    height: 32px;
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.view-mode-btn:hover {
    color: var(--text);
}

.view-mode-btn.active {
    background: var(--stroke);
    color: #ffffff;
}

.view-mode-btn svg {
    width: 16px;
    height: 16px;
}
</style>
