<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton } from '@/Components/UI';
import type { MaintenanceItem, MaintenanceKpis, MaintenanceFilterPill } from '@/Components/Maintenance/types';
import MaintenanceKpiRow from '@/Components/Maintenance/MaintenanceKpiRow.vue';
import MaintenanceToolbar from '@/Components/Maintenance/MaintenanceToolbar.vue';
import MaintenanceTable from '@/Components/Maintenance/MaintenanceTable.vue';
import MaintenanceEmptyState from '@/Components/Maintenance/MaintenanceEmptyState.vue';

const props = defineProps<{
    mantenimientos: MaintenanceItem[];
    kpis: MaintenanceKpis;
    filters?: Record<string, string>;
}>();

const searchQuery = ref(props.filters?.search || '');
const activeFilter = ref<MaintenanceFilterPill>('todos');

const filteredMantenimientos = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    const filter = activeFilter.value;

    return props.mantenimientos.filter((item) => {
        if (filter === 'pendiente' && item.estado !== 'pendiente') return false;
        if (filter === 'en_proceso' && item.estado !== 'en_proceso') return false;
        if (filter === 'completado' && item.estado !== 'completado') return false;
        if (filter === 'preventivo' && item.tipoMantenimiento !== 'preventivo') return false;
        if (filter === 'correctivo' && item.tipoMantenimiento !== 'correctivo') return false;

        if (!q) return true;
        const codMatch = (item.equipoCodigo || '').toLowerCase().includes(q);
        const descMatch = (item.descripcion || '').toLowerCase().includes(q);
        const techMatch = (item.tecnicoNombre || '').toLowerCase().includes(q);
        const tipoMatch = (item.tipoMantenimiento || '').toLowerCase().includes(q);
        return codMatch || descMatch || techMatch || tipoMatch;
    });
});

const openCreateView = () => router.visit('/mantenimientos/crear');
const openDashboardView = () => router.visit('/mantenimientos/dashboard');

const handleComplete = (id: number) => {
    if (confirm('¿Desea marcar este mantenimiento como completado?')) {
        router.post(`/mantenimientos/${id}/completar`, {}, { preserveScroll: true });
    }
};

const handleDelete = (id: number) => {
    if (confirm('¿Está seguro de eliminar este registro de mantenimiento?')) {
        router.delete(`/mantenimientos/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout title="Gestión de Mantenimientos">
        <Head title="Gestión de Mantenimientos" />

        <div class="view-wrapper">
            <!-- CABECERA DEL MÓDULO -->
            <BasePageHeader
                title="Gestión de Mantenimientos"
                subtitle="Control predictivo, preventivo y correctivo de la flota técnica."
            >
                <template #icon>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                </template>
                <template #actions>
                    <BaseButton variant="secondary" type="button" @click="openDashboardView">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-action-icon">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                        </svg>
                        <span>Dashboard</span>
                    </BaseButton>
                    <BaseButton variant="primary" type="button" @click="openCreateView">
                        + Programar Mantenimiento
                    </BaseButton>
                </template>
            </BasePageHeader>

            <!-- 3 TARJETAS KPIS -->
            <MaintenanceKpiRow :kpis="kpis" @select-filter="activeFilter = $event" />

            <!-- BARRA DE HERRAMIENTAS Y FILTROS -->
            <MaintenanceToolbar
                v-model:active-filter="activeFilter"
                v-model:search-query="searchQuery"
            />

            <!-- TABLA O ESTADO VACÍO -->
            <MaintenanceTable
                v-if="filteredMantenimientos.length > 0"
                :items="filteredMantenimientos"
                @complete="handleComplete"
                @delete="handleDelete"
            />
            <MaintenanceEmptyState v-else />
        </div>
    </AppLayout>
</template>

<style scoped>
.view-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-bottom: 24px;
}

.header-action-icon {
    width: 15px;
    height: 15px;
    margin-right: 6px;
}
</style>
