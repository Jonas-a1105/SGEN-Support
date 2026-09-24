<script setup lang="ts">
import type { ReportHistoryItem } from './types';

defineProps<{
    items: ReportHistoryItem[];
    totalCount: number;
    searchQuery: string;
}>();

const emit = defineEmits<{
    (e: 'update:searchQuery', val: string): void;
}>();
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
                    placeholder="Buscar..."
                    autocomplete="off"
                    @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>REPORTE</th>
                        <th>FORMATO</th>
                        <th>FECHA</th>
                        <th>USUARIO</th>
                        <th>FILTROS</th>
                        <th class="text-right">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>
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
                        </td>
                        <td>
                            <span class="format-badge-pill" :class="item.format">
                                {{ item.format.toUpperCase() }}
                            </span>
                        </td>
                        <td>{{ item.date }}</td>
                        <td>
                            <span class="user-badge-mini">@{{ item.user }}</span>
                        </td>
                        <td>
                            <span class="filters-summary">{{ item.filters }}</span>
                        </td>
                        <td class="text-right">
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
                        </td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td colspan="6" class="empty-table-cell">
                            No se encontraron reportes en el historial.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="pagination-footer">
            <div>
                Mostrando <strong>{{ items.length }}</strong> de
                <strong>{{ totalCount }}</strong> registros
            </div>
            <div class="pagination-controls-row">
                <button type="button" class="page-step-btn" disabled>Anterior</button>
                <button type="button" class="page-step-btn" disabled>Siguiente</button>
            </div>
        </div>
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

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.custom-table th {
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-dim);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    background: var(--bg-card);
}

.custom-table td {
    padding: 14px 16px;
    font-size: 13px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.custom-table tr:hover td {
    background: var(--stroke-subtle);
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

.text-right {
    text-align: right;
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

.empty-table-cell {
    text-align: center;
    padding: 32px;
    color: var(--text-muted);
}

.pagination-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    font-size: 12px;
    color: var(--text-muted);
    flex-wrap: wrap;
    gap: 12px;
}

.pagination-controls-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-step-btn {
    padding: 5px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 12px;
    cursor: pointer;
    box-shadow: none !important;
}

.page-step-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
