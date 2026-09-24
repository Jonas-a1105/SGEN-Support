<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    currentPage: number;
    lastPage: number;
    total?: number;
    from?: number;
    to?: number;
}

const props = withDefaults(defineProps<Props>(), {
    total: 0,
    from: 0,
    to: 0,
});

const emit = defineEmits<{
    (e: 'page-change', page: number): void;
}>();

const visiblePages = computed<(number | string)[]>(() => {
    const pages: (number | string)[] = [];
    const current = props.currentPage;
    const last = props.lastPage;

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
    if (typeof page === 'number' && page >= 1 && page <= props.lastPage && page !== props.currentPage) {
        emit('page-change', page);
    }
}
</script>

<template>
    <div v-if="lastPage > 1 || total > 0" class="base-pagination">
        <div class="pagination-info">
            <span v-if="total > 0">
                Mostrando <strong>{{ from }}-{{ to }}</strong> de <strong>{{ total }}</strong> registros
            </span>
        </div>

        <div v-if="lastPage > 1" class="pagination-controls">
            <!-- Botón Anterior -->
            <button
                type="button"
                class="page-btn nav-btn"
                :disabled="currentPage <= 1"
                title="Página anterior"
                @click="goToPage(currentPage - 1)"
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
                    :class="{ active: p === currentPage }"
                    @click="goToPage(p)"
                >
                    {{ p }}
                </button>
            </template>

            <!-- Botón Siguiente -->
            <button
                type="button"
                class="page-btn nav-btn"
                :disabled="currentPage >= lastPage"
                title="Página siguiente"
                @click="goToPage(currentPage + 1)"
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

.pagination-info {
    font-size: 12px;
    color: var(--text-muted);
}

.pagination-info strong {
    color: var(--text);
    font-weight: 700;
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
