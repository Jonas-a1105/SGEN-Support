<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Product } from '@/Types/inventory';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

const props = defineProps<{
    isOpen: boolean;
    product: Product | null;
    products: Product[];
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const selectedItemId = ref<number | null>(props.product?.id || null);

const form = useForm({
    product_id: props.product?.id || 0,
    type: 'ENTRADA',
    quantity: 1,
    reason: '',
});

watch(
    () => props.product,
    (newVal) => {
        if (newVal) {
            selectedItemId.value = newVal.id;
            form.product_id = newVal.id;
        }
    },
    { immediate: true }
);

const currentProduct = (): Product | undefined => {
    return props.products.find((p) => p.id === selectedItemId.value) || props.product || props.products[0];
};

const submit = () => {
    const item = currentProduct();
    if (!item) return;

    form.product_id = item.id;
    form.post('/inventario/ajustar', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('reason');
            form.quantity = 1;
            emit('close');
        },
    });
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Ajustar Stock de Inventario"
        max-width="md"
        @close="emit('close')"
    >
        <form id="formAdjustStock" class="modal-form-stack" @submit.prevent="submit">
            <div class="product-summary-box">
                <div v-if="product">
                    <strong class="product-summary-name" id="stockModalItemName">{{ product.name }}</strong>
                    <div class="product-summary-meta">
                        <span>Código: <BaseBadge variant="code" id="stockModalSku">{{ product.sku }}</BaseBadge></span>
                        <span>Stock Actual: <strong>{{ product.current_stock }} unidades</strong></span>
                    </div>
                </div>
                <div v-else>
                    <label class="form-label">Seleccionar Artículo</label>
                    <select v-model="selectedItemId" class="form-select">
                        <option v-for="p in products" :key="p.id" :value="p.id">
                            {{ p.sku }} - {{ p.name }} (Stock: {{ p.current_stock }})
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Movimiento</label>
                <select v-model="form.type" class="form-select" id="adjustMovementType">
                    <option value="ENTRADA">Entrada (+) Ingreso de mercadería</option>
                    <option value="SALIDA">Salida (-) Consumo o merma</option>
                    <option value="AJUSTE">Conteo de Auditoría (Fijar exacto)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Cantidad</label>
                <input v-model="form.quantity" type="number" class="form-input" id="adjustAmount" min="1" required />
            </div>

            <div class="form-group">
                <label class="form-label">Motivo o Justificación</label>
                <input
                    v-model="form.reason"
                    type="text"
                    class="form-input"
                    id="adjustReason"
                    placeholder="Ej: Compra nueva orden #440, reposición de oficina..."
                    required
                />
            </div>

            <div class="modal-actions-row">
                <BaseButton variant="secondary" id="cancelAdjustStock" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton variant="primary" :disabled="form.processing" :loading="form.processing" type="submit">
                    Aplicar Movimiento
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

.product-summary-box {
    padding: var(--space-3) var(--space-4);
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: var(--radius-md);
}

.product-summary-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    display: block;
    margin-bottom: var(--space-1);
}

.product-summary-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-muted);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--radius-md);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 13px;
    font-family: var(--font-sans);
    outline: none;
    transition: border-color var(--transition-fast);
}

.form-input:focus,
.form-select:focus {
    border-color: var(--orange);
}

.modal-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    margin-top: var(--space-2);
    padding-top: var(--space-3);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
