<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton, BaseEmptyState } from '@/Components/UI';
import EquipmentKpiRow from '@/Components/Equipment/EquipmentKpiRow.vue';
import EquipmentToolbar from '@/Components/Equipment/EquipmentToolbar.vue';
import EquipmentCard from '@/Components/Equipment/EquipmentCard.vue';
import EquipmentTable from '@/Components/Equipment/EquipmentTable.vue';
import ViewEditEquipmentWizard from '@/Components/Inventory/ViewEditEquipmentWizard.vue';
import ModalEquipmentDetail from '@/Components/Equipment/ModalEquipmentDetail.vue';
import ModalEquipmentDelete from '@/Components/Equipment/ModalEquipmentDelete.vue';
import ModalDecommissionEquipment from '@/Components/Equipment/Detail/ModalDecommissionEquipment.vue';
import { useEquipmentFilters } from '@/Composables/useEquipmentFilters';
import { usePermissions } from '@/Composables/usePermissions';
import { useToast } from '@/Composables/useToast';
import type { EquipmentItem, EquipmentKpis, DepartmentOption, EmployeeOption } from '@/Types';

const props = defineProps<{
    kpis: EquipmentKpis;
    equipos: EquipmentItem[];
    options: {
        departments: DepartmentOption[];
        employees: EmployeeOption[];
    };
    filters?: Record<string, string>;
}>();

const rawEquipos = toRef(props, 'equipos');

const {
    currentPillFilter,
    selectedDepartment,
    searchQuery,
    isDense,
    activeViewMode,
    filteredEquipos,
    setPillFilter,
    setDepartmentFilter,
    setViewMode,
    toggleDense,
} = useEquipmentFilters(rawEquipos);

// Formulario canónico (wizard, mismo diseño de Inventario): vista a pantalla completa
// en lugar de modal. Edición por id ligero: la ficha completa se carga desde el detalle.
const showFormView = ref(false);
const formEquipmentId = ref<number | null>(null);
const { addToast } = useToast();
const { can } = usePermissions();
const editingEquipment = ref<EquipmentItem | null>(null);

const showDetailModal = ref(false);
const detailEquipment = ref<EquipmentItem | null>(null);

const showDeleteModal = ref(false);
const deletingEquipment = ref<EquipmentItem | null>(null);
// Baja patrimonial formal (modo legal, sustituye la eliminación para activos con historia).
const showDecommissionModal = ref(false);
const decommissioningEquipment = ref<EquipmentItem | null>(null);

const openCreateModal = () => {
    editingEquipment.value = null;
    formEquipmentId.value = null;
    showFormView.value = true;
};

const openEditModal = (item: EquipmentItem) => {
    editingEquipment.value = item; // contexto para el mensaje; la data llega vía detalle
    formEquipmentId.value = item.numericId;
    showFormView.value = true;
};

const closeFormView = () => {
    showFormView.value = false;
    formEquipmentId.value = null;
    editingEquipment.value = null;
};

const openDetailModal = (item: EquipmentItem) => {
    detailEquipment.value = item;
    showDetailModal.value = true;
};

const openDeleteModal = (item: EquipmentItem) => {
    deletingEquipment.value = item;
    showDeleteModal.value = true;
};

const openDecommissionModal = (item: EquipmentItem) => {
    decommissioningEquipment.value = item;
    showDecommissionModal.value = true;
};



