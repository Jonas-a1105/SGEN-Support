<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton, BaseEmptyState } from '@/Components/UI';
import EquipmentKpiRow, { type EquipmentKpis } from '@/Components/Equipment/EquipmentKpiRow.vue';
import EquipmentToolbar from '@/Components/Equipment/EquipmentToolbar.vue';
import EquipmentCard from '@/Components/Equipment/EquipmentCard.vue';
import EquipmentTable from '@/Components/Equipment/EquipmentTable.vue';
import ModalEquipmentForm, { type DepartmentOption, type EmployeeOption } from '@/Components/Equipment/ModalEquipmentForm.vue';
import ModalEquipmentDetail from '@/Components/Equipment/ModalEquipmentDetail.vue';
import ModalEquipmentDelete from '@/Components/Equipment/ModalEquipmentDelete.vue';
import { useEquipmentFilters, type EquipmentItem } from '@/Composables/useEquipmentFilters';

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
    searchQuery,
    isDense,
    activeViewMode,
    filteredEquipos,
    setPillFilter,
    setViewMode,
    toggleDense,
} = useEquipmentFilters(rawEquipos);

// Modales
const showFormModal = ref(false);
const editingEquipment = ref<EquipmentItem | null>(null);

const showDetailModal = ref(false);
const detailEquipment = ref<EquipmentItem | null>(null);

const showDeleteModal = ref(false);
const deletingEquipment = ref<EquipmentItem | null>(null);

const openCreateModal = () => {
    editingEquipment.value = null;
    showFormModal.value = true;
};

const openEditModal = (item: EquipmentItem) => {
    editingEquipment.value = item;
    showFormModal.value = true;
};

const openDetailModal = (item: EquipmentItem) => {
    detailEquipment.value = item;
    showDetailModal.value = true;
};

const openDeleteModal = (item: EquipmentItem) => {
    deletingEquipment.value = item;
    showDeleteModal.value = true;
};

const handleSaveEquipment = (payload: Record<string, unknown>) => {
    if (editingEquipment.value) {
        router.put(`/equipos/${editingEquipment.value.numericId}`, payload, {
            onSuccess: () => {
                showFormModal.value = false;
                editingEquipment.value = null;
            },
        });
    } else {
        router.post('/equipos', payload, {
            onSuccess: () => {
                showFormModal.value = false;
            },
        });
    }
};

const handleConfirmDelete = () => {
    if (!deletingEquipment.value) return;

    router.delete(`/equipos/${deletingEquipment.value.numericId}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingEquipment.value = null;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Gestión de Equipos Tecnológicos" />

        <div class="equipment-module-page">
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
                    <BaseButton variant="primary" @click="openCreateModal">
                        + Nuevo Equipo
                    </BaseButton>
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
                @update:current-pill="setPillFilter"
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
                />
            </section>

            <!-- TABLE VIEW -->
            <EquipmentTable
                v-else-if="activeViewMode === 'table' && filteredEquipos.length > 0"
                :equipos="filteredEquipos"
                @view="openDetailModal"
                @edit="openEditModal"
                @delete="openDeleteModal"
            />

            <!-- EMPTY STATE -->
            <BaseEmptyState
                v-else
                title="No se encontraron equipos"
                subtitle="Prueba con otros términos de búsqueda o cambia los filtros seleccionados."
            />

            <!-- MODALES -->
            <ModalEquipmentForm
                :show="showFormModal"
                :edit-equipment="editingEquipment"
                :departments="options.departments"
                :employees="options.employees"
                @close="showFormModal = false"
                @save="handleSaveEquipment"
            />

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
