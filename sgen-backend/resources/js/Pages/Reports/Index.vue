<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton } from '@/Components/UI';
import type {
    Category,
    Department,
    ViewMode,
    ReportScope,
    ReportHistoryItem,
    TicketsFilters,
    HistoryFilterFormat,
} from '@/Components/Reports/types';
import ReportScopeTabs from '@/Components/Reports/ReportScopeTabs.vue';
import ReportFilterForm from '@/Components/Reports/ReportFilterForm.vue';
import ReportPreviewCard from '@/Components/Reports/ReportPreviewCard.vue';
import PredefinedReportsGrid from '@/Components/Reports/PredefinedReportsGrid.vue';
import ReportHistoryKpis from '@/Components/Reports/ReportHistoryKpis.vue';
import ReportHistoryTable from '@/Components/Reports/ReportHistoryTable.vue';

const props = defineProps<{
    categories: Category[];
    departments: Department[];
}>();

const activeView = ref<ViewMode>('generator');
const activeScope = ref<ReportScope>('soportes');

const ticketsFilters = ref<TicketsFilters>({
    fecha_inicio: '',
    fecha_fin: '',
    estado: '',
    categoria_id: '',
    prioridad: '',
});

const reportHistory = ref<ReportHistoryItem[]>([
    {
        id: 1,
        title: 'Reporte General de Tickets',
        scope: 'soportes',
        date: 'Hoy, 09:30 AM',
        format: 'pdf',
        user: 'admin',
        filters: 'Todos los estados',
        url: '/reportes/tickets/pdf',
    },
    {
        id: 2,
        title: 'Valoración de Inventario',
        scope: 'inventario',
        date: 'Ayer, 04:15 PM',
        format: 'pdf',
        user: 'admin',
        filters: 'Almacén Central',
        url: '/reportes/inventario/pdf',
    },
    {
        id: 3,
        title: 'Mantenimientos del Mes',
        scope: 'mantenimiento',
        date: '20 Sep 2026',
        format: 'pdf',
        user: 'admin',
        filters: 'Preventivos y correctivos',
        url: '/reportes/mantenimientos/pdf',
    },
    {
        id: 4,
        title: 'Exportación Tickets Excel',
        scope: 'soportes',
        date: '18 Sep 2026',
        format: 'excel',
        user: 'admin',
        filters: 'Resueltos',
        url: '/reportes/tickets/excel',
    },
]);

const searchHistory = ref('');
const historyFilterFormat = ref<HistoryFilterFormat>('all');

const filteredHistory = computed(() => {
    let list = reportHistory.value;
    const q = searchHistory.value.trim().toLowerCase();

    if (historyFilterFormat.value === 'pdf') {
        list = list.filter((i) => i.format === 'pdf');
    } else if (historyFilterFormat.value === 'excel') {
        list = list.filter((i) => i.format === 'excel');
    } else if (historyFilterFormat.value === 'today') {
        list = list.filter((i) => i.date.toLowerCase().includes('hoy'));
    }

    if (!q) return list;
    return list.filter(
        (i) =>
            i.title.toLowerCase().includes(q) ||
            i.user.toLowerCase().includes(q) ||
            i.filters.toLowerCase().includes(q) ||
            i.scope.toLowerCase().includes(q)
    );
});

const previewSummaryText = computed(() => {
    if (activeScope.value === 'soportes') {
        const parts: string[] = [];
        if (ticketsFilters.value.estado) parts.push(`estado "${ticketsFilters.value.estado}"`);
        if (ticketsFilters.value.prioridad) parts.push(`prioridad "${ticketsFilters.value.prioridad}"`);
        if (ticketsFilters.value.fecha_inicio && ticketsFilters.value.fecha_fin) {
            parts.push(`entre ${ticketsFilters.value.fecha_inicio} y ${ticketsFilters.value.fecha_fin}`);
        }
        return parts.length > 0
            ? `Se exportarán tickets filtrados por ${parts.join(', ')}.`
            : 'Se exportará el histórico completo de incidencias y tickets.';
    }
    if (activeScope.value === 'inventario') {
        return 'Se exportará el inventario consolidado, valorización y stock actual.';
    }
    if (activeScope.value === 'mantenimiento') {
        return 'Se exportará el cronograma de mantenimientos preventivos y correctivos.';
    }
    return 'Se generará el reporte de rendimiento del equipo técnico con KPIs de resolución.';
});

