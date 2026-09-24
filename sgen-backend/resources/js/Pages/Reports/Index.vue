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
                    :pdf-count="3"
                    :excel-count="1"
                    :today-count="2"
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
