<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton, BaseEmptyState } from '@/Components/UI';
import EmployeeKpiRow, { type EmployeeKpis } from '@/Components/Employee/EmployeeKpiRow.vue';
import EmployeeToolbar, { type DeptFilterOption } from '@/Components/Employee/EmployeeToolbar.vue';
import EmployeeCard from '@/Components/Employee/EmployeeCard.vue';
import EmployeeTable from '@/Components/Employee/EmployeeTable.vue';
import EmployeeFormView, { type DepartmentSelectOption, type UserSelectOption } from '@/Components/Employee/EmployeeFormView.vue';
import ModalEmployeeDelete from '@/Components/Employee/ModalEmployeeDelete.vue';
import { useEmployeeFilters, type EmployeeItem } from '@/Composables/useEmployeeFilters';

const props = defineProps<{
    kpis: EmployeeKpis;
    empleados: EmployeeItem[];
    options: {
        departments: (DepartmentSelectOption & DeptFilterOption)[];
        users: UserSelectOption[];
    };
    filters?: Record<string, string>;
}>();

const rawEmployees = toRef(props, 'empleados');

const {
    currentDeptFilter,
    currentKpiFilter,
    searchQuery,
    isDense,
    activeViewMode,
    filteredEmployees,
    setDeptFilter,
    setKpiFilter,
    setViewMode,
    toggleDense,
} = useEmployeeFilters(rawEmployees);

// Modos de vista: 'directory' | 'form'
const currentSection = ref<'directory' | 'form'>('directory');
const editingEmployee = ref<EmployeeItem | null>(null);

const showDeleteModal = ref(false);
const deletingEmployee = ref<EmployeeItem | null>(null);

const openCreateForm = () => {
    editingEmployee.value = null;
    currentSection.value = 'form';
};

const openEditForm = (emp: EmployeeItem) => {
    editingEmployee.value = emp;
    currentSection.value = 'form';
};

const backToDirectory = () => {
    editingEmployee.value = null;
    currentSection.value = 'directory';
};

const openDeleteModal = (emp: EmployeeItem) => {
    deletingEmployee.value = emp;
    showDeleteModal.value = true;
};

const handleSaveEmployee = (payload: Record<string, unknown>) => {
    // Inertia tipa sus payloads como Record<string, any>: casteo explícito en el límite de IO.
    const body = payload as Record<string, any>;
    if (editingEmployee.value) {
        router.put(`/personal/${editingEmployee.value.numericId}`, body, {
            onSuccess: () => {
                backToDirectory();
            },
            onError: (errors) => {
                const firstErr = Object.values(errors)[0] || 'Error al actualizar la ficha del empleado.';
                alert(firstErr);
            },
        });
    } else {
        router.post('/personal', body, {
            onSuccess: () => {
                backToDirectory();
            },
            onError: (errors) => {
                const firstErr = Object.values(errors)[0] || 'Error al registrar el empleado.';
                alert(firstErr);
            },
        });
    }
};

const handleConfirmDelete = () => {
    if (!deletingEmployee.value) return;

    router.delete(`/personal/${deletingEmployee.value.numericId}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingEmployee.value = null;
        },
        onError: (errors) => {
            const firstErr = Object.values(errors)[0] || 'Error al eliminar el empleado.';
            alert(firstErr);
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Directorio de Personal" />

        <div class="employee-module-page">
            <!-- VISTA 1: DIRECTORIO DE PERSONAL -->
            <div v-if="currentSection === 'directory'" class="directory-section">
                <!-- CABECERA DEL MÓDULO -->
                <BasePageHeader
                    title="Directorio de Personal"
                    subtitle="Gestiona el talento humano de la organización."
                >
                    <template #icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </template>
                    <template #actions>
                        <BaseButton variant="primary" @click="openCreateForm">
                            + Nuevo Empleado
                        </BaseButton>
                    </template>
                </BasePageHeader>

                <!-- KPIS ROW -->
                <EmployeeKpiRow :kpis="kpis" />

                <!-- TOOLBAR -->
                <EmployeeToolbar
                    :current-dept="currentDeptFilter"
                    :search-query="searchQuery"
                    :view-mode="activeViewMode"
                    :is-dense="isDense"
                    :departments="options.departments"
                    @update:current-dept="setDeptFilter"
                    @update:search-query="searchQuery = $event"
                    @update:view-mode="setViewMode"
                    @toggle-dense="toggleDense"
                />

                <!-- GRID VIEW -->
                <section
                    v-if="activeViewMode === 'grid' && filteredEmployees.length > 0"
                    class="employee-cards-grid"
                    :class="{ dense: isDense }"
                >
                    <EmployeeCard
                        v-for="emp in filteredEmployees"
                        :key="emp.numericId"
                        :employee="emp"
                        :is-dense="isDense"
                        @edit="openEditForm"
                        @delete="openDeleteModal"
                    />
                </section>

                <!-- TABLE VIEW -->
                <EmployeeTable
                    v-else-if="activeViewMode === 'table' && filteredEmployees.length > 0"
                    :employees="filteredEmployees"
                    @edit="openEditForm"
                    @delete="openDeleteModal"
                />

                <!-- EMPTY STATE -->
                <BaseEmptyState
                    v-else
                    title="No se encontraron empleados"
                    subtitle="Prueba con otros términos de búsqueda o selecciona otro departamento."
                />
            </div>

            <!-- VISTA 2: FORMULARIO DE REGISTRO / EDICIÓN CON PREVIEW EN VIVO -->
            <EmployeeFormView
                v-else-if="currentSection === 'form'"
                :edit-employee="editingEmployee"
                :departments="options.departments"
                :users="options.users"
                @back="backToDirectory"
                @save="handleSaveEmployee"
            />

            <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
            <ModalEmployeeDelete
                :show="showDeleteModal"
                :employee="deletingEmployee"
                @close="showDeleteModal = false"
                @confirm="handleConfirmDelete"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.employee-module-page {
    padding-bottom: 24px;
}

.directory-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.employee-cards-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.employee-cards-grid.dense {
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}

@media (max-width: 1200px) {
    .employee-cards-grid,
    .employee-cards-grid.dense {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .employee-cards-grid,
    .employee-cards-grid.dense {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 560px) {
    .employee-cards-grid,
    .employee-cards-grid.dense {
        grid-template-columns: 1fr;
    }
}
</style>
