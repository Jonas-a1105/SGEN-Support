<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseKpiCard, BaseTabs, type TabItem } from '@/Components/UI';
import { formatCurrency } from '@/Utils/formatters';
import EquipmentDetailHeader from '@/Components/Equipment/Detail/EquipmentDetailHeader.vue';
import SidebarCustodyLocation from '@/Components/Equipment/Detail/SidebarCustodyLocation.vue';
import SidebarHardwareIdentity from '@/Components/Equipment/Detail/SidebarHardwareIdentity.vue';
import TabEquipmentSpecs from '@/Components/Equipment/Detail/TabEquipmentSpecs.vue';
import TabEquipmentPurchase from '@/Components/Equipment/Detail/TabEquipmentPurchase.vue';
import TabEquipmentTickets from '@/Components/Equipment/Detail/TabEquipmentTickets.vue';
import TabEquipmentMaintenances from '@/Components/Equipment/Detail/TabEquipmentMaintenances.vue';
import ModalReassignEquipment from '@/Components/Equipment/Detail/ModalReassignEquipment.vue';
import ModalEditEquipment from '@/Components/Equipment/Detail/ModalEditEquipment.vue';
import type {
    EquipmentDetail,
    EquipmentTabKey,
} from '@/Components/Equipment/Detail/types';

export type {
    EquipmentDetail,
    EquipmentTicket,
    EquipmentMaintenance,
    DepartmentOption,
    EmployeeOption,
    EquipmentTabKey,
} from '@/Components/Equipment/Detail/types';

const props = defineProps<{
    equipment: EquipmentDetail;
}>();

const activeTab = ref<string>('specs');
const isReassignModalOpen = ref(false);
const isEditModalOpen = ref(false);

const tabs = computed<TabItem[]>(() => [
    { key: 'specs', label: 'Especificaciones' },
    { key: 'purchase', label: 'Adquisición y Garantía' },
    { key: 'support', label: 'Soportes', count: props.equipment.tickets?.length },
    { key: 'maintenance', label: 'Mantenimiento', count: props.equipment.maintenances?.length },
]);

const setActiveTab = (tab: string) => {
    activeTab.value = tab;
};

const kpiStatusColor = computed<'green' | 'blue' | 'yellow' | 'red'>(() => {
    switch (props.equipment.rawStatus.toLowerCase()) {
        case 'disponible':
            return 'green';
        case 'en_uso':
            return 'blue';
        case 'en_reparacion':
            return 'yellow';
        default:
            return 'red';
    }
});
</script>

<template>
    <AppLayout :title="`Equipo: ${equipment.name}`">
        <Head :title="`Equipo - ${equipment.name}`" />

        <div class="equip-show-container">
            <!-- Header Bar -->
            <EquipmentDetailHeader
                :equipment="equipment"
                @edit="isEditModalOpen = true"
            />

            <!-- 4 KPI Cards -->
            <section class="equip-kpi-grid" aria-label="Indicadores del equipo">
                <BaseKpiCard
                    label="ESTADO OPERATIVO"
                    :value="equipment.status"
                    subtext="Condición del activo"
                    icon="fa-solid fa-signal"
                    :color="kpiStatusColor"
                />
                <BaseKpiCard
                    label="MANTENIMIENTOS"
                    :value="equipment.maintenances.length"
                    subtext="Historial de servicios"
                    icon="fa-solid fa-wrench"
                    color="yellow"
                />
                <BaseKpiCard
                    label="TICKETS DE SOPORTE"
                    :value="equipment.tickets.length"
                    subtext="Reportes de incidencia"
                    icon="fa-solid fa-ticket"
                    color="blue"
                />
                <BaseKpiCard
                    label="ESTADO DE GARANTÍA"
                    :value="equipment.warrantyRemaining || (equipment.warranty ? 'Activa' : 'Sin garantía')"
                    :subtext="equipment.purchaseValue ? formatCurrency(equipment.purchaseValue) : 'Sin costo reg.'"
                    icon="fa-solid fa-shield-halved"
                    color="purple"
                />
            </section>

            <!-- Main 2-Column Layout -->
            <div class="equip-content-layout">
                <!-- Left Column: Identity & Custody -->
                <aside class="equip-sidebar-col">
                    <SidebarCustodyLocation
                        :equipment="equipment"
                        @reassign="isReassignModalOpen = true"
                    />
                    <SidebarHardwareIdentity :equipment="equipment" />
                </aside>

                <!-- Right Column: Tabs Panel -->
                <main class="equip-main-col">
                    <div class="equip-panel-wrapper">
                        <!-- Navigation Tabs Bar with BaseTabs -->
                        <div class="equip-tabs-bar">
                            <BaseTabs v-model="activeTab" :tabs="tabs" />
                        </div>

                        <!-- Tab Panels -->
                        <div class="equip-tab-body">
                            <TabEquipmentSpecs
                                v-if="activeTab === 'specs'"
                                :equipment="equipment"
                            />

                            <TabEquipmentPurchase
                                v-else-if="activeTab === 'purchase'"
                                :equipment="equipment"
                            />

                            <TabEquipmentTickets
                                v-else-if="activeTab === 'support'"
                                :tickets="equipment.tickets"
                                :equipment-id="equipment.id"
                            />

                            <TabEquipmentMaintenances
                                v-else-if="activeTab === 'maintenance'"
                                :maintenances="equipment.maintenances"
                                :equipment-id="equipment.id"
                            />
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- Modals -->
        <ModalReassignEquipment
            :is-open="isReassignModalOpen"
            :equipment="equipment"
            @close="isReassignModalOpen = false"
        />

        <ModalEditEquipment
            :is-open="isEditModalOpen"
            :equipment="equipment"
            @close="isEditModalOpen = false"
        />
    </AppLayout>
</template>

<style scoped>
.equip-show-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.equip-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.equip-content-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 24px;
    align-items: start;
}

.equip-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.equip-main-col {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.equip-panel-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.equip-tabs-bar {
    padding: var(--space-3) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
}

.equip-tab-body {
    padding: 24px;
}

@media (max-width: 1024px) {
    .equip-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }

    .equip-content-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .equip-show-container {
        gap: 16px;
    }

    .equip-tab-body {
        padding: 16px;
    }
}
</style>
