<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseConfirmModal, BaseKpiCard, BaseTabs, type TabItem } from '@/Components/UI';
import DepartmentDetailHeader from '@/Components/Department/Detail/DepartmentDetailHeader.vue';
import TabDepartmentOverview from '@/Components/Department/Detail/TabDepartmentOverview.vue';
import TabDepartmentEmployees from '@/Components/Department/Detail/TabDepartmentEmployees.vue';
import TabDepartmentAssets from '@/Components/Department/Detail/TabDepartmentAssets.vue';
import TabDepartmentInventory from '@/Components/Department/Detail/TabDepartmentInventory.vue';
import ModalAssignEmployee from '@/Components/Department/Detail/ModalAssignEmployee.vue';
import ModalAssignEquipment from '@/Components/Department/Detail/ModalAssignEquipment.vue';
import ModalEditDepartment from '@/Components/Department/Detail/ModalEditDepartment.vue';
import { IconUsers, IconDevices, IconPackages, IconTicket } from '@tabler/icons-vue';
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

const activeTab = ref<string>('overview');

const tabs = computed<TabItem[]>(() => [
    { key: 'overview', label: 'Visión General' },
    { key: 'employees', label: 'Colaboradores', count: props.department.empleados?.length },
    { key: 'assets', label: 'Equipos & Activos', count: props.department.equipos?.length },
    { key: 'inventory', label: 'Artículos Inventario', count: props.department.consumables?.length },
]);

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
                    color="blue"
                >
                    <template #icon>
                        <IconUsers :size="22" stroke-width="2" />
                    </template>
                </BaseKpiCard>
                <BaseKpiCard
                    label="EQUIPOS ASIGNADOS"
                    :value="department.equiposCount"
                    subtext="Activos en uso"
                    color="cyan"
                >
                    <template #icon>
                        <IconDevices :size="22" stroke-width="2" />
                    </template>
                </BaseKpiCard>
                <BaseKpiCard
                    label="ARTÍCULOS INVENTARIO"
                    :value="department.consumablesCount"
                    subtext="Materiales en stock"
                    color="orange"
                >
                    <template #icon>
                        <IconPackages :size="22" stroke-width="2" />
                    </template>
                </BaseKpiCard>
                <BaseKpiCard
                    label="TICKETS REGISTRADOS"
                    :value="department.ticketsCount"
                    subtext="Casos generados"
                    color="green"
                >
                    <template #icon>
                        <IconTicket :size="22" stroke-width="2" />
                    </template>
                </BaseKpiCard>
            </section>

            <!-- Main Panel with Tabs -->
            <main class="dept-main-panel">
                <div class="dept-panel-wrapper">
                    <!-- Navigation Tabs Bar using BaseTabs -->
                    <div class="dept-tabs-bar">
                        <BaseTabs v-model="activeTab" :tabs="tabs" />
                    </div>

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
                            :consumibles="department.consumables"
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

.dept-tabs-bar {
    padding: var(--space-3) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
}

.dept-tab-body {
    padding: 24px;
}

@media (max-width: 1024px) {
    .dept-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
}

@media (max-width: 640px) {
    .dept-show-container {
        gap: 16px;
    }

    .dept-tab-body {
        padding: 16px;
    }
}
</style>
