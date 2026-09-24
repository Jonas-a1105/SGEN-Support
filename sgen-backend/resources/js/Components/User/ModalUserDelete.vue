<script setup lang="ts">
import type { UserItem } from '@/Composables/useUserFilters';
import BaseConfirmModal from '@/Components/UI/BaseConfirmModal.vue';

const props = defineProps<{
    show: boolean;
    user?: UserItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm', id: number): void;
}>();

function handleConfirm() {
    if (props.user) {
        emit('confirm', props.user.id);
    }
}
</script>

<template>
    <BaseConfirmModal
        :is-open="show && !!user"
        title="Eliminar Usuario"
        :item-name="user ? `la cuenta de usuario ${user.username}` : ''"
        message="Se revocarán todos los accesos al sistema inmediatamente."
        confirm-label="Confirmar Eliminación"
        variant="danger"
        @close="emit('close')"
        @confirm="handleConfirm"
    />
</template>
