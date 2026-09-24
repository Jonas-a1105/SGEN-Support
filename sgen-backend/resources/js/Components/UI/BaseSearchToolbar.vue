<script setup lang="ts">
interface Props {
    modelValue?: string;
    placeholder?: string;
    showClear?: boolean;
    searchPosition?: 'left' | 'right';
}

withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: 'Buscar...',
    showClear: true,
    searchPosition: 'left',
});

defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'search'): void;
}>();
</script>

<template>
    <div class="base-search-toolbar">
        <div class="toolbar-left-group">
            <!-- Slot Prepend (ej. switch de densidad o acciones a la izquierda) -->
            <slot name="prepend" />

            <!-- Buscador en posición izquierda -->
            <div v-if="searchPosition === 'left'" class="search-input-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    :value="modelValue"
                    type="text"
                    class="toolbar-search-input"
                    :placeholder="placeholder"
                    @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                    @keyup.enter="$emit('search')"
                >
                <button
                    v-if="showClear && modelValue"
                    type="button"
                    class="search-clear-btn"
                    title="Limpiar búsqueda"
                    @click="$emit('update:modelValue', '')"
                >
                    ✕
                </button>
            </div>

            <!-- Filtros adicionales inyectados vía slot -->
            <div v-if="$slots.filters" class="toolbar-filters">
                <slot name="filters" />
            </div>
        </div>

        <!-- Acciones a la derecha (vistas, exportar, toggles, o buscador a la derecha) -->
        <div class="toolbar-right-group">
            <!-- Buscador en posición derecha -->
            <div v-if="searchPosition === 'right'" class="search-input-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    :value="modelValue"
                    type="text"
                    class="toolbar-search-input"
                    :placeholder="placeholder"
                    @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                    @keyup.enter="$emit('search')"
                >
                <button
                    v-if="showClear && modelValue"
                    type="button"
                    class="search-clear-btn"
                    title="Limpiar búsqueda"
                    @click="$emit('update:modelValue', '')"
                >
                    ✕
                </button>
            </div>

            <slot name="actions" />
        </div>
    </div>
</template>

<style scoped>
.base-search-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    margin-bottom: 16px;
    box-shadow: none !important;
}

.toolbar-left-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
    flex-wrap: wrap;
}

.search-input-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 280px;
    max-width: 100%;
}

.search-icon {
    position: absolute;
    left: 11px;
    width: 15px;
    height: 15px;
    color: var(--text-muted);
    pointer-events: none;
}

.toolbar-search-input {
    width: 100%;
    height: 34px;
    padding: 0 30px 0 34px;
    font-size: 13px;
    font-family: inherit;
    color: var(--text);
    background-color: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 20px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.toolbar-search-input::placeholder {
    color: var(--text-muted);
}

.toolbar-search-input:focus {
    border-color: var(--brand, var(--blue, #2563eb));
}

.search-clear-btn {
    position: absolute;
    right: 8px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 11px;
    cursor: pointer;
    display: grid;
    place-items: center;
    padding: 0;
}

.search-clear-btn:hover {
    color: var(--text);
    background: var(--stroke);
}

.toolbar-filters {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.toolbar-right-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .base-search-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .search-input-box {
        width: 100%;
    }
    .toolbar-right-group {
        justify-content: flex-end;
    }
}
</style>
