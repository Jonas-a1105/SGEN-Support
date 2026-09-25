<script setup lang="ts">
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';
import BaseInput from '@/Components/UI/BaseInput.vue';
import type { Product, Department } from '@/Types/inventory';

interface Props {
    isOpen: boolean;
    products: Product[];
    departamentos: Department[];
    preselectedItem?: Product | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = useForm({
    item_id: props.preselectedItem?.id || '',
    origen_id: '',
    destino_id: '',
    cantidad: 1,
    motivo: '',
});

const productOptions = computed<ComboboxOption[]>(() =>
    props.products.map((p) => ({
        value: p.id,
        label: `${p.codigo} - ${p.nombre} (Stock: ${p.stock_actual})`,
    }))
);

const departmentOptions = computed<ComboboxOption[]>(() =>
    props.departamentos.map((d) => ({
        value: d.id,
        label: d.nombre,
    }))
);

watch(
    () => props.preselectedItem,
    (item) => {
        if (item) {
            form.item_id = item.id;
        }
    },
    { immediate: true }
);

watch(
    () => props.isOpen,
    (open) => {
        if (open && !props.preselectedItem) {
            form.reset();
        }
    }
);

const handleSubmit = () => {
    form.post('/inventario/transferir', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Transferir Stock entre Almacenes / Áreas" max-width="md" @close="emit('close')">
        <form class="transfer-form-stack" @submit.prevent="handleSubmit">
            <BaseCombobox
                v-model="form.item_id"
                label="Artículo a Transferir *"
                placeholder="Seleccione el artículo..."
                :options="productOptions"
                :searchable="true"
                required
            />

            <div class="two-col-grid">
                <BaseCombobox
                    v-model="form.origen_id"
                    label="Ubicación Origen"
                    placeholder="Almacén Central (por defecto)"
                    :options="departmentOptions"
                    :searchable="true"
                    clearable
                />

                <BaseCombobox
                    v-model="form.destino_id"
                    label="Ubicación Destino *"
                    placeholder="Seleccione destino..."
                    :options="departmentOptions"
                    :searchable="true"
                    required
                />
            </div>

            <div class="two-col-grid">
                <BaseInput
                    v-model.number="form.cantidad"
                    type="number"
                    label="Cantidad *"
                    min="1"
                    required
                />

                <BaseInput
                    v-model="form.motivo"
                    label="Motivo / Justificación"
                    placeholder="Ej. Abastecimiento de taller"
                />
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing || !form.item_id || !form.destino_id || form.cantidad < 1"
                >
                    Confirmar Transferencia
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.transfer-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.two-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

@media (max-width: 640px) {
    .two-col-grid {
        grid-template-columns: 1fr;
    }
}
</style>
