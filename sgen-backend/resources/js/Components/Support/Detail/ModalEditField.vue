<script setup lang="ts">
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

interface Props {
    isOpen: boolean;
    ticketId: number;
    initialStatus?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = reactive({
    estado: props.initialStatus || 'resuelto',
    isSubmitting: false,
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            form.estado = props.initialStatus || 'resuelto';
            form.isSubmitting = false;
        }
    }
);

const handleSubmit = () => {
    if (form.isSubmitting) return;

    form.isSubmitting = true;
    router.put(
        `/soportes/${props.ticketId}`,
        {
            estado: form.estado,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.isSubmitting = false;
                emit('close');
            },
            onError: () => {
                form.isSubmitting = false;
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Modificar Estado / Cierre" max-width="sm" @close="emit('close')">
        <form class="edit-field-form" @submit.prevent="handleSubmit">
            <div class="form-group">
                <label class="form-label" for="editStatusSelect">Estado del Ticket</label>
                <select id="editStatusSelect" v-model="form.estado" class="form-select">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="resuelto">Resuelto (Registra fecha y hora de cierre)</option>
                </select>
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton variant="primary" type="submit" :disabled="form.isSubmitting">
                    {{ form.isSubmitting ? 'Guardando...' : 'Actualizar' }}
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.edit-field-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
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
.form-select {
    width: 100%;
    height: 40px;
    padding: 8px 12px;
    border-radius: 10px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
}
.form-select:focus {
    border-color: var(--primary);
}
.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
