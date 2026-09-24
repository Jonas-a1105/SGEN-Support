<script setup lang="ts">
import type { EmployeeItem } from '@/Composables/useEmployeeFilters';
import BaseConfirmModal from '@/Components/UI/BaseConfirmModal.vue';

defineProps<{
    show: boolean;
    employee: EmployeeItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <BaseConfirmModal
        :is-open="show && !!employee"
        title="Eliminar Registro de Personal"
        :item-name="employee ? `${employee.fullName} (${employee.id})` : ''"
        message="Esta acción cancelará cualquier vinculación a tickets o inventario que tenga asociada."
        confirm-label="Eliminar Empleado"
        variant="danger"
        @close="emit('close')"
        @confirm="emit('confirm')"
    />
</template>
