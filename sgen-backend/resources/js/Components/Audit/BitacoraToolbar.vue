<script setup lang="ts">
const search = defineModel<string>('search', { default: '' });
const selectedEntity = defineModel<string>('selectedEntity', { default: 'all' });

defineProps<{
    showingCount: number;
    totalCount: number;
}>();

const emit = defineEmits<{
    (e: 'export'): void;
}>();

const filterOptions = [
    { value: 'all', label: 'Todos los módulos' },
    { value: 'soporte', label: 'Soportes / Tickets' },
    { value: 'equipo', label: 'Equipos' },
    { value: 'usuario', label: 'Usuarios' },
    { value: 'departamento', label: 'Departamentos' },
    { value: 'inventario', label: 'Inventario' },
];
</script>

<template>
    <section class="toolbar-card" aria-label="Herramientas de bitácora">
        <div class="search-and-filters">
            <div class="search-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    class="search-input"
                    placeholder="Buscar por usuario, acción o referencia..."
                    autocomplete="off"
                />
            </div>

            <div class="entity-chips-row">
                <button
                    v-for="opt in filterOptions"
                    :key="opt.value"
                    type="button"
                    class="chip-btn"
                    :class="{ active: selectedEntity === opt.value }"
                    @click="selectedEntity = opt.value"
                >
                    {{ opt.label }}
                </button>
            </div>
        </div>

        <div class="toolbar-actions">
            <span class="count-badge">
                {{ showingCount }} de {{ totalCount }} eventos
            </span>
            <button class="btn-export-log" type="button" @click="emit('export')">
                <svg viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Exportar Bitácora</span>
            </button>
        </div>
    </section>
</template>

<style scoped>
.toolbar-card {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--card-radius, 14px);
    padding: 14px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: none !important;
}

.search-and-filters {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    flex: 1;
    min-width: 280px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    padding: 8px 14px;
    min-width: 260px;
    max-width: 380px;
    flex: 1;
    box-shadow: none !important;
}

.search-box svg {
    width: 16px;
    height: 16px;
    color: var(--text-muted, #8e9199);
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.search-input {
    background: transparent;
    border: none;
    outline: none;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    width: 100%;
}

.search-input::placeholder {
    color: var(--text-dim, #60636d);
}

.entity-chips-row {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.chip-btn {
    padding: 6px 12px;
    border-radius: 20px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-sub, #1e2024);
    color: var(--text-muted, #8e9199);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
    white-space: nowrap;
    box-shadow: none !important;
}

.chip-btn:hover {
    color: var(--text, #f4f4f6);
    border-color: var(--stroke-hover, #454952);
}

.chip-btn.active {
    background: var(--accent-soft, rgba(79, 70, 229, 0.12));
    border-color: var(--orange, #4f46e5);
    color: var(--orange, #4f46e5);
}

.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.count-badge {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted, #8e9199);
    white-space: nowrap;
}

.btn-export-log {
    height: 38px;
    border-radius: 10px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-sub, #1e2024);
    color: var(--text, #f4f4f6);
    padding: 0 16px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
    white-space: nowrap;
}

.btn-export-log:hover {
    background: var(--stroke-subtle, #23252a);
    border-color: var(--stroke-hover, #454952);
    color: #ffffff;
}

.btn-export-log svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

@media (max-width: 768px) {
    .toolbar-card {
        flex-direction: column;
        align-items: stretch;
    }
    .search-box {
        max-width: 100%;
    }
    .toolbar-actions {
        justify-content: space-between;
    }
}
</style>
