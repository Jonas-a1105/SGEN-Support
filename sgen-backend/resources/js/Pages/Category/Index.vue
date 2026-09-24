<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseButton, BasePageHeader } from '@/Components/UI';
import { useCategoryFilters, type CategoryItem } from '@/Composables/useCategoryFilters';
import CategoryKpiRow, { type CategoryKpis } from '@/Components/Category/CategoryKpiRow.vue';
import CategoryToolbar from '@/Components/Category/CategoryToolbar.vue';
import CategoryTable from '@/Components/Category/CategoryTable.vue';
import ModalCategoryForm from '@/Components/Category/ModalCategoryForm.vue';
import ModalCategoryDelete from '@/Components/Category/ModalCategoryDelete.vue';

const props = defineProps<{
    categories: CategoryItem[];
    kpis: CategoryKpis;
    filters?: {
        search?: string;
    };
}>();

const categoriesRef = toRef(props, 'categories');
const { search, filteredCategories } = useCategoryFilters(categoriesRef);

// Form modal state
const showFormModal = ref(false);
const editingCategory = ref<CategoryItem | null>(null);

// Delete modal state
const showDeleteModal = ref(false);
const deletingCategory = ref<CategoryItem | null>(null);

function openCreateModal() {
    editingCategory.value = null;
    showFormModal.value = true;
}

function openEditModal(category: CategoryItem) {
    editingCategory.value = category;
    showFormModal.value = true;
}

function openDeleteModal(category: CategoryItem) {
    deletingCategory.value = category;
    showDeleteModal.value = true;
}

function handleSaveCategory(payload: {
    id?: number;
    nombre: string;
    descripcion: string | null;
    icono: string;
    color: string;
}) {
    if (payload.id) {
        router.put(`/categorias/${payload.id}`, payload, {
            onSuccess: () => {
                showFormModal.value = false;
            },
        });
    } else {
        router.post('/categorias', payload, {
            onSuccess: () => {
                showFormModal.value = false;
            },
        });
    }
}

function handleConfirmDelete(id: number) {
    router.delete(`/categorias/${id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Gestión de Categorías" />

        <div class="module-wrapper">
            <!-- Header -->
            <BasePageHeader
                title="Gestión de Categorías"
                subtitle="Clasificación y tipificación de incidencias para soporte y tickets."
                icon="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"
            >
                <template #actions>
                    <BaseButton variant="primary" size="md" @click="openCreateModal">
                        + Nueva Categoría
                    </BaseButton>
                </template>
            </BasePageHeader>

            <!-- KPIs -->
            <CategoryKpiRow :kpis="kpis" />

            <!-- Toolbar -->
            <CategoryToolbar
                v-model:search="search"
                :showing-count="filteredCategories.length"
                :total-count="categories.length"
            />

            <!-- Table -->
            <CategoryTable
                :categories="filteredCategories"
                @edit="openEditModal"
                @delete="openDeleteModal"
            />

            <!-- Form Modal -->
            <ModalCategoryForm
                :show="showFormModal"
                :category="editingCategory"
                @close="showFormModal = false"
                @save="handleSaveCategory"
            />

            <!-- Delete Modal -->
            <ModalCategoryDelete
                :show="showDeleteModal"
                :category="deletingCategory"
                @close="showDeleteModal = false"
                @confirm="handleConfirmDelete"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.module-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
</style>