const handleDownloadPdf = () => {
    if (activeScope.value === 'soportes') {
        const params = new URLSearchParams();
        if (ticketsFilters.value.fecha_inicio) params.append('fecha_inicio', ticketsFilters.value.fecha_inicio);
        if (ticketsFilters.value.fecha_fin) params.append('fecha_fin', ticketsFilters.value.fecha_fin);
        if (ticketsFilters.value.estado) params.append('estado', ticketsFilters.value.estado);
        if (ticketsFilters.value.categoria_id) params.append('categoria_id', ticketsFilters.value.categoria_id);
        if (ticketsFilters.value.prioridad) params.append('prioridad', ticketsFilters.value.prioridad);
        window.open(`/reportes/tickets/pdf?${params.toString()}`, '_blank');
    } else if (activeScope.value === 'inventario') {
        window.open('/reportes/inventario/pdf', '_blank');
    } else if (activeScope.value === 'mantenimiento') {
        window.open('/reportes/mantenimientos/pdf', '_blank');
    } else if (activeScope.value === 'rendimiento') {
        window.open('/reportes/rendimiento/pdf', '_blank');
    }
};

const handleExportCsv = () => {
    if (activeScope.value === 'soportes') {
        const params = new URLSearchParams();
        if (ticketsFilters.value.fecha_inicio) params.append('fecha_inicio', ticketsFilters.value.fecha_inicio);
        if (ticketsFilters.value.fecha_fin) params.append('fecha_fin', ticketsFilters.value.fecha_fin);
        if (ticketsFilters.value.estado) params.append('estado', ticketsFilters.value.estado);
        if (ticketsFilters.value.categoria_id) params.append('categoria_id', ticketsFilters.value.categoria_id);
        if (ticketsFilters.value.prioridad) params.append('prioridad', ticketsFilters.value.prioridad);
        window.open(`/reportes/tickets/excel?${params.toString()}`, '_blank');
    } else {
        handleDownloadPdf();
    }
};

const downloadPreset = (preset: 'inventory' | 'maintenance' | 'performance') => {
    if (preset === 'inventory') window.open('/reportes/inventario/pdf', '_blank');
    if (preset === 'maintenance') window.open('/reportes/mantenimientos/pdf', '_blank');
    if (preset === 'performance') window.open('/reportes/rendimiento/pdf', '_blank');
};
</script>

<template>
    <AppLayout title="Centro de Reportes y Analíticas">
        <Head title="Centro de Reportes y Analíticas" />

        <div class="module-wrapper">
            <!-- VISTA 1: GENERADOR DE REPORTES Y ANALÍTICAS -->
            <section v-if="activeView === 'generator'" class="view-pane">
                <BasePageHeader
                    title="Reportes & Analíticas"
                    subtitle="Genera, exporta y analiza el rendimiento de tu operación."
                >
                    <template #icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                            <path d="M22 12A10 10 0 0 0 12 2v10z" />
                        </svg>
                    </template>
                    <template #actions>
                        <BaseButton variant="secondary" @click="activeView = 'history'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-action-icon">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>Historial</span>
                        </BaseButton>
                    </template>
                </BasePageHeader>

                <!-- TARJETA PRINCIPAL: GENERADOR -->
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

                        <ReportScopeTabs v-model="activeScope" />
                    </div>

                    <div class="generator-body-grid">
                        <div class="generator-inputs-col">
                            <ReportFilterForm
                                :scope="activeScope"
                                :filters="ticketsFilters"
                                :categories="categories"
                                @update:filters="ticketsFilters = $event"
                            />

                            <ReportPreviewCard :summary-text="previewSummaryText" />
                        </div>

                        <div class="generator-actions-col">
                            <button class="btn-export-solid" type="button" @click="handleDownloadPdf">
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
                                v-if="activeScope === 'soportes'"
                                class="btn-export-outline"
                                type="button"
                                @click="handleExportCsv"
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

                <PredefinedReportsGrid @download-preset="downloadPreset" />
            </section>

            <!-- VISTA 2: HISTORIAL DE REPORTES -->
            <section v-else class="view-pane">
                <BasePageHeader
                    title="Historial de Reportes"
                    subtitle="Registro de todos los reportes generados."
                >
                    <template #icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </template>
                    <template #actions>
                        <BaseButton variant="secondary" @click="activeView = 'generator'">
                            ← Volver a Reportes
                        </BaseButton>
                    </template>
                </BasePageHeader>

                <ReportHistoryKpis
                    :total-count="reportHistory.length"
                    :pdf-count="3"
                    :excel-count="1"
                    :today-count="2"
                    :active-filter="historyFilterFormat"
                    @update:active-filter="historyFilterFormat = $event"
                />

                <ReportHistoryTable
                    :items="filteredHistory"
                    :total-count="reportHistory.length"
                    :search-query="searchHistory"
                    @update:search-query="searchHistory = $event"
                />
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.module-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-bottom: 32px;
}

.view-pane {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.header-action-icon {
    width: 15px;
    height: 15px;
    margin-right: 6px;
}

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

@media (max-width: 768px) {
    .module-header {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
