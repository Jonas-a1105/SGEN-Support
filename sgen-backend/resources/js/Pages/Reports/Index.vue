<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
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
import ReportGeneratorCard from '@/Components/Reports/ReportGeneratorCard.vue';
import PredefinedReportsGrid from '@/Components/Reports/PredefinedReportsGrid.vue';
import ReportHistoryKpis from '@/Components/Reports/ReportHistoryKpis.vue';
import ReportHistoryTable from '@/Components/Reports/ReportHistoryTable.vue';

defineProps<{
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

const page = usePage<{ auth?: { user?: { username: string } } }>();
const STORAGE_KEY = 'sgen_reports_download_history';

const loadHistoryFromStorage = (): ReportHistoryItem[] => {
    if (typeof window === 'undefined') return [];
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            return JSON.parse(stored);
        }
    } catch {
        // Fallback silently if storage read fails
    }
    return [];
};

const reportHistory = ref<ReportHistoryItem[]>(loadHistoryFromStorage());

const recordReportDownload = (item: {
    title: string;
    scope: ReportScope;
    format: 'pdf' | 'excel' | 'csv';
    filters: string;
    url: string;
}) => {
    const now = new Date();
    const dateFormatted = now.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

    const newItem: ReportHistoryItem = {
        id: Date.now(),
        title: item.title,
        scope: item.scope,
        date: dateFormatted,
        format: item.format,
        user: page.props.auth?.user?.username || 'admin',
        filters: item.filters,
        url: item.url,
    };

    reportHistory.value = [newItem, ...reportHistory.value.slice(0, 49)];
    if (typeof window !== 'undefined') {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(reportHistory.value));
        } catch {
            // Ignore storage write issues
        }
    }
};

const searchHistory = ref('');
const historyFilterFormat = ref<HistoryFilterFormat>('all');

const historyPdfCount = computed(() => reportHistory.value.filter((i) => i.format === 'pdf').length);
const historyExcelCount = computed(() => reportHistory.value.filter((i) => i.format === 'excel' || (i.format as string) === 'csv').length);
const historyTodayCount = computed(() => {
    const nowStr = new Date().toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }).toLowerCase();
    return reportHistory.value.filter((i) => i.date.toLowerCase().includes(nowStr) || i.date.toLowerCase().includes('hoy')).length;
});

const filteredHistory = computed(() => {
    let list = reportHistory.value;
    const q = searchHistory.value.trim().toLowerCase();

    if (historyFilterFormat.value === 'pdf') {
        list = list.filter((i) => i.format === 'pdf');
    } else if (historyFilterFormat.value === 'excel') {
        list = list.filter((i) => i.format === 'excel' || (i.format as string) === 'csv');
    } else if (historyFilterFormat.value === 'today') {
        const nowStr = new Date().toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }).toLowerCase();
        list = list.filter((i) => i.date.toLowerCase().includes(nowStr) || i.date.toLowerCase().includes('hoy'));
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
    let url = '';
    let title = '';
    let filters = 'General';

    if (activeScope.value === 'soportes') {
        const params = new URLSearchParams();
        if (ticketsFilters.value.fecha_inicio) params.append('fecha_inicio', ticketsFilters.value.fecha_inicio);
        if (ticketsFilters.value.fecha_fin) params.append('fecha_fin', ticketsFilters.value.fecha_fin);
        if (ticketsFilters.value.estado) params.append('estado', ticketsFilters.value.estado);
        if (ticketsFilters.value.categoria_id) params.append('categoria_id', ticketsFilters.value.categoria_id);
        if (ticketsFilters.value.prioridad) params.append('prioridad', ticketsFilters.value.prioridad);
        url = `/reportes/tickets/pdf?${params.toString()}`;
        title = 'Reporte de Tickets';
        filters = ticketsFilters.value.estado ? `Estado: ${ticketsFilters.value.estado}` : 'Todos los tickets';
    } else if (activeScope.value === 'inventario') {
        url = '/reportes/inventario/pdf';
        title = 'Valoración de Inventario';
        filters = 'Catálogo completo';
    } else if (activeScope.value === 'mantenimiento') {
        url = '/reportes/mantenimientos/pdf';
        title = 'Reporte de Mantenimientos';
        filters = 'Cronograma preventivo y correctivo';
    } else if (activeScope.value === 'rendimiento') {
        url = '/reportes/rendimiento/pdf';
        title = 'Rendimiento Técnico';
        filters = 'Métricas y SLA global';
    }

    if (url) {
        window.open(url, '_blank');
        recordReportDownload({
            title,
            scope: activeScope.value,
            format: 'pdf',
            filters,
            url,
        });
    }
};

