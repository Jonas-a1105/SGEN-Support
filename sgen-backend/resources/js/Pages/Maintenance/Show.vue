<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseKpiCard } from '@/Components/UI';
import { formatCurrency, formatDate } from '@/Utils/formatters';
import MaintenanceDetailHeader from '@/Components/Maintenance/Detail/MaintenanceDetailHeader.vue';
import CardMaintenanceAsset from '@/Components/Maintenance/Detail/CardMaintenanceAsset.vue';
import CardWorkDetail from '@/Components/Maintenance/Detail/CardWorkDetail.vue';
import CardTechnicalObservations from '@/Components/Maintenance/Detail/CardTechnicalObservations.vue';
import CardMaintenanceHistory from '@/Components/Maintenance/Detail/CardMaintenanceHistory.vue';
import SidebarPlanning from '@/Components/Maintenance/Detail/SidebarPlanning.vue';
import SidebarTechnician from '@/Components/Maintenance/Detail/SidebarTechnician.vue';
import SidebarFinancial from '@/Components/Maintenance/Detail/SidebarFinancial.vue';
import ModalCompleteMaintenance from '@/Components/Maintenance/Detail/ModalCompleteMaintenance.vue';
import ModalPostponeMaintenance from '@/Components/Maintenance/Detail/ModalPostponeMaintenance.vue';
import ModalCancelMaintenance from '@/Components/Maintenance/Detail/ModalCancelMaintenance.vue';
import ModalEditMaintenance from '@/Components/Maintenance/Detail/ModalEditMaintenance.vue';
import type {
    MaintenanceDetail,
    FormOptions,
} from '@/Components/Maintenance/Detail/types';

export type {
    MaintenanceDetail,
    EquipmentInfo,
    RelatedMaintenance,
    FormOptions,
} from '@/Components/Maintenance/Detail/types';

const props = defineProps<{
    maintenance: MaintenanceDetail;
    options?: FormOptions;
}>();

const isCompleteModalOpen = ref(false);
const isPostponeModalOpen = ref(false);
const isCancelModalOpen = ref(false);
const isEditModalOpen = ref(false);


</script>

<template>
    <AppLayout :title="`Mantenimiento #${maintenance.id}`">
        <Head :title="`Mantenimiento #${maintenance.id} - ${maintenance.tipoMantenimiento}`" />

        <div class="maint-show-wrapper">
            <!-- Header Bar & Overdue Alert -->
            <MaintenanceDetailHeader
                :maintenance="maintenance"
                @complete="isCompleteModalOpen = true"
                @postpone="isPostponeModalOpen = true"
                @cancel="isCancelModalOpen = true"
                @edit="isEditModalOpen = true"
            />

            <!-- 4 KPI Metrics Row -->
            <section class="maint-kpi-grid" aria-label="Métricas de la orden de mantenimiento">
                <BaseKpiCard
                    label="COSTO ESTIMADO / FINAL"
                    :value="formatCurrency(maintenance.costo)"
                    :subtext="`Tipo: ${maintenance.tipoMantenimiento}`"
                    color="brand"
                />
                <BaseKpiCard
                    label="ESTADO DEL SERVICIO"
                    :value="maintenance.estado.toUpperCase().replace('_', ' ')"
                    :subtext="`Orden #${maintenance.id}`"
                    :color="maintenance.estado === 'completado' ? 'green' : (maintenance.isOverdue ? 'red' : 'blue')"
                />
                <BaseKpiCard
                    label="FRECUENCIA OPERATIVA"
                    :value="maintenance.frecuencia.toUpperCase()"
                    :subtext="maintenance.frecuencia !== 'unica' ? 'Recurrencia activa' : 'Servicio puntual'"
                    color="purple"
                />
                <BaseKpiCard
                    label="PRÓXIMA FECHA"
                    :value="formatDate(maintenance.proximaFecha)"
                    :subtext="maintenance.proximaFecha ? 'Ciclo programado' : 'Sin fecha recurrente'"
                    color="orange"
                />
            </section>

            <!-- Main Content: 2 Columns -->
            <div class="maint-content-grid">
                <!-- LEFT COLUMN: Main Details (8 cols) -->
                <main class="maint-col-main">
                    <!-- Equipment Card -->
                    <CardMaintenanceAsset :equipo="maintenance.equipo" />

                    <!-- Work Description Card -->
                    <CardWorkDetail
                        :descripcion="maintenance.descripcion"
                        :checklist="maintenance.checklist"
                        :is-completed="maintenance.estado === 'completado'"
                    />

                    <!-- Technical Observations Card -->
                    <CardTechnicalObservations
                        v-if="maintenance.observaciones"
                        :observaciones="maintenance.observaciones"
                    />

                    <!-- Equipment Maintenance History -->
                    <CardMaintenanceHistory :historial="maintenance.historialEquipo" />
                </main>

                <!-- RIGHT COLUMN: Metadata & Technician (4 cols) -->
                <aside class="maint-col-sidebar">
                    <SidebarPlanning :maintenance="maintenance" />
                    <SidebarTechnician
                        :tecnico-nombre="maintenance.tecnicoNombre"
                        :realizado-por="maintenance.realizadoPor"
                    />
                    <SidebarFinancial :maintenance="maintenance" />
                </aside>
            </div>
        </div>

        <!-- MODALS -->
        <ModalCompleteMaintenance
            :is-open="isCompleteModalOpen"
            :maintenance="maintenance"
            @close="isCompleteModalOpen = false"
        />

        <ModalPostponeMaintenance
            :is-open="isPostponeModalOpen"
            :maintenance="maintenance"
            @close="isPostponeModalOpen = false"
        />

        <ModalCancelMaintenance
            :is-open="isCancelModalOpen"
            :maintenance="maintenance"
            @close="isCancelModalOpen = false"
        />

        <ModalEditMaintenance
            :is-open="isEditModalOpen"
            :maintenance="maintenance"
            :options="options"
            @close="isEditModalOpen = false"
        />
    </AppLayout>
</template>

<style scoped>
.maint-show-wrapper {
    display: flex;
    flex-direction: column;
    gap: 24px;
    width: 100%;
}

.maint-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 1024px) {
    .maint-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
}

.maint-content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 1024px) {
    .maint-content-grid {
        grid-template-columns: 8fr 4fr;
    }
}

.maint-col-main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.maint-col-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}
</style>
