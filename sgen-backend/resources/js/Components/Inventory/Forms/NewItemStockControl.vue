<script setup lang="ts">
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import BaseDatePicker from '@/Components/UI/BaseDatePicker.vue';
import { IconPackage, IconMapPin } from '@tabler/icons-vue';

defineProps<{
    initialStock: number;
    minimumStock: number;
    purchasePrice: number;
    vendor: string;
    purchaseDate: string;
    warrantyExpiration: string;
    location?: string;
}>();

const emit = defineEmits<{
    (e: 'update:initialStock', val: number): void;
    (e: 'update:minimumStock', val: number): void;
    (e: 'update:purchasePrice', val: number): void;
    (e: 'update:vendor', val: string): void;
    (e: 'update:purchaseDate', val: string): void;
    (e: 'update:warrantyExpiration', val: string): void;
}>();
</script>

<template>
    <div class="form-stock-control-panel">
        <div class="form-control-header">
            <span class="form-section-title-inline">
                <IconPackage :size="16" stroke-width="2" class="inline-icon" />
                <span>Control de Stock</span>
            </span>
            <BaseBadge variant="info">
                <IconMapPin :size="12" stroke-width="2" class="inline-icon" />
                <span>Asignación: {{ location || 'Almacén Central' }}</span>
            </BaseBadge>
        </div>

        <!-- Rejilla de 3 Columnas Exclusiva de Stock e Inversión -->
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label" for="inputItemStockInit">Stock Inicial</label>
                <input
                    :value="initialStock"
                    type="number"
                    class="form-input"
                    id="inputItemStockInit"
                    min="0"
                    @input="emit('update:initialStock', Number(($event.target as HTMLInputElement).value))"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="inputItemStockAlert">Stock Mínimo (Alerta)</label>
                <input
                    :value="minimumStock"
                    type="number"
                    class="form-input"
                    id="inputItemStockAlert"
                    min="1"
                    @input="emit('update:minimumStock', Number(($event.target as HTMLInputElement).value))"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="inputItemCost">Costo Unitario ($)</label>
                <input
                    :value="purchasePrice"
                    type="number"
                    class="form-input"
                    id="inputItemCost"
                    step="0.01"
                    @input="emit('update:purchasePrice', Number(($event.target as HTMLInputElement).value))"
                />
            </div>
        </div>

        <!-- Rejilla de 2 Columnas de Compra -->
        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="inputItemVendor">Proveedor</label>
                <input
                    :value="vendor"
                    type="text"
                    class="form-input"
                    id="inputItemVendor"
                    placeholder="Ej: TecnoSupply C.A."
                    @input="emit('update:vendor', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group-custom">
                <BaseDatePicker
                    :model-value="purchaseDate"
                    label="Fecha de Compra"
                    @update:model-value="emit('update:purchaseDate', $event)"
                />
            </div>
        </div>

        <div class="form-group-custom">
            <BaseDatePicker
                :model-value="warrantyExpiration"
                label="Vencimiento Garantía"
                @update:model-value="emit('update:warrantyExpiration', $event)"
            />
        </div>
    </div>
</template>

<style scoped>
.form-stock-control-panel {
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    padding-top: var(--space-4);
}

.form-control-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
}

.form-section-title-inline {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

/* Rejillas exclusivas encapsuladas en scoped */
.form-row-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-3);
}

.form-row-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-3);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    margin-bottom: var(--space-3);
}

.form-group-custom {
    margin-bottom: var(--space-3);
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.form-input {
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

.form-input:focus {
    border-color: var(--orange);
}

@media (max-width: 768px) {
    .form-row-3,
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
