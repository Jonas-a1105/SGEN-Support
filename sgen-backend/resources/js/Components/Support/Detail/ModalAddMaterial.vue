<script setup lang="ts">
import { reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import type { InventoryItemOption } from '@/types/support';
import { IconAlertTriangle } from '@tabler/icons-vue';

interface Props {
    isOpen: boolean;
    ticketId: number;
    inventoryItems?: InventoryItemOption[];
}

const props = withDefaults(defineProps<Props>(), {
    inventoryItems: () => [],
});

const emit = defineEmits<{ (e: 'close'): void }>();

const form = reactive({
    item_id: null as number | null,
    cantidad: 1,
    isSubmitting: false,
    errorMessage: '',
});

const selectedItem = computed(() => {
    if (!form.item_id) return null;
    return props.inventoryItems.find((i) => i.id === form.item_id) || null;
});

const maxAvailableStock = computed(() => {
    return selectedItem.value ? selectedItem.value.stock : 999;
});

const isStockInsufficient = computed(() => {
    if (!selectedItem.value) return false;
    return form.cantidad > selectedItem.value.stock;
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            form.item_id = props.inventoryItems.length > 0 ? props.inventoryItems[0].id : null;
            form.cantidad = 1;
            form.isSubmitting = false;
            form.errorMessage = '';
        }
    }
);

const handleSubmit = () => {
    if (!form.item_id || form.cantidad <= 0 || isStockInsufficient.value || form.isSubmitting) {
        return;
    }

    form.isSubmitting = true;
    form.errorMessage = '';

    router.post(
        `/soportes/${props.ticketId}/materiales`,
        {
            item_id: form.item_id,
            cantidad: form.cantidad,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.isSubmitting = false;
                emit('close');
            },
            onError: (errors) => {
                form.isSubmitting = false;
                form.errorMessage = Object.values(errors).flat().join(', ') || 'Error al imputar material.';
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Registrar Consumo de Material / Repuesto" max-width="md" @close="emit('close')">
        <form class="material-form" @submit.prevent="handleSubmit">
            <p class="modal-description">
                Selecciona la pieza del inventario a descontar. La operación actualizará el Kardex y descontará el stock de forma atómica.
            </p>

            <div v-if="form.errorMessage" class="error-banner">
                {{ form.errorMessage }}
            </div>

            <div class="form-group">
                <label class="form-label" for="matItemSelect">Artículo / Repuesto del Almacén *</label>
                <select
                    id="matItemSelect"
                    v-model.number="form.item_id"
                    class="form-select"
                    required
                >
                    <option :value="null" disabled>-- Selecciona un repuesto --</option>
                    <option
                        v-for="item in inventoryItems"
                        :key="item.id"
                        :value="item.id"
                        :disabled="item.stock <= 0"
                    >
                        [{{ item.code }}] {{ item.name }} — Stock disponible: {{ item.stock }}
                    </option>
                </select>
            </div>

            <div v-if="selectedItem" class="stock-pill-row">
                <span class="stock-indicator" :class="{ 'stock-low': selectedItem.stock < 5, 'stock-zero': selectedItem.stock <= 0 }">
                    Stock en Bodega: <strong>{{ selectedItem.stock }} uds</strong>
                </span>
                <span v-if="selectedItem.stock <= 0" class="stock-alert">
                    <IconAlertTriangle :size="14" stroke-width="2" class="alert-icon-inline" />
                    <span>Sin existencias suficientes para consumir</span>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="matQuantity">Cantidad Utilizada *</label>
                <input
                    id="matQuantity"
                    v-model.number="form.cantidad"
                    type="number"
                    min="1"
                    :max="maxAvailableStock > 0 ? maxAvailableStock : 1"
                    class="form-input"
                    :class="{ 'input-error': isStockInsufficient }"
                    required
                />
                <span v-if="isStockInsufficient" class="field-error">
                    La cantidad solicitada supera el stock disponible ({{ maxAvailableStock }} uds).
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
                    :disabled="form.isSubmitting || !form.item_id || isStockInsufficient || (selectedItem && selectedItem.stock <= 0)"
                >
                    Descontar e Imputar
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.material-form {
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
.form-input {
    width: 100%;
    height: 42px;
    padding: 8px 12px;
    border-radius: 10px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
}
.form-select:focus,
.form-input:focus {
    border-color: var(--primary);
}
.input-error {
    border-color: var(--danger) !important;
}
.field-error {
    font-size: 12px;
    color: var(--danger);
    font-weight: 500;
}
.stock-pill-row {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
}
.stock-indicator {
    padding: 4px 10px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    color: var(--text);
}
.stock-low {
    background: rgba(255, 193, 7, 0.15);
    color: #b58100;
}
.stock-zero {
    background: rgba(220, 53, 69, 0.15);
    color: var(--danger);
}
.stock-alert {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--danger);
    font-weight: 600;
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
