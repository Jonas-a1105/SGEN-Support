<script setup lang="ts">
import type { MaintenanceFilterPill } from './types';

defineProps<{
    activeFilter: MaintenanceFilterPill;
    searchQuery: string;
}>();

const emit = defineEmits<{
    (e: 'update:activeFilter', value: MaintenanceFilterPill): void;
    (e: 'update:searchQuery', value: string): void;
}>();

const filterPills: { value: MaintenanceFilterPill; label: string }[] = [
    { value: 'todos', label: 'Todos' },
    { value: 'pendiente', label: 'Pendientes' },
    { value: 'en_proceso', label: 'En Proceso' },
    { value: 'completado', label: 'Completados' },
    { value: 'preventivo', label: 'Preventivos' },
    { value: 'correctivo', label: 'Correctivos' },
];
</script>

<template>
    <section class="toolbar-card">
        <div class="toolbar-left">
            <div class="filter-pills" role="tablist">
                <button
                    v-for="pill in filterPills"
                    :key="pill.value"
                    type="button"
                    class="filter-pill"
                    :class="{ active: activeFilter === pill.value }"
                    @click="emit('update:activeFilter', pill.value)"
                >
                    {{ pill.label }}
                </button>
            </div>
        </div>

        <div class="toolbar-right">
            <div class="search-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    :value="searchQuery"
                    type="text"
                    class="search-input"
                    placeholder="Buscar equipo, técnico, descripción..."
                    autocomplete="off"
                    @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>
    </section>
</template>

<style scoped>
.toolbar-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: none !important;
}

.toolbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-pills {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.filter-pill {
    height: 32px;
    padding: 0 14px;
    border-radius: 9px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
    box-shadow: none !important;
}

.filter-pill:hover {
    border-color: var(--stroke-hover);
    color: var(--text);
}

.filter-pill.active {
    background: var(--orange);
    color: #ffffff;
    border-color: var(--orange);
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-grow: 1;
    justify-content: flex-end;
}

.search-box {
    position: relative;
    max-width: 320px;
    width: 100%;
}

.search-box svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    stroke: var(--text-muted);
    fill: none;
    stroke-width: 2;
    pointer-events: none;
}

.search-input {
    width: 100%;
    height: 36px;
    padding: 0 12px 0 36px;
    border-radius: 10px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.search-input:focus {
    border-color: var(--orange);
}

@media (max-width: 768px) {
    .toolbar-card {
        flex-direction: column;
        align-items: stretch;
    }
    .toolbar-right {
        width: 100%;
        justify-content: stretch;
    }
    .search-box {
        max-width: 100%;
    }
}
</style>
