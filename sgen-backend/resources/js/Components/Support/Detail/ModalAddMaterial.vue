<script setup lang="ts">
import { reactive } from 'vue';
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
    item_nombre: '',
    cantidad: 1,
    isSubmitting: false,
});

const handleSubmit = () => {
    if (!form.item_nombre.trim() || form.isSubmitting) return;

    form.isSubmitting = true;
    router.post(
        `/soportes/${props.ticketId}/materiales`,
        {
            item_nombre: form.item_nombre,
            cantidad: form.cantidad,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.item_nombre = '';
                form.cantidad = 1;
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
    <BaseModal :is-open="isOpen" title="Registrar Material / Repuesto" max-width="sm" @close="emit('close')">
        <form class="material-form" @submit.prevent="handleSubmit">
            <div class="form-group">
                <label class="form-label" for="matItemName">Artículo / Pieza de Recambio</label>
                <input
                    id="matItemName"
                    v-model="form.item_nombre"
                    type="text"
                    class="form-input"
                    placeholder="Ej: Cable Patch Cord Cat6 2m, Memoria RAM DDR3..."
                    required
                />
            </div>

            <div class="form-group">
                <label class="form-label" for="matQuantity">Cantidad Utilizada</label>
                <input
                    id="matQuantity"
                    v-model.number="form.cantidad"
                    type="number"
                    min="1"
                    class="form-input"
                    required
                />
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton variant="primary" type="submit" :disabled="form.isSubmitting">
                    {{ form.isSubmitting ? 'Guardando...' : 'Agregar al Ticket' }}
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.material-form {
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
.form-input {
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
.form-input:focus {
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
