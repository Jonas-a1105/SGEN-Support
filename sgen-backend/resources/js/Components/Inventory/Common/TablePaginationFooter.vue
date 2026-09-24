<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        showingCount: number;
        totalCount: number;
        itemLabel?: string;
        currentPage: number;
        totalPages: number;
        perPage?: number;
        perPageOptions?: number[];
    }>(),
    {
        itemLabel: 'items',
        perPage: 5,
        perPageOptions: () => [5, 10, 20, 50],
    }
);

const emit = defineEmits<{
    (e: 'change-page', page: number): void;
    (e: 'change-per-page', perPage: number): void;
}>();

const handlePerPageChange = (event: Event) => {
    const val = Number((event.target as HTMLSelectElement).value);
    emit('change-per-page', val);
};
</script>

<template>
    <div class="pagination-footer">
        <div class="pagination-info">
            Mostrando <strong>{{ showingCount }}</strong> de <strong>{{ totalCount }}</strong> {{ itemLabel }}
            <span class="pagination-per-page-label">Mostrar:</span>
            <select
                :value="perPage"
                class="pagination-select"
                @change="handlePerPageChange"
            >
                <option v-for="opt in perPageOptions" :key="opt" :value="opt">
                    {{ opt }}
                </option>
            </select>
        </div>
        <div class="pagination-controls">
            <button
                class="page-nav-btn"
                :disabled="currentPage <= 1"
                type="button"
                @click="emit('change-page', currentPage - 1)"
            >
                ‹ Anterior
            </button>
            <span class="page-current-indicator">
                Página <strong>{{ currentPage }}</strong> / <span>{{ totalPages || 1 }}</span>
            </span>
            <button
                class="page-nav-btn"
                :disabled="currentPage >= totalPages"
                type="button"
                @click="emit('change-page', currentPage + 1)"
            >
                Siguiente ›
            </button>
        </div>
    </div>
</template>

<style scoped>
.pagination-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-3) var(--space-4);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    background: var(--bg-card);
    font-size: 13px;
    color: var(--text-muted);
    flex-wrap: wrap;
    gap: var(--space-3);
}

.pagination-info {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.pagination-per-page-label {
    margin-left: var(--space-3);
    font-size: 12px;
}

.pagination-select {
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-sm);
    color: var(--text);
    padding: 2px var(--space-2);
    font-size: 12px;
    outline: none;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.page-nav-btn {
    padding: 4px var(--space-3);
    border-radius: var(--radius-sm);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    cursor: pointer;
    font-size: 12px;
    transition: all var(--transition-fast);
}

.page-nav-btn:hover:not(:disabled) {
    background: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.page-nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-current-indicator {
    font-size: 12px;
}
</style>
