<script setup lang="ts">
import type { EquipmentItem } from '@/Composables/useEquipmentFilters';
import BaseConfirmModal from '@/Components/UI/BaseConfirmModal.vue';

defineProps<{
    show: boolean;
    item: EquipmentItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <BaseConfirmModal
        :is-open="show && !!item"
        title="Eliminar Activo Tecnológico"
        :item-name="item ? `${item.name} (ID: ${item.id})` : ''"
        message="Esta acción retirará el activo de los inventarios y desvinculará sus registros asociados."
        confirm-label="Eliminar Equipo"
        variant="danger"
        @close="emit('close')"
        @confirm="emit('confirm')"
    />
</template>
