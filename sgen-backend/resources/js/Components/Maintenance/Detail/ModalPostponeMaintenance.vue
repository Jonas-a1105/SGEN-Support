<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput } from '@/Components/UI';
import type { MaintenanceDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    maintenance: MaintenanceDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const postponeForm = ref({
    nueva_fecha: '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            postponeForm.value = {
                nueva_fecha: '',
            };
        }
    }
);

const handlePostpone = () => {
    if (!postponeForm.value.nueva_fecha) return;
    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenance.id}/posponer`,
        {
            nueva_fecha: postponeForm.value.nueva_fecha,
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
        title="Posponer Orden de Mantenimiento"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handlePostpone" class="modal-form-stack">
            <div class="modal-intro-text">
                Indica la nueva fecha y hora para la ejecución del servicio técnico sobre el equipo <strong>{{ maintenance.equipo?.codigo }}</strong>.
            </div>

            <div class="form-group">
                <label class="form-label">Nueva Fecha y Hora Programada *</label>
                <BaseInput
                    v-model="postponeForm.nueva_fecha"
                    type="datetime-local"
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
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="warning"
                    size="md"
                    type="submit"
                    :disabled="isSubmitting || !postponeForm.nueva_fecha"
                >
                    Posponer Servicio
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
