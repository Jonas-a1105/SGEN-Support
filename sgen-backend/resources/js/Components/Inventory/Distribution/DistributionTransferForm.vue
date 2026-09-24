<script setup lang="ts">
import { ref } from 'vue';
import type { Product, Department, TransferStockPayload } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = defineProps<{
    item: Product;
    departments: Department[];
    isSubmitting?: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit', payload: TransferStockPayload): void;
}>();

const originId = ref<number | null>(null);
const destinationId = ref<number | null>(props.departments[0]?.id || null);
const quantity = ref(1);
const reason = ref('');

const handleSubmit = () => {
    if (!destinationId.value) return;

    emit('submit', {
        item_id: props.item.id,
        origen_id: originId.value,
        destino_id: destinationId.value,
        cantidad: quantity.value,
        motivo: reason.value,
    });
};
</script>

<template>
    <BaseCard padding="lg" class="transfer-card">
        <div class="transfer-panel-header">
            <div class="transfer-icon-square">
                <svg viewBox="0 0 24 24">
                    <path d="M17 2.1l4 4-4 4" />
                    <path d="M3 12.2v-2a4 4 0 0 1 4-4h14" />
                    <path d="M7 21.9l-4-4 4-4" />
                    <path d="M21 11.8v2a4 4 0 0 1-4 4H3" />
                </svg>
            </div>
            <div>
                <h3 class="panel-title">Transferir Stock</h3>
                <span class="transfer-sub-desc">Mover items entre almacenes</span>
            </div>
        </div>

        <form id="formTransferStock" class="transfer-form-stack" @submit.prevent="handleSubmit">
            <div class="form-group">
                <label class="form-label" for="transferOriginSelect">ORIGEN</label>
                <select v-model="originId" class="form-select" id="transferOriginSelect">
                    <option :value="null">🏢 Almacén Central (Principal)</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">
                        {{ d.nombre }}
                    </option>
                </select>
            </div>

            <div class="arrow-down-divider">↓</div>

            <div class="form-group">
                <label class="form-label" for="transferDestSelect">
                    DESTINO <span class="required-asterisk">*</span>
                </label>
                <select v-model="destinationId" class="form-select" id="transferDestSelect" required>
                    <option :value="null" disabled>📍 Seleccione un destino...</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">
                        {{ d.nombre }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="transferQtyInput">CANTIDAD</label>
                <div class="transfer-qty-row">
                    <input
                        v-model.number="quantity"
                        type="number"
                        class="form-input transfer-qty-input"
                        id="transferQtyInput"
                        min="1"
                        :max="item.current_stock"
                        required
                    />
                    <span class="transfer-unit-label">Unidades</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="transferReasonInput">MOTIVO</label>
                <textarea
                    v-model="reason"
                    class="form-textarea"
                    id="transferReasonInput"
                    rows="3"
                    placeholder="Ej: Reabastecimiento regular..."
                />
            </div>

            <BaseButton
                variant="primary"
                id="btnSubmitTransfer"
                type="submit"
                class="w-full"
                :disabled="isSubmitting || !destinationId"
                :loading="isSubmitting"
            >
                <span>✔</span>
                <span>Confirmar Transferencia</span>
            </BaseButton>
        </form>
    </BaseCard>
</template>

<style scoped>
.transfer-card {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.transfer-panel-header {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: var(--space-3);
}

.transfer-icon-square {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-md);
    background: var(--blue);
    color: #ffffff;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.transfer-icon-square svg {
    width: 22px;
    height: 22px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.transfer-sub-desc {
    font-size: 12px;
    color: var(--text-muted);
}

.transfer-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    margin-bottom: 0;
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.required-asterisk {
    color: var(--red);
}

.arrow-down-divider {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    margin: 2px auto;
    color: var(--text-muted);
    font-size: 14px;
}

.form-input,
.form-select,
.form-textarea {
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
.form-select:focus,
.form-textarea:focus {
    border-color: var(--orange);
}

.transfer-qty-row {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.transfer-qty-input {
    width: 120px !important;
}

.transfer-unit-label {
    font-size: 13px;
    color: var(--text-muted);
}

.w-full {
    width: 100%;
}
</style>
