<script setup lang="ts">
import { BaseDataTable, type DataTableColumn } from '@/Components/UI';
import type { ReportHistoryItem } from './types';

defineProps<{
    items: ReportHistoryItem[];
    totalCount: number;
    searchQuery: string;
}>();

const emit = defineEmits<{
    (e: 'update:searchQuery', val: string): void;
}>();

const columns: DataTableColumn[] = [
    { key: 'reporte', label: 'REPORTE', sortable: true },
    { key: 'formato', label: 'FORMATO', width: '110px' },
    { key: 'fecha', label: 'FECHA', width: '160px', sortable: true },
    { key: 'usuario', label: 'USUARIO', width: '130px' },
    { key: 'filtros', label: 'FILTROS' },
    { key: 'acciones', label: 'ACCIONES', width: '100px', align: 'right' },
];
</script>

<template>
    <div class="history-table-card">
        <div class="history-toolbar-row">
            <h2 class="panel-title">Descargas Recientes</h2>

            <div class="search-history-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    :value="searchQuery"
                    type="text"
                    class="search-history-input"
                    placeholder="Buscar en historial..."
                    autocomplete="off"
                    @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <BaseDataTable
            :columns="columns"
            :items="items"
            empty-title="No se encontraron reportes en el historial"
            empty-subtitle="Prueba con otros términos de búsqueda."
        >
            <template #cell-reporte="{ item }">
                <div class="report-cell">
                    <div class="report-icon-thumb" :class="item.scope">
                        <svg viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                    </div>
                    <div>
                        <div class="report-item-title">{{ item.title }}</div>
                        <div class="report-item-sub">{{ item.scope }}</div>
                    </div>
                </div>
            </template>

            <template #cell-formato="{ item }">
                <span class="format-badge-pill" :class="item.format">
                    {{ item.format.toUpperCase() }}
                </span>
            </template>

            <template #cell-fecha="{ item }">
                <span>{{ item.date }}</span>
            </template>

            <template #cell-usuario="{ item }">
                <span class="user-badge-mini">@{{ item.user }}</span>
            </template>

            <template #cell-filtros="{ item }">
                <span class="filters-summary">{{ item.filters }}</span>
            </template>

            <template #cell-acciones="{ item }">
                <a
                    :href="item.url"
                    target="_blank"
                    class="table-action-icon-btn"
                    title="Descargar nuevamente"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                </a>
            </template>
        </BaseDataTable>
    </div>
</template>

<style scoped>
.history-table-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: none !important;
}

.history-toolbar-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.panel-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.search-history-box {
    position: relative;
    width: 280px;
}

.search-history-box svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: var(--text-muted);
    pointer-events: none;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.search-history-input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 36px;
    border-radius: 11px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}

.search-history-input:focus {
    border-color: var(--orange);
}

.report-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-icon-thumb {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: none !important;
}

.report-icon-thumb.soportes {
    background: rgba(99, 102, 241, 0.14);
    color: #6366f1;
}

.report-icon-thumb.inventario {
    background: rgba(16, 185, 129, 0.14);
    color: #10b981;
}

.report-icon-thumb.mantenimiento {
    background: rgba(245, 158, 11, 0.14);
    color: #f59e0b;
}

.report-icon-thumb.rendimiento {
    background: rgba(99, 102, 241, 0.14);
    color: #6366f1;
}

.report-icon-thumb svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.report-item-title {
    font-weight: 600;
    color: var(--text);
}

.report-item-sub {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: capitalize;
}

.format-badge-pill {
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    letter-spacing: 0.04em;
    box-shadow: none !important;
}

.format-badge-pill.pdf {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.format-badge-pill.excel {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.user-badge-mini {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    color: var(--text-muted);
    font-size: 11px;
    box-shadow: none !important;
}

.filters-summary {
    font-size: 12px;
    color: var(--text-muted);
}

.table-action-icon-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke-subtle);
    background: transparent;
    color: var(--text-muted);
    display: inline-grid;
    place-items: center;
    cursor: pointer;
    transition: all 0.18s ease;
    text-decoration: none;
    box-shadow: none !important;
}

.table-action-icon-btn:hover {
    color: var(--orange);
    border-color: var(--stroke);
    background: var(--bg-sub);
}

.table-action-icon-btn svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