const handleExportCsv = () => {
    let url = '';
    let title = '';
    let filters = 'General';

    if (activeScope.value === 'soportes') {
        const params = new URLSearchParams();
        if (ticketsFilters.value.fecha_inicio) params.append('fecha_inicio', ticketsFilters.value.fecha_inicio);
        if (ticketsFilters.value.fecha_fin) params.append('fecha_fin', ticketsFilters.value.fecha_fin);
        if (ticketsFilters.value.estado) params.append('estado', ticketsFilters.value.estado);
        if (ticketsFilters.value.categoria_id) params.append('categoria_id', ticketsFilters.value.categoria_id);
        if (ticketsFilters.value.prioridad) params.append('prioridad', ticketsFilters.value.prioridad);
        url = `/reportes/tickets/excel?${params.toString()}`;
        title = 'Exportación Tickets Excel';
        filters = ticketsFilters.value.estado ? `Estado: ${ticketsFilters.value.estado}` : 'Todos';
    } else if (activeScope.value === 'inventario') {
        url = '/reportes/inventario/excel';
        title = 'Exportación Inventario Excel';
        filters = 'Stock consolidado';
    } else if (activeScope.value === 'mantenimiento') {
        url = '/reportes/mantenimientos/excel';
        title = 'Exportación Mantenimientos Excel';
        filters = 'Órdenes de servicio';
    } else {
        handleDownloadPdf();
        return;
    }

    if (url) {
        window.open(url, '_blank');
        recordReportDownload({
            title,
            scope: activeScope.value,
            format: 'excel',
            filters,
            url,
        });
    }
};

const downloadPreset = (preset: 'inventory' | 'maintenance' | 'performance' | 'equipment') => {
    if (preset === 'inventory') {
        window.open('/reportes/inventario/pdf', '_blank');
        recordReportDownload({
            title: 'Inventario General',
            scope: 'inventario',
            format: 'pdf',
            filters: 'Plantilla predeterminada',
            url: '/reportes/inventario/pdf',
        });
    }
    if (preset === 'maintenance') {
        window.open('/reportes/mantenimientos/pdf', '_blank');
        recordReportDownload({
            title: 'Plan de Mantenimiento',
            scope: 'mantenimiento',
            format: 'pdf',
            filters: 'Plantilla predeterminada',
            url: '/reportes/mantenimientos/pdf',
        });
    }
    if (preset === 'performance') {
        window.open('/reportes/rendimiento/pdf', '_blank');
        recordReportDownload({
            title: 'Rendimiento y SLA',
            scope: 'rendimiento',
            format: 'pdf',
            filters: 'Plantilla predeterminada',
            url: '/reportes/rendimiento/pdf',
        });
    }
    if (preset === 'equipment') {
        window.open('/reportes/equipos/excel', '_blank');
        recordReportDownload({
            title: 'Equipos TI Excel',
            scope: 'inventario',
            format: 'excel',
            filters: 'Plantilla predeterminada',
            url: '/reportes/equipos/excel',
        });
    }
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

                <ReportGeneratorCard
                    v-model:scope="activeScope"
                    v-model:filters="ticketsFilters"
                    :categories="categories"
                    :preview-summary="previewSummaryText"
                    @download-pdf="handleDownloadPdf"
                    @export-csv="handleExportCsv"
                />

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
                    :pdf-count="historyPdfCount"
                    :excel-count="historyExcelCount"
                    :today-count="historyTodayCount"
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
</style>
