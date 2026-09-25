<script setup lang="ts">
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseSignaturePad from '@/Components/UI/BaseSignaturePad.vue';

interface Props {
    isOpen: boolean;
    ticketId: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = reactive({
    solucion: '',
    firma_base64: '' as string | null,
    isSubmitting: false,
    errorMessage: '',
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            form.solucion = '';
            form.firma_base64 = null;
            form.isSubmitting = false;
            form.errorMessage = '';
        }
    }
);

const handleSubmit = () => {
    if (!form.solucion.trim() || form.solucion.trim().length < 10 || form.isSubmitting) {
        return;
    }

    form.isSubmitting = true;
    form.errorMessage = '';

    router.put(
        `/soportes/${props.ticketId}`,
        {
            estado: 'resuelto',
            solucion: form.solucion.trim(),
            firma_base64: form.firma_base64 || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.isSubmitting = false;
                emit('close');
            },
            onError: (errors) => {
                form.isSubmitting = false;
                form.errorMessage = Object.values(errors).flat().join(', ') || 'Error al resolver el ticket.';
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Resolver Ticket de Soporte" max-width="lg" @close="emit('close')">
        <form class="resolve-form" @submit.prevent="handleSubmit">
            <p class="modal-description">
                Describe detalladamente la solución técnica o el procedimiento aplicado para resolver el requerimiento. El ticket quedará marcado como <strong>Resuelto</strong> y se registrará la fecha y hora de cierre.
            </p>

            <div v-if="form.errorMessage" class="error-banner">
                {{ form.errorMessage }}
            </div>

            <div class="form-group">
                <label class="form-label" for="ticketSolutionText">Diagnóstico y Solución Técnica *</label>
                <textarea
                    id="ticketSolutionText"
                    v-model="form.solucion"
                    rows="3"
                    class="form-textarea"
                    placeholder="Detalla los pasos realizados para solucionar la falla (mínimo 10 caracteres)..."
                    required
                ></textarea>
                <span v-if="form.solucion.trim().length > 0 && form.solucion.trim().length < 10" class="field-hint">
                    Mínimo 10 caracteres (actualmente: {{ form.solucion.trim().length }}).
                </span>
            </div>

            <div class="form-group">
                <BaseSignaturePad
                    v-model="form.firma_base64"
                    :height="150"
                    label="Firma de Conformidad del Solicitante (Opcional)"
                    helperText="Firma digitalizada de quien recibe el servicio para constancia de entrega."
                />
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="primary"
                    type="submit"
                    :loading="form.isSubmitting"
                    :disabled="form.isSubmitting || form.solucion.trim().length < 10"
                >
                    Guardar y Resolver
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.resolve-form {
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
