<script setup lang="ts">
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

interface Props {
    isOpen: boolean;
    ticketId: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = reactive({
    motivo: '',
    isSubmitting: false,
    errorMessage: '',
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            form.motivo = '';
            form.isSubmitting = false;
            form.errorMessage = '';
        }
    }
);

const handleSubmit = () => {
    if (!form.motivo.trim() || form.motivo.trim().length < 10 || form.isSubmitting) {
        return;
    }

    form.isSubmitting = true;
    form.errorMessage = '';

    router.post(
        `/soportes/${props.ticketId}/reabrir`,
        { motivo: form.motivo.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.isSubmitting = false;
                emit('close');
            },
            onError: (errors) => {
                form.isSubmitting = false;
                form.errorMessage = Object.values(errors).flat().join(', ') || 'Error al reabrir el ticket.';
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Reabrir Ticket de Soporte" max-width="md" @close="emit('close')">
        <form class="reopen-form" @submit.prevent="handleSubmit">
            <p class="modal-description">
                Por favor explica por qué requieres reabrir este ticket. El estado volverá a <strong>En Proceso</strong>, se notificará al técnico asignado y se registrará en el historial de atención.
            </p>

            <div v-if="form.errorMessage" class="error-banner">
                {{ form.errorMessage }}
            </div>

            <div class="form-group">
                <label class="form-label" for="ticketReopenMotive">Motivo de Reapertura *</label>
                <textarea
                    id="ticketReopenMotive"
                    v-model="form.motivo"
                    rows="4"
                    class="form-textarea"
                    placeholder="Explica detalladamente por qué el requerimiento no quedó solucionado (mínimo 10 caracteres)..."
                    required
                ></textarea>
                <span v-if="form.motivo.trim().length > 0 && form.motivo.trim().length < 10" class="field-hint">
                    Mínimo 10 caracteres (actualmente: {{ form.motivo.trim().length }}).
                </span>
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="primary"
                    type="submit"
                    :loading="form.isSubmitting"
                    :disabled="form.isSubmitting || form.motivo.trim().length < 10"
                >
                    Confirmar y Reabrir
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.reopen-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.modal-description {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.45;
}
.error-banner {
    padding: 10px 14px;
    border-radius: 8px;
    background: rgba(220, 53, 69, 0.12);
    border: 1px solid var(--danger);
    color: var(--danger);
    font-size: 13px;
    font-weight: 500;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.form-textarea {
    width: 100%;
    padding: 8px 12px;
    border-radius: 10px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    font-family: inherit;
}
.form-textarea:focus {
    border-color: var(--primary);
}
.field-hint {
    font-size: 12px;
    color: var(--warning);
    font-weight: 500;
}
.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
