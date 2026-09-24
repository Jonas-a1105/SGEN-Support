<script setup lang="ts">
import type { CategoryItem } from '@/Composables/useCategoryFilters';
import BaseConfirmModal from '@/Components/UI/BaseConfirmModal.vue';

const props = defineProps<{
    show: boolean;
    category?: CategoryItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm', id: number): void;
}>();

function handleConfirm() {
    if (props.category) {
        emit('confirm', props.category.id);
    }
}
</script>

<template>
    <BaseConfirmModal
        :is-open="show && !!category"
        title="Eliminar Categoría"
        :item-name="category ? `la categoría ${category.nombre}` : ''"
        message="Los tickets asociados mantendrán su histórico pero no podrán clasificarse bajo este concepto en nuevos reportes."
        confirm-label="Confirmar Eliminación"
        variant="danger"
        @close="emit('close')"
        @confirm="handleConfirm"
    />
</template>
