<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton, BaseEmptyState } from '@/Components/UI';
import DepartmentToolbar from '@/Components/Department/DepartmentToolbar.vue';
import DepartmentCard from '@/Components/Department/DepartmentCard.vue';
import DepartmentTable from '@/Components/Department/DepartmentTable.vue';
import DepartmentFormView from '@/Components/Department/DepartmentFormView.vue';
import type { LeadershipCandidate } from '@/Types/department';
import ModalDepartmentDelete from '@/Components/Department/ModalDepartmentDelete.vue';
import { useDepartmentFilters, type DepartmentItem } from '@/Composables/useDepartmentFilters';

const props = defineProps<{
    departamentos: DepartmentItem[];
    candidatos: LeadershipCandidate[];
    filters?: Record<string, string>;
}>();

const rawDepartments = toRef(props, 'departamentos');

const {
    searchQuery,
    isDense,
    activeViewMode,
    filteredDepartments,
    setViewMode,
    toggleDense,
} = useDepartmentFilters(rawDepartments);

// Secciones: 'list' | 'form'
const currentSection = ref<'list' | 'form'>('list');
const editingDepartment = ref<DepartmentItem | null>(null);

const showDeleteModal = ref(false);
const deletingDepartment = ref<DepartmentItem | null>(null);

const openCreateForm = () => {
    editingDepartment.value = null;
    currentSection.value = 'form';
};

const openEditForm = (dept: DepartmentItem) => {
    editingDepartment.value = dept;
    currentSection.value = 'form';
};

const backToList = () => {
    editingDepartment.value = null;
    currentSection.value = 'list';
};

const openDeleteModal = (dept: DepartmentItem) => {
    deletingDepartment.value = dept;
    showDeleteModal.value = true;
};

const handleSaveDepartment = (payload: Record<string, unknown>) => {
    // Inertia tipa sus payloads como Record<string, any>: casteo explícito en el límite de IO.
    const body = payload as Record<string, any>;
    if (editingDepartment.value) {
        router.put(`/departamentos/${editingDepartment.value.numericId}`, body, {
            onSuccess: () => {
                backToList();
            },
            onError: (errors) => {
                const firstErr = Object.values(errors)[0] || 'Error al actualizar el departamento.';
                alert(firstErr);
            },
        });
    } else {
        router.post('/departamentos', body, {
            onSuccess: () => {
                backToList();
            },
            onError: (errors) => {
                const firstErr = Object.values(errors)[0] || 'Error al registrar el departamento.';
                alert(firstErr);
            },
        });
    }
};

const handleConfirmDelete = () => {
    if (!deletingDepartment.value) return;

    router.delete(`/departamentos/${deletingDepartment.value.numericId}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingDepartment.value = null;
        },
        onError: (errors) => {
            const firstErr = Object.values(errors)[0] || 'Error al eliminar el departamento.';
            alert(firstErr);
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Gestión de Departamentos" />

        <div class="departments-module-page">
            <!-- VISTA 1: CATÁLOGO DE DEPARTAMENTOS -->
            <div v-if="currentSection === 'list'" class="catalog-section">
                <!-- CABECERA DEL MÓDULO -->
                <BasePageHeader
                    title="Departamentos"
                    subtitle="Gestiona las unidades y áreas funcionales de la organización."
                >
                    <template #icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="2"></line>
                            <line x1="15" y1="22" x2="15" y2="2"></line>
                            <line x1="8" y1="6" x2="16" y2="6"></line>
                            <line x1="8" y1="10" x2="16" y2="10"></line>
                            <line x1="8" y1="14" x2="16" y2="14"></line>
                        </svg>
                    </template>
                    <template #actions>
                        <BaseButton variant="primary" @click="openCreateForm">
                            + Crear Departamento
                        </BaseButton>
                    </template>
                </BasePageHeader>

                <!-- TOOLBAR -->
                <DepartmentToolbar
                    :search-query="searchQuery"
                    :view-mode="activeViewMode"
                    :is-dense="isDense"
                    @update:search-query="searchQuery = $event"
                    @update:view-mode="setViewMode"
                    @toggle-dense="toggleDense"
                />

                <!-- GRID VIEW -->
                <section
                    v-if="activeViewMode === 'grid' && filteredDepartments.length > 0"
                    class="departments-grid"
                    :class="{ dense: isDense }"
                >
                    <DepartmentCard
                        v-for="dept in filteredDepartments"
                        :key="dept.numericId"
                        :department="dept"
                        :is-dense="isDense"
                        @edit="openEditForm"
                        @delete="openDeleteModal"
                    />
                </section>

                <!-- TABLE VIEW -->
                <DepartmentTable
                    v-else-if="activeViewMode === 'table' && filteredDepartments.length > 0"
                    :departments="filteredDepartments"
                    @edit="openEditForm"
                    @delete="openDeleteModal"
                />

                <!-- EMPTY STATE -->
                <BaseEmptyState
                    v-else
                    title="No se encontraron departamentos"
                    subtitle="Prueba con otros términos de búsqueda."
                />
            </div>

            <!-- VISTA 2: FORMULARIO CREAR / EDITAR -->
            <DepartmentFormView
                v-else-if="currentSection === 'form'"
                :edit-department="editingDepartment"
                :candidates="candidatos"
                @back="backToList"
                @save="handleSaveDepartment"
            />

            <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
            <ModalDepartmentDelete
                :show="showDeleteModal"
                :department="deletingDepartment"
                @close="showDeleteModal = false"
                @confirm="handleConfirmDelete"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.departments-module-page {
    padding-bottom: 24px;
}

.catalog-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.departments-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.departments-grid.dense {
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}

@media (max-width: 1200px) {
    .departments-grid,
    .departments-grid.dense {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .departments-grid,
    .departments-grid.dense {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 560px) {
    .departments-grid,
    .departments-grid.dense {
        grid-template-columns: 1fr;
    }
}
</style>
