<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseTextarea } from '@/Components/UI';
import type { MaintenanceDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    maintenance: MaintenanceDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const cancelForm = ref({
    motivo: '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            cancelForm.value = {
                motivo: '',
            };
        }
    }
);

const handleCancel = () => {
    if (!cancelForm.value.motivo) return;
    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenance.id}/cancelar`,
        {
            motivo: cancelForm.value.motivo,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
                emit('close');
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Cancelar Orden de Mantenimiento"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleCancel" class="modal-form-stack">
            <div class="modal-intro-text danger">
                ¿Estás seguro de que deseas cancelar este mantenimiento? Esta orden quedará anulada y no podrá ser ejecutada.
            </div>

            <div class="form-group">
                <label class="form-label">Motivo de Cancelación *</label>
                <BaseTextarea
                    v-model="cancelForm.motivo"
                    placeholder="Indica el motivo por el cual se anula la orden..."
                    :rows="3"
                    required
                />
            </div>

            <div class="modal-actions-footer">
                <BaseButton
                    variant="subtle"
                    size="md"
                    type="button"
                    @click="emit('close')"
                >
                    Volver
                </BaseButton>
                <BaseButton
                    variant="danger"
                    size="md"
                    type="submit"
                    :disabled="isSubmitting || !cancelForm.motivo"
                >
                    Confirmar Cancelación
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.modal-intro-text {
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
    padding: 12px;
    background: var(--stroke-subtle);
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
}

.modal-intro-text.danger {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}

.modal-actions-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
