<script setup lang="ts">
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';

interface Props {
    isOpen: boolean;
    ticketId: number;
    initialStatus?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const statusOptions: ComboboxOption[] = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'en_proceso', label: 'En Proceso' },
    { value: 'resuelto', label: 'Resuelto', sublabel: 'Registra fecha y hora de cierre' },
];

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
            <BaseCombobox
                :model-value="form.estado"
                label="Estado del Ticket"
                :options="statusOptions"
                :searchable="false"
                @update:model-value="(val) => { if (val != null) form.estado = String(val); }"
            />

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
    gap: 16px;
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