const handleConfirmDelete = () => {
    if (!deletingEquipment.value) return;

    router.delete(`/equipos/${deletingEquipment.value.numericId}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingEquipment.value = null;
        },
        onError: (errors) => {
            const firstErr = Object.values(errors)[0] || 'Error al eliminar el equipo.';
            addToast({ type: 'error', title: firstErr });
        },
    });
};

const handleExportExcel = () => {
    window.open('/equipos/export/excel', '_blank');
};
</script>

<template>
    <AppLayout>
        <Head title="Gestión de Equipos Tecnológicos" />

        <ViewEditEquipmentWizard
            v-if="showFormView"
            :equipment="null"
            :equipment-id="formEquipmentId"
            :departamentos="options.departments"
            :empleados="(options.employees as unknown as import('@/Types/inventory').Employee[])"
            @back="closeFormView"
            @saved="closeFormView"
        />

        <div v-else class="equipment-module-page">
            <!-- CABECERA DEL MÓDULO -->
            <BasePageHeader
                title="Gestión de Equipos Tecnológicos"
                subtitle="Administra y supervisa los activos informáticos y tecnológicos."
            >
                <template #icon>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </template>
                <template #actions>
                    <div class="equipment-header-actions">
                        <BaseButton v-if="can('equipos.view')" variant="secondary" @click="handleExportExcel">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="action-btn-icon">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span>Exportar Excel</span>
                        </BaseButton>
                        <BaseButton v-if="can('equipos.manage')" variant="primary" @click="openCreateModal">
                            + Nuevo Equipo
                        </BaseButton>
                    </div>
                </template>
            </BasePageHeader>

            <!-- KPIS ROW -->
            <EquipmentKpiRow :kpis="kpis" />

            <!-- TOOLBAR -->
            <EquipmentToolbar
                :current-pill="currentPillFilter"
                :search-query="searchQuery"
                :view-mode="activeViewMode"
                :is-dense="isDense"
                :departments="options.departments"
                :selected-department="selectedDepartment"
                @update:current-pill="setPillFilter"
                @update:selected-department="setDepartmentFilter"
                @update:search-query="searchQuery = $event"
                @update:view-mode="setViewMode"
                @toggle-dense="toggleDense"
            />

            <!-- GRID VIEW -->
            <section
                v-if="activeViewMode === 'grid' && filteredEquipos.length > 0"
                class="equipment-cards-grid"
                :class="{ dense: isDense }"
            >
                <EquipmentCard
                    v-for="item in filteredEquipos"
                    :key="item.numericId"
                    :item="item"
                    :is-dense="isDense"
                    @view="openDetailModal"
                    @edit="openEditModal"
                    @delete="openDeleteModal"
                    @decommission="openDecommissionModal"
                />
            </section>

            <!-- TABLE VIEW -->
            <EquipmentTable
                v-else-if="activeViewMode === 'table' && filteredEquipos.length > 0"
                :equipos="filteredEquipos"
                @view="openDetailModal"
                @edit="openEditModal"
                @delete="openDeleteModal"
                @decommission="openDecommissionModal"
            />

            <!-- EMPTY STATE -->
            <BaseEmptyState
                v-else
                title="No se encontraron equipos"
                subtitle="Prueba con otros términos de búsqueda o cambia los filtros seleccionados."
            />

            <!-- MODALES -->
            <ModalEquipmentDetail
                :show="showDetailModal"
                :item="detailEquipment"
                @close="showDetailModal = false"
            />

            <ModalEquipmentDelete
                :show="showDeleteModal"
                :item="deletingEquipment"
                @close="showDeleteModal = false"
                @confirm="handleConfirmDelete"
            />

            <!-- Baja patrimonial formal: sustituye la eliminación para activos
                 con historia custodial u operativa (acta con hash verificable). -->
            <ModalDecommissionEquipment
                v-if="decommissioningEquipment"
                :is-open="showDecommissionModal"
                :equipment-id="decommissioningEquipment.numericId"
                :equipment-code="decommissioningEquipment.name"
                @close="showDecommissionModal = false; decommissioningEquipment = null"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.equipment-module-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-bottom: 24px;
}

.equipment-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.action-btn-icon {
    width: 15px;
    height: 15px;
    margin-right: 6px;
    display: inline-block;
    vertical-align: middle;
}

.equipment-cards-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.equipment-cards-grid.dense {
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}

@media (max-width: 1200px) {
    .equipment-cards-grid,
    .equipment-cards-grid.dense {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .equipment-cards-grid,
    .equipment-cards-grid.dense {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 560px) {
    .equipment-cards-grid,
    .equipment-cards-grid.dense {
        grid-template-columns: 1fr;
    }
}
</style>
