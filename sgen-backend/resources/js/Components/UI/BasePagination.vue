<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    currentPage?: number;
    lastPage?: number;
    totalPages?: number;
    total?: number;
    totalItems?: number;
    totalCount?: number;
    from?: number;
    to?: number;
    perPage?: number;
    perPageOptions?: number[];
    itemLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    currentPage: 1,
    lastPage: 1,
    totalPages: 0,
    total: 0,
    totalItems: 0,
    totalCount: 0,
    from: 0,
    to: 0,
    perPage: 10,
    perPageOptions: () => [],
    itemLabel: 'registros',
});

const emit = defineEmits<{
    (e: 'page-change', page: number): void;
    (e: 'change-page', page: number): void;
    (e: 'per-page-change', perPage: number): void;
    (e: 'change-per-page', perPage: number): void;
}>();

const effectiveCurrentPage = computed(() => props.currentPage || 1);
const effectiveLastPage = computed(() => Math.max(1, props.lastPage || props.totalPages || 1));
const effectiveTotal = computed(() => props.total || props.totalItems || props.totalCount || 0);
const effectivePerPage = computed(() => props.perPage || 10);

const effectiveFrom = computed(() => {
    if (props.from > 0) return props.from;
    if (effectiveTotal.value === 0) return 0;
    return (effectiveCurrentPage.value - 1) * effectivePerPage.value + 1;
});

const effectiveTo = computed(() => {
    if (props.to > 0) return props.to;
    if (effectiveTotal.value === 0) return 0;
    return Math.min(effectiveCurrentPage.value * effectivePerPage.value, effectiveTotal.value);
});

const effectiveLabel = computed(() => props.itemLabel || 'registros');

const visiblePages = computed<(number | string)[]>(() => {
    const pages: (number | string)[] = [];
    const current = effectiveCurrentPage.value;
    const last = effectiveLastPage.value;

    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    pages.push(1);

    if (current > 3) {
        pages.push('...');
    }

    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (current < last - 2) {
        pages.push('...');
    }

    pages.push(last);

    return pages;
});

function goToPage(page: number | string) {
    if (typeof page === 'number' && page >= 1 && page <= effectiveLastPage.value && page !== effectiveCurrentPage.value) {
        emit('page-change', page);
        emit('change-page', page);
    }
}

function handlePerPageChange(pp: number) {
    if (pp !== effectivePerPage.value) {
        emit('per-page-change', pp);
        emit('change-per-page', pp);
    }
}
</script>

<template>
    <div v-if="effectiveLastPage > 1 || effectiveTotal > 0" class="base-pagination">
        <div class="pagination-info-group">
            <span v-if="effectiveTotal > 0" class="pagination-info">
                Mostrando <strong>{{ effectiveFrom }}-{{ effectiveTo }}</strong> de <strong>{{ effectiveTotal }}</strong> {{ effectiveLabel }}
            </span>

            <div v-if="perPageOptions && perPageOptions.length > 0" class="per-page-control">
                <span class="per-page-label">Filas:</span>
                <div class="per-page-pills">
                    <button
                        v-for="opt in perPageOptions"
                        :key="opt"
                        type="button"
                        class="per-page-pill"
                        :class="{ active: effectivePerPage === opt }"
                        @click="handlePerPageChange(opt)"
                    >
                        {{ opt }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="effectiveLastPage > 1" class="pagination-controls">
            <!-- Botón Anterior -->
            <button
                type="button"
                class="page-btn nav-btn"
                :disabled="effectiveCurrentPage <= 1"
                title="Página anterior"
                @click="goToPage(effectiveCurrentPage - 1)"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            <!-- Páginas numéricas -->
            <template v-for="(p, idx) in visiblePages" :key="idx">
                <span v-if="p === '...'" class="page-ellipsis">...</span>
                <button
                    v-else
                    type="button"
                    class="page-btn num-btn"
                    :class="{ active: p === effectiveCurrentPage }"
                    @click="goToPage(p)"
                >
                    {{ p }}
                </button>
            </template>

            <!-- Botón Siguiente -->
            <button
                type="button"
                class="page-btn nav-btn"
                :disabled="effectiveCurrentPage >= effectiveLastPage"
                title="Página siguiente"
                @click="goToPage(effectiveCurrentPage + 1)"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.base-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 16px;
    background: var(--bg-card);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: 0 0 var(--panel-radius) var(--panel-radius);
    flex-wrap: wrap;
}

.pagination-info-group {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.pagination-info {
    font-size: 12px;
    color: var(--text-muted);
}

.pagination-info strong {
    color: var(--text);
    font-weight: 700;
}

.per-page-control {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
}

.per-page-label {
    color: var(--text-muted);
    font-weight: 600;
}

.per-page-pills {
    display: flex;
    gap: 3px;
}

.per-page-pill {
    padding: 2px 7px;
    border-radius: var(--radius-sm, 6px);
    border: var(--stroke-w, 1px) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: none !important;
    transition: all var(--transition-fast);
}

.per-page-pill:hover {
    background: var(--stroke-subtle);
    color: var(--text);
}

.per-page-pill.active {
    background: var(--brand, var(--orange));
    border-color: var(--brand, var(--orange));
    color: #ffffff;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 4px;
}

.page-btn {
    height: 32px;
    min-width: 32px;
    padding: 0 8px;
    border-radius: var(--radius-sm);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
    box-shadow: none !important;
}

.page-btn:hover:not(:disabled) {
    background: var(--stroke-subtle);
    color: var(--text);
    border-color: var(--stroke-hover);
}

.page-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.page-btn.active {
    background: var(--orange);
    border-color: var(--orange);
    color: #ffffff;
    font-weight: 700;
}

.nav-btn svg {
    width: 14px;
    height: 14px;
}

.page-ellipsis {
    padding: 0 4px;
    font-size: 12px;
    color: var(--text-dim);
}

@media (max-width: 640px) {
    .base-pagination {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
}
</style>
