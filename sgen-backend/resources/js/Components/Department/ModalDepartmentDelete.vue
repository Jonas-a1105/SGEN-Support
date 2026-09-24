<script setup lang="ts">
import type { DepartmentItem } from '@/Composables/useDepartmentFilters';
import BaseConfirmModal from '@/Components/UI/BaseConfirmModal.vue';

defineProps<{
    show: boolean;
    department: DepartmentItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <BaseConfirmModal
        :is-open="show && !!department"
        title="Eliminar Departamento"
        :item-name="department ? `${department.name} (${department.code})` : ''"
        message="Los equipos y empleados asignados a esta unidad quedarán desvinculados."
        confirm-label="Eliminar Departamento"
        variant="danger"
        @close="emit('close')"
        @confirm="emit('confirm')"
    />
</template>
