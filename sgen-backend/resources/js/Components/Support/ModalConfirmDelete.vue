<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseConfirmModal from '@/Components/UI/BaseConfirmModal.vue';

interface Props {
    isOpen: boolean;
    ticketId: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const isDeleting = ref(false);

const handleConfirm = () => {
    if (!props.ticketId || isDeleting.value) return;

    isDeleting.value = true;
    router.delete(`/soportes/${props.ticketId}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            emit('close');
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <BaseConfirmModal
        :is-open="isOpen"
        title="Eliminar Ticket"
        :item-name="ticketId ? `#T-${ticketId}` : ''"
        message="Esta acción no se puede deshacer."
        confirm-label="Eliminar Ticket"
        variant="danger"
        :loading="isDeleting"
        @close="emit('close')"
        @confirm="handleConfirm"
    />
</template>
