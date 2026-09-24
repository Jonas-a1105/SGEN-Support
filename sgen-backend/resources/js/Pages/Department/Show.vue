<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseConfirmModal, BaseKpiCard } from '@/Components/UI';
import DepartmentDetailHeader from '@/Components/Department/Detail/DepartmentDetailHeader.vue';
import TabDepartmentOverview from '@/Components/Department/Detail/TabDepartmentOverview.vue';
import TabDepartmentEmployees from '@/Components/Department/Detail/TabDepartmentEmployees.vue';
import TabDepartmentAssets from '@/Components/Department/Detail/TabDepartmentAssets.vue';
import TabDepartmentInventory from '@/Components/Department/Detail/TabDepartmentInventory.vue';
import ModalAssignEmployee from '@/Components/Department/Detail/ModalAssignEmployee.vue';
import ModalAssignEquipment from '@/Components/Department/Detail/ModalAssignEquipment.vue';
import ModalEditDepartment from '@/Components/Department/Detail/ModalEditDepartment.vue';
import type {
    DepartmentDetail,
    DepartmentEmployee,
    DepartmentEquipment,
    DepartmentTabKey,
} from '@/Components/Department/Detail/types';

export type {
    DepartmentDetail,
    DepartmentEmployee,
    DepartmentEquipment,
    DepartmentConsumable,
    EmployeeCandidate,
    EquipmentCandidate,
} from '@/Components/Department/Detail/types';

const props = defineProps<{
    department: DepartmentDetail;
}>();

const activeTab = ref<DepartmentTabKey>('overview');

// Modals
const isAssignEmployeeOpen = ref(false);
const isAssignEquipmentOpen = ref(false);
const isEditModalOpen = ref(false);

interface UnlinkTarget {
    type: 'employee' | 'equipment';
    id: number;
    name: string;
}
const isConfirmUnlinkOpen = ref(false);
const unlinkTarget = ref<UnlinkTarget | null>(null);

const requestUnlinkEmployee = (emp: DepartmentEmployee) => {
    unlinkTarget.value = {
        type: 'employee',
        id: emp.id,
        name: emp.fullName,
    };
    isConfirmUnlinkOpen.value = true;
};

const requestUnlinkEquipment = (eq: DepartmentEquipment) => {
    unlinkTarget.value = {
        type: 'equipment',
        id: eq.id,
        name: `${eq.codigo} - ${eq.nombre}`,
    };
    isConfirmUnlinkOpen.value = true;
};

const handleConfirmUnlink = () => {
    if (!unlinkTarget.value) return;

    const { type, id } = unlinkTarget.value;
    const url = type === 'employee'
        ? `/departamentos/${props.department.id}/empleados/${id}`
        : `/departamentos/${props.department.id}/equipos/${id}`;

    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            isConfirmUnlinkOpen.value = false;
            unlinkTarget.value = null;
        },
    });
};
</script>

