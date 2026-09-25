<script setup lang="ts">
import ReportScopeTabs from './ReportScopeTabs.vue';
import ReportFilterForm from './ReportFilterForm.vue';
import ReportPreviewCard from './ReportPreviewCard.vue';
import type { ReportScope, TicketsFilters, Category } from './types';

defineProps<{
    scope: ReportScope;
    filters: TicketsFilters;
    categories: Category[];
    previewSummary: string;
}>();

const emit = defineEmits<{
    (e: 'update:scope', val: ReportScope): void;
    (e: 'update:filters', val: TicketsFilters): void;
    (e: 'download-pdf'): void;
    (e: 'export-csv'): void;
}>();
</script>

<template>
    <div class="generator-card">
        <div class="generator-head-row">
            <div class="generator-head-title">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                <h2 class="panel-title">Generador de Reportes</h2>
            </div>

            <ReportScopeTabs
                :model-value="scope"
                @update:model-value="emit('update:scope', $event)"
            />
        </div>

        <div class="generator-body-grid">
            <div class="generator-inputs-col">
                <ReportFilterForm
                    :scope="scope"
                    :filters="filters"
                    :categories="categories"
                    @update:filters="emit('update:filters', $event)"
                />

                <ReportPreviewCard :summary-text="previewSummary" />
            </div>

            <div class="generator-actions-col">
                <button class="btn-export-solid" type="button" @click="emit('download-pdf')">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <span>Descargar PDF</span>
                </button>

                <button
                    v-if="['soportes', 'inventario', 'mantenimiento'].includes(scope)"
                    class="btn-export-outline"
                    type="button"
                    @click="emit('export-csv')"
                >
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <line x1="3" y1="9" x2="21" y2="9" />
                        <line x1="3" y1="15" x2="21" y2="15" />
                        <line x1="9" y1="3" x2="9" y2="21" />
                        <line x1="15" y1="3" x2="15" y2="21" />
                    </svg>
                    <span>Exportar CSV / Excel</span>
                </button>

                <p class="export-caption-note">
                    El reporte incluirá gráficos y tablas detalladas listas para auditoría oficial.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.generator-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: none !important;
}

.generator-head-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.generator-head-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.generator-head-title svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    color: var(--orange);
}

.panel-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.generator-body-grid {
    display: grid;
    grid-template-columns: 1.45fr 0.85fr;
    gap: 24px;
    align-items: stretch;
}

.generator-inputs-col {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.generator-actions-col {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 12px;
    border-left: var(--stroke-w) solid var(--stroke-subtle);
    padding-left: 24px;
}

.btn-export-solid {
    height: 44px;
    border-radius: 12px;
    border: none;
    background: var(--orange);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: filter 0.2s ease;
    box-shadow: none !important;
}

.btn-export-solid:hover {
    filter: brightness(1.08);
}

.btn-export-solid svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}

.btn-export-outline {
    height: 44px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.btn-export-outline:hover {
    border-color: var(--stroke-hover);
    background: var(--stroke-subtle);
}

.btn-export-outline svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.export-caption-note {
    font-size: 11px;
    color: var(--text-dim);
    text-align: center;
    margin: 4px 0 0;
    line-height: 1.4;
}

@media (max-width: 980px) {
    .generator-body-grid {
        grid-template-columns: 1fr;
    }
    .generator-actions-col {
        border-left: none;
        border-top: var(--stroke-w) solid var(--stroke-subtle);
        padding-left: 0;
        padding-top: 18px;
    }
}
</style>
