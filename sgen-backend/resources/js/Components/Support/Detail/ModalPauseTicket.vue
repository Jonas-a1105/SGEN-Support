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

const predefinedReasons = [
    'En espera de repuestos / materiales del almacén',
    'En espera de respuesta o validación del usuario solicitante',
    'Equipo enviado a revisión de garantía / proveedor externo',
    'En espera de ventana de mantenimiento fuera de horario laboral',
    'Otro motivo técnico',
];

const form = reactive({
    preset: predefinedReasons[0],
    customMotivo: '',
    isSubmitting: false,
    errorMessage: '',
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            form.preset = predefinedReasons[0];
            form.customMotivo = '';
            form.isSubmitting = false;
            form.errorMessage = '';
        }
    }
);

const handleSubmit = () => {
    const finalReason = form.preset === 'Otro motivo técnico'
        ? form.customMotivo.trim()
        : form.customMotivo.trim()
            ? `${form.preset}: ${form.customMotivo.trim()}`
            : form.preset;

    if (!finalReason || form.isSubmitting) return;

    form.isSubmitting = true;
    form.errorMessage = '';

    router.post(
        `/soportes/${props.ticketId}/pausar`,
        { motivo: finalReason },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.isSubmitting = false;
                emit('close');
            },
            onError: (errors) => {
                form.isSubmitting = false;
                form.errorMessage = Object.values(errors).flat().join(', ') || 'Error al pausar el ticket.';
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Pausar Atención del Ticket (En Espera)" max-width="md" @close="emit('close')">
        <form class="pause-form" @submit.prevent="handleSubmit">
            <p class="modal-description">
                El estado pasará a <strong>En Espera</strong> y el cronómetro de tiempo de atención se detendrá hasta que se reanude.
            </p>

            <div v-if="form.errorMessage" class="error-banner">
                {{ form.errorMessage }}
            </div>

            <div class="form-group">
                <label class="form-label" for="pauseReasonSelect">Motivo de la Pausa *</label>
                <select
                    id="pauseReasonSelect"
                    v-model="form.preset"
                    class="form-select"
                    required
                >
                    <option v-for="r in predefinedReasons" :key="r" :value="r">
                        {{ r }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="pauseCustomNotes">
                    Detalle o justificación adicional {{ form.preset === 'Otro motivo técnico' ? '*' : '(opcional)' }}
                </label>
                <textarea
                    id="pauseCustomNotes"
                    v-model="form.customMotivo"
                    rows="3"
                    class="form-textarea"
                    :placeholder="form.preset === 'Otro motivo técnico' ? 'Escribe el motivo detallado de la pausa...' : 'Notas adicionales sobre la pausa...'"
                    :required="form.preset === 'Otro motivo técnico'"
                ></textarea>
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="warning"
                    type="submit"
                    :loading="form.isSubmitting"
                    :disabled="form.isSubmitting || (form.preset === 'Otro motivo técnico' && !form.customMotivo.trim())"
                >
                    Pausar Ticket
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.pause-form {
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
.form-select,
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
.form-select {
    height: 42px;
}
.form-select:focus,
.form-textarea:focus {
    border-color: var(--primary);
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