<template>
    <AppLayout :title="`Departamento: ${department.nombre}`">
        <Head :title="`Departamento - ${department.nombre}`" />

        <div class="dept-show-container">
            <!-- Header Bar -->
            <DepartmentDetailHeader
                :department="department"
                @edit="isEditModalOpen = true"
            />

            <!-- 4 KPI Metrics -->
            <section class="dept-kpi-grid" aria-label="Métricas del departamento">
                <BaseKpiCard
                    label="COLABORADORES"
                    :value="department.empleadosCount"
                    subtext="Miembros adscritos"
                    icon="fa-solid fa-users"
                    color="blue"
                />
                <BaseKpiCard
                    label="EQUIPOS ASIGNADOS"
                    :value="department.equiposCount"
                    subtext="Activos en uso"
                    icon="fa-solid fa-laptop"
                    color="cyan"
                />
                <BaseKpiCard
                    label="ARTÍCULOS INVENTARIO"
                    :value="department.consumablesCount"
                    subtext="Materiales en stock"
                    icon="fa-solid fa-boxes-stacked"
                    color="orange"
                />
                <BaseKpiCard
                    label="TICKETS REGISTRADOS"
                    :value="department.ticketsCount"
                    subtext="Casos generados"
                    icon="fa-solid fa-ticket"
                    color="green"
                />
            </section>

            <!-- Main Panel with Tabs -->
            <main class="dept-main-panel">
                <div class="dept-panel-wrapper">
                    <!-- Navigation Tabs Bar -->
                    <nav class="dept-tabs-nav" aria-label="Pestañas de departamento">
                        <button
                            type="button"
                            class="dept-tab-button"
                            :class="{ 'is-active': activeTab === 'overview' }"
                            @click="activeTab = 'overview'"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span>Visión General</span>
                        </button>

                        <button
                            type="button"
                            class="dept-tab-button"
                            :class="{ 'is-active': activeTab === 'employees' }"
                            @click="activeTab = 'employees'"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Colaboradores</span>
                            <span v-if="department.empleados.length > 0" class="dept-tab-counter">
                                {{ department.empleados.length }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="dept-tab-button"
                            :class="{ 'is-active': activeTab === 'assets' }"
                            @click="activeTab = 'assets'"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                            <span>Equipos &amp; Activos</span>
                            <span v-if="department.equipos.length > 0" class="dept-tab-counter">
                                {{ department.equipos.length }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="dept-tab-button"
                            :class="{ 'is-active': activeTab === 'inventory' }"
                            @click="activeTab = 'inventory'"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                            <span>Inventario &amp; Consumibles</span>
                            <span v-if="department.consumibles.length > 0" class="dept-tab-counter">
                                {{ department.consumibles.length }}
                            </span>
                        </button>
                    </nav>

                    <!-- Tab Panels Body -->
                    <div class="dept-tab-body">
                        <TabDepartmentOverview
                            v-if="activeTab === 'overview'"
                            :department="department"
                            @assign-manager="isEditModalOpen = true"
                        />

                        <TabDepartmentEmployees
                            v-else-if="activeTab === 'employees'"
                            :empleados="department.empleados"
                            @assign="isAssignEmployeeOpen = true"
                            @unlink="requestUnlinkEmployee"
                        />

                        <TabDepartmentAssets
                            v-else-if="activeTab === 'assets'"
                            :equipos="department.equipos"
                            @assign="isAssignEquipmentOpen = true"
                            @unlink="requestUnlinkEquipment"
                        />

                        <TabDepartmentInventory
                            v-else-if="activeTab === 'inventory'"
                            :consumibles="department.consumibles"
                        />
                    </div>
                </div>
            </main>
        </div>

        <!-- MODAL: Vincular Colaborador -->
        <ModalAssignEmployee
            :is-open="isAssignEmployeeOpen"
            :department="department"
            @close="isAssignEmployeeOpen = false"
        />

        <!-- MODAL: Asignar Equipo -->
        <ModalAssignEquipment
            :is-open="isAssignEquipmentOpen"
            :department="department"
            @close="isAssignEquipmentOpen = false"
        />

        <!-- MODAL: Editar Información -->
        <ModalEditDepartment
            :is-open="isEditModalOpen"
            :department="department"
            @close="isEditModalOpen = false"
        />

        <!-- MODAL: Confirmar Desvinculación -->
        <BaseConfirmModal
            :is-open="isConfirmUnlinkOpen"
            :title="unlinkTarget?.type === 'employee' ? 'Desvincular Colaborador' : 'Desvincular Equipo'"
            :message="`¿Está seguro de que desea desvincular a '${unlinkTarget?.name}' de este departamento? No se eliminará del sistema.`"
            confirm-text="Desvincular"
            variant="danger"
            @close="isConfirmUnlinkOpen = false"
            @confirm="handleConfirmUnlink"
        />
    </AppLayout>
</template>

<style scoped>
.dept-show-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 24px;
    min-height: 100vh;
}

.dept-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.dept-main-panel {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.dept-panel-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.dept-tabs-nav {
    display: flex;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
    overflow-x: auto;
}

.dept-tab-button {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: 8px 16px;
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

.dept-tab-button svg {
    width: 16px;
    height: 16px;
}

.dept-tab-button:hover {
    color: var(--text);
    background: var(--stroke-subtle);
}

.dept-tab-button.is-active {
    background: var(--bg-card);
    color: var(--brand);
}

.dept-tab-counter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: var(--radius-pill);
    font-size: 11px;
    font-weight: 700;
    background: var(--stroke);
    color: var(--text);
}

.dept-tab-button.is-active .dept-tab-counter {
    background: var(--brand-glow);
    color: var(--brand);
}

.dept-tab-body {
    padding: 24px;
}

@media (max-width: 1024px) {
    .dept-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .dept-kpi-grid {
        grid-template-columns: 1fr;
    }

    .dept-show-container {
        padding: 16px;
        gap: 16px;
    }

    .dept-tab-body {
        padding: 16px;
    }
}
</style>
