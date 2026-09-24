<script setup lang="ts">
defineProps<{
    name: string;
    category: string;
    unitOfMeasure: string;
    brand: string;
    model: string;
}>();

const emit = defineEmits<{
    (e: 'update:name', val: string): void;
    (e: 'update:category', val: string): void;
    (e: 'update:unitOfMeasure', val: string): void;
    (e: 'update:brand', val: string): void;
    (e: 'update:model', val: string): void;
}>();
</script>

<template>
    <div>
        <span class="form-section-title">🏷 Ficha Técnica</span>

        <div class="form-group">
            <label class="form-label" for="inputItemName">
                Nombre del Artículo <span class="required-asterisk">*</span>
            </label>
            <input
                :value="name"
                type="text"
                class="form-input"
                id="inputItemName"
                placeholder="Ej: Cable HDMI 4K Trenzado 2m"
                required
                @input="emit('update:name', ($event.target as HTMLInputElement).value)"
            />
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="selectItemCategory">Categoría</label>
                <select
                    :value="category"
                    class="form-select"
                    id="selectItemCategory"
                    @change="emit('update:category', ($event.target as HTMLSelectElement).value)"
                >
                    <option value="Consumibles">Consumibles</option>
                    <option value="Cables & Red">Cables & Red</option>
                    <option value="Hardware">Hardware</option>
                    <option value="Periféricos">Periféricos</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="selectItemUnit">Unidad de Medida</label>
                <select
                    :value="unitOfMeasure"
                    class="form-select"
                    id="selectItemUnit"
                    @change="emit('update:unitOfMeasure', ($event.target as HTMLSelectElement).value)"
                >
                    <option value="Unidad">Unidad</option>
                    <option value="Metro">Metro</option>
                    <option value="Caja">Caja</option>
                    <option value="Pieza">Pieza</option>
                </select>
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="inputItemBrand">Marca</label>
                <input
                    :value="brand"
                    type="text"
                    class="form-input"
                    id="inputItemBrand"
                    placeholder="Ej: Belkin"
                    @input="emit('update:brand', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="form-group">
                <label class="form-label" for="inputItemModel">Modelo</label>
                <input
                    :value="model"
                    type="text"
                    class="form-input"
                    id="inputItemModel"
                    placeholder="Ej: F3Y021bt2M"
                    @input="emit('update:model', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-section-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: var(--space-3);
    display: block;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    margin-bottom: var(--space-3);
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

.form-row-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-3);
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

@media (max-width: 768px) {
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
