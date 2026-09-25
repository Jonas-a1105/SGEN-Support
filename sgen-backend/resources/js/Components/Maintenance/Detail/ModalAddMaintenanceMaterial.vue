<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput, BaseSelect, BaseBadge } from '@/Components/UI';
import { formatCurrency } from '@/Utils/formatters';
import type { InventoryOptionItem } from '@/Types/maintenance';

const props = withDefaults(
    defineProps<{
        isOpen: boolean;
        maintenanceId: number;
        inventoryItems?: InventoryOptionItem[];
    }>(),
    {
        inventoryItems: () => [],
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const selectedItemId = ref<number | ''>('');
const cantidad = ref<number>(1);
const isSubmitting = ref(false);
const validationError = ref<string | null>(null);

const itemSelectOptions = computed(() => {
    return props.inventoryItems.map((item) => ({
        value: item.id,
        label: `${item.codigo} - ${item.nombre} (${item.stock_actual} ${item.unidad_medida || 'uds'} disp.)`,
    }));
});

const selectedItem = computed(() => {
    if (!selectedItemId.value) return null;
    return props.inventoryItems.find((i) => i.id === Number(selectedItemId.value)) || null;
});

const maxStock = computed(() => {
    return selectedItem.value ? selectedItem.value.stock_actual : 1;
});

const estimatedTotal = computed(() => {
    if (!selectedItem.value) return 0;
    return (selectedItem.value.valor_compra || 0) * (cantidad.value || 0);
});

const stockRemaining = computed(() => {
    if (!selectedItem.value) return 0;
    return selectedItem.value.stock_actual - (cantidad.value || 0);
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            selectedItemId.value = props.inventoryItems.length > 0 ? props.inventoryItems[0].id : '';
            cantidad.value = 1;
            validationError.value = null;
        }
    }
);

const handleSubmit = () => {
    validationError.value = null;

    if (!selectedItemId.value) {
        validationError.value = 'Debe seleccionar un material o repuesto del inventario.';
        return;
    }

    if (!cantidad.value || cantidad.value < 1) {
        validationError.value = 'La cantidad a consumir debe ser de al menos 1 unidad.';
        return;
    }

    if (selectedItem.value && cantidad.value > selectedItem.value.stock_actual) {
        validationError.value = `Stock insuficiente. Solo hay ${selectedItem.value.stock_actual} unidades disponibles.`;
        return;
    }

    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenanceId}/materiales`,
        {
            item_id: Number(selectedItemId.value),
            cantidad: Number(cantidad.value),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
            },
            onError: (err) => {
                validationError.value = Object.values(err)[0] as string || 'Ocurrió un error al registrar el material.';
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Consumir Repuesto / Material"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSubmit" class="modal-form-stack">
            <div class="modal-intro-text">
                Seleccione el ítem del catálogo para asociarlo a la orden <strong>#{{ maintenanceId }}</strong>.
                Las existencias se deducirán automáticamente y se registrará un movimiento en el Kardex.
            </div>

            <div v-if="validationError" class="modal-alert-error">
                <svg viewBox="0 0 24 24" class="alert-icon" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ validationError }}</span>
            </div>

            <div v-if="inventoryItems.length === 0" class="no-items-warning">
                <svg viewBox="0 0 24 24" class="alert-icon" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>No hay ítems con existencias disponibles en el inventario para consumir.</span>
            </div>

            <div v-else class="form-fields-container">
                <div class="form-group">
                    <label class="form-label" for="materialSelect">
                        Seleccionar Repuesto / Insumo <span class="required">*</span>
                    </label>
                    <BaseSelect
                        id="materialSelect"
                        v-model="selectedItemId"
                        :options="itemSelectOptions"
                        placeholder="-- Elija un material del almacén --"
                        required
                    />
                </div>

                <div class="form-group">
                    <label class="form-label" for="materialCantidad">
                        Cantidad a Utilizar <span class="required">*</span>
                    </label>
                    <BaseInput
                        id="materialCantidad"
                        v-model.number="cantidad"
                        type="number"
                        min="1"
                        :max="maxStock"
                        placeholder="1"
                        required
                    />
                </div>

                <!-- Live Preview Card -->
                <div v-if="selectedItem" class="item-preview-card">
                    <div class="preview-row">
                        <span class="preview-label">Código:</span>
                        <BaseBadge variant="code">{{ selectedItem.codigo }}</BaseBadge>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Stock Actual:</span>
                        <strong class="stock-current">{{ selectedItem.stock_actual }} {{ selectedItem.unidad_medida || 'uds' }}</strong>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Stock Remanente:</span>
                        <strong :class="stockRemaining < 0 ? 'stock-danger' : 'stock-ok'">
                            {{ stockRemaining }} {{ selectedItem.unidad_medida || 'uds' }}
                        </strong>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Costo Estimado:</span>
                        <strong class="preview-total">{{ formatCurrency(estimatedTotal) }}</strong>
                    </div>
                </div>
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
                    variant="primary"
                    size="md"
                    type="submit"
                    :disabled="isSubmitting || inventoryItems.length === 0"
                >
                    <span v-if="isSubmitting">Procesando...</span>
                    <span v-else>Confirmar y Descontar Stock</span>
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.modal-intro-text {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
}

.modal-alert-error {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3);
    background: rgba(239, 68, 68, 0.1);
    color: var(--red);
    border: var(--stroke-w) solid rgba(239, 68, 68, 0.25);
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 500;
}

.no-items-warning {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3);
    background: rgba(249, 115, 22, 0.1);
    color: var(--orange);
    border: var(--stroke-w) solid rgba(249, 115, 22, 0.25);
    border-radius: var(--radius-md);
    font-size: 13px;
}

.alert-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.form-fields-container {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}

.required {
    color: var(--red);
}

.item-preview-card {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: var(--radius-md);
    padding: var(--space-3);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.preview-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.preview-label {
    color: var(--text-muted);
}

.stock-current {
    color: var(--text);
}

.stock-ok {
    color: var(--green);
}

.stock-danger {
    color: var(--red);
}

.preview-total {
    color: var(--orange);
    font-size: 13px;
}

.modal-actions-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
