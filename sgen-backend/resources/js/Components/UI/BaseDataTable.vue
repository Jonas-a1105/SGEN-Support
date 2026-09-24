<script setup lang="ts" generic="T extends Record<string, any>">
import { ref } from 'vue';
import BasePagination from '@/Components/UI/BasePagination.vue';
import BaseEmptyState from '@/Components/UI/BaseEmptyState.vue';

export interface DataTableColumn {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    width?: string;
    headerClass?: string;
    cellClass?: string;
}

export interface DataTablePagination {
    currentPage: number;
    lastPage: number;
    total?: number;
    from?: number;
    to?: number;
}

interface Props {
    columns: DataTableColumn[];
    items: T[];
    pagination?: DataTablePagination | null;
    rowKey?: string | ((item: T, index: number) => string | number);
    emptyTitle?: string;
    emptyDescription?: string;
    emptyIcon?: string;
    hoverable?: boolean;
    striped?: boolean;
    rowClass?: string | ((item: T, index: number) => string);
}

const props = withDefaults(defineProps<Props>(), {
    pagination: null,
    rowKey: 'id',
    emptyTitle: 'No se encontraron registros',
    emptyDescription: 'No hay datos disponibles para mostrar en este momento.',
    emptyIcon: 'fa-solid fa-inbox',
    hoverable: true,
    striped: false,
});

const emit = defineEmits<{
    (e: 'sort', payload: { key: string; direction: 'asc' | 'desc' }): void;
    (e: 'page-change', page: number): void;
    (e: 'row-click', item: T, index: number): void;
}>();

const sortKey = ref<string>('');
const sortDirection = ref<'asc' | 'desc'>('asc');

const handleSort = (column: DataTableColumn) => {
    if (!column.sortable) return;

    if (sortKey.value === column.key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortDirection.value = 'asc';
    }

    emit('sort', { key: sortKey.value, direction: sortDirection.value });
};

const getItemKey = (item: T, index: number): string | number => {
    if (typeof props.rowKey === 'function') {
        return props.rowKey(item, index);
    }
    return (item as Record<string, any>)[props.rowKey] ?? index;
};

const getAlignmentClass = (align?: 'left' | 'center' | 'right'): string => {
    switch (align) {
        case 'center': return 'text-center';
        case 'right': return 'text-right';
        case 'left':
        default:
            return 'text-left';
    }
};
</script>

<template>
    <div class="base-data-table-wrapper">
        <div class="table-container">
            <table class="data-table" :class="{ 'is-hoverable': hoverable, 'is-striped': striped }">
                <thead>
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                getAlignmentClass(col.align),
                                col.headerClass,
                                { 'is-sortable': col.sortable }
                            ]"
                            @click="handleSort(col)"
                        >
                            <div class="th-content-row" :class="getAlignmentClass(col.align)">
                                <slot :name="`header-${col.key}`" :column="col">
                                    <span>{{ col.label }}</span>
                                </slot>

                                <span v-if="col.sortable" class="sort-icon-box">
                                    <svg
                                        v-if="sortKey === col.key && sortDirection === 'asc'"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="sort-svg active"
                                    >
                                        <polyline points="18 15 12 9 6 15" />
                                    </svg>
                                    <svg
                                        v-else-if="sortKey === col.key && sortDirection === 'desc'"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="sort-svg active"
                                    >
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                    <svg
                                        v-else
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="sort-svg neutral"
                                    >
                                        <polyline points="7 15 12 20 17 15" />
                                        <polyline points="7 9 12 4 17 9" />
                                    </svg>
                                </span>
                            </div>
                        </th>

                        <!-- Optional Actions Header Column -->
                        <th v-if="$slots.actions" class="text-right actions-column-header">
                            <span>ACCIONES</span>
                        </th>
                    </tr>
                </thead>

                <tbody v-if="items.length > 0">
                    <tr
                        v-for="(item, index) in items"
                        :key="getItemKey(item, index)"
                        class="data-table-row"
                        :class="typeof rowClass === 'function' ? rowClass(item, index) : rowClass"
                        @click="emit('row-click', item, index)"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[getAlignmentClass(col.align), col.cellClass]"
                        >
                            <slot
                                :name="`cell-${col.key}`"
                                :item="item"
                                :value="(item as Record<string, any>)[col.key]"
                                :index="index"
                            >
                                {{ (item as Record<string, any>)[col.key] ?? '-' }}
                            </slot>
                        </td>

                        <!-- Optional Actions Cell -->
                        <td v-if="$slots.actions" class="text-right actions-column-cell" @click.stop>
                            <slot name="actions" :item="item" :index="index" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State inside table container -->
            <div v-if="items.length === 0" class="table-empty-box">
                <slot name="empty">
                    <BaseEmptyState
                        :title="emptyTitle"
                        :description="emptyDescription"
                        :icon="emptyIcon"
                    />
                </slot>
            </div>
        </div>

        <!-- Integrated BasePagination -->
        <div v-if="pagination" class="table-pagination-footer">
            <BasePagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :total="pagination.total"
                :from="pagination.from"
                :to="pagination.to"
                @page-change="emit('page-change', $event)"
            />
        </div>
    </div>
</template>

<style scoped>
.base-data-table-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-md, 10px);
    overflow: hidden;
    box-shadow: none !important;
}

.table-container {
    width: 100%;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.data-table th {
    background: var(--bg-sub);
    padding: var(--space-3, 12px) var(--space-4, 16px);
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--stroke);
    user-select: none;
    white-space: nowrap;
}

.data-table th.is-sortable {
    cursor: pointer;
    transition: color var(--transition-fast, 0.15s ease);
}

.data-table th.is-sortable:hover {
    color: var(--text);
}

.th-content-row {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1, 4px);
}

.sort-icon-box {
    display: inline-flex;
    align-items: center;
}

.sort-svg {
    width: 13px;
    height: 13px;
}

.sort-svg.active {
    color: var(--brand);
}

.sort-svg.neutral {
    color: var(--text-dim);
    opacity: 0.6;
}

.data-table td {
    padding: var(--space-3, 12px) var(--space-4, 16px);
    border-bottom: 1px solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.data-table.is-hoverable tbody tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.data-table.is-striped tbody tr:nth-child(even) td {
    background: var(--bg-sub);
}

.data-table-row {
    transition: background var(--transition-fast, 0.15s ease);
}

.actions-column-header,
.actions-column-cell {
    white-space: nowrap;
}

.text-left {
    text-align: left;
    justify-content: flex-start;
}

.text-center {
    text-align: center;
    justify-content: center;
}

.text-right {
    text-align: right;
    justify-content: flex-end;
}

.table-empty-box {
    padding: var(--space-8, 32px) var(--space-4, 16px);
}

.table-pagination-footer {
    padding: var(--space-3, 12px) var(--space-4, 16px);
    border-top: 1px solid var(--stroke-subtle);
    background: var(--bg-sub);
}
</style>
