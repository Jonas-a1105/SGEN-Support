<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseKpiCard } from '@/Components/UI';
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

const activeTab = ref<EquipmentTabKey>('specs');
const isReassignModalOpen = ref(false);
const isEditModalOpen = ref(false);

const setActiveTab = (tab: EquipmentTabKey) => {
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
                    :active="activeTab === 'maintenance'"
                    clickable
                    @click="setActiveTab('maintenance')"
                />
                <BaseKpiCard
                    label="TICKETS DE SOPORTE"
                    :value="equipment.tickets.length"
                    subtext="Reportes de incidencia"
                    icon="fa-solid fa-ticket"
                    color="blue"
                    :active="activeTab === 'support'"
                    clickable
                    @click="setActiveTab('support')"
                />
                <BaseKpiCard
                    label="ESTADO DE GARANTÍA"
                    :value="equipment.warrantyRemaining || (equipment.warranty ? 'Activa' : 'Sin garantía')"
                    :subtext="equipment.purchaseValue ? formatCurrency(equipment.purchaseValue) : 'Sin costo reg.'"
                    icon="fa-solid fa-shield-halved"
                    color="purple"
                    :active="activeTab === 'purchase'"
                    clickable
                    @click="setActiveTab('purchase')"
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
                        <!-- Navigation Tabs Bar -->
                        <nav class="equip-tabs-nav" aria-label="Pestañas de equipo">
                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'specs' }"
                                @click="setActiveTab('specs')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                    <rect x="9" y="9" width="6" height="6"></rect>
                                    <line x1="9" y1="1" x2="9" y2="4"></line>
                                    <line x1="15" y1="1" x2="15" y2="4"></line>
                                    <line x1="9" y1="20" x2="9" y2="23"></line>
                                    <line x1="15" y1="20" x2="15" y2="23"></line>
                                    <line x1="20" y1="9" x2="23" y2="9"></line>
                                    <line x1="20" y1="14" x2="23" y2="14"></line>
                                    <line x1="1" y1="9" x2="4" y2="9"></line>
                                    <line x1="1" y1="14" x2="4" y2="14"></line>
                                </svg>
                                <span>Especificaciones</span>
                            </button>

                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'purchase' }"
                                @click="setActiveTab('purchase')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                <span>Adquisición y Garantía</span>
                            </button>

                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'support' }"
                                @click="setActiveTab('support')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span>Soportes</span>
                                <span v-if="equipment.tickets.length > 0" class="equip-tab-counter">
                                    {{ equipment.tickets.length }}
                                </span>
                            </button>

                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'maintenance' }"
                                @click="setActiveTab('maintenance')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                </svg>
                                <span>Mantenimiento</span>
                                <span v-if="equipment.maintenances.length > 0" class="equip-tab-counter">
                                    {{ equipment.maintenances.length }}
                                </span>
                            </button>
                        </nav>

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

        <!-- MODAL: Reasignar Ubicación y Custodio -->
        <ModalReassignEquipment
            :is-open="isReassignModalOpen"
            :equipment="equipment"
            @close="isReassignModalOpen = false"
        />

        <!-- MODAL: Editar Ficha del Equipo -->
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
    gap: var(--space-6);
    padding: var(--space-6);
    max-width: 1440px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

.equip-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: var(--space-4);
}

.equip-content-layout {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: var(--space-6);
    align-items: flex-start;
}

.equip-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.equip-main-col {
    display: flex;
    flex-direction: column;
}

.equip-panel-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.equip-tabs-nav {
    display: flex;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
    overflow-x: auto;
}

.equip-tab-button {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    border: none;
    background: transparent;
    color: var(--text-dim);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
    white-space: nowrap;
}

.equip-tab-button svg {
    width: 16px;
    height: 16px;
}

.equip-tab-button:hover {
    color: var(--text);
    background: var(--stroke-subtle);
}

.equip-tab-button.is-active {
    background: var(--bg-card);
    color: var(--brand);
}

.equip-tab-counter {
    background: var(--stroke);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
}

.equip-tab-button.is-active .equip-tab-counter {
    background: rgba(var(--brand-rgb), 0.2);
    color: var(--brand);
}

.equip-tab-body {
    padding: var(--space-6);
}

@media (max-width: 1024px) {
    .equip-content-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .equip-show-container {
        padding: var(--space-4);
        gap: var(--space-4);
    }

    .equip-tab-body {
        padding: var(--space-4);
    }
}
</style>
