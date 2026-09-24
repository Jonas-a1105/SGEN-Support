<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Equipment } from './types';

const props = defineProps<{
    modelValue: string;
    equipments?: Equipment[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const assetSearchQuery = ref('');

const filteredEquipments = computed(() => {
    const list = props.equipments || [];
    const q = assetSearchQuery.value.trim().toLowerCase();
    if (!q) return list;
    return list.filter(
        (eq) =>
            eq.code.toLowerCase().includes(q) ||
            eq.model.toLowerCase().includes(q) ||
            eq.type.toLowerCase().includes(q) ||
            eq.department.toLowerCase().includes(q)
    );
});
</script>

<template>
    <div class="form-section-card">
        <div class="card-section-header">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg>
            <span>1. Selección del Activo</span>
        </div>

        <div class="form-group">
            <div class="search-asset-wrap">
                <svg viewBox="0 0 24 24" class="search-field-icon">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    v-model="assetSearchQuery"
                    type="text"
                    class="form-input search-input-padded"
                    placeholder="Buscar por serial, nombre o código..."
                    autocomplete="off"
                />
            </div>
            <span class="helper-text">Ingrese el serial, código de activo o nombre para filtrar.</span>

            <div class="asset-select-wrap">
                <select
                    :value="modelValue"
                    class="form-select"
                    required
                    @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
                >
                    <option value="" disabled>-- Seleccione un equipo del inventario --</option>
                    <option
                        v-for="eq in filteredEquipments"
                        :key="eq.id"
                        :value="eq.id"
                    >
                        {{ eq.model }} • ID: {{ eq.code }} ({{ eq.department }})
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-section-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: none !important;
}

.card-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: 14px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.card-section-header svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    color: var(--orange);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.search-asset-wrap {
    position: relative;
}

.search-field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    stroke: var(--text-muted);
    fill: none;
    stroke-width: 2;
    pointer-events: none;
}

.search-input-padded {
    padding-left: 38px;
}

.asset-select-wrap {
    margin-top: 6px;
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.form-input:focus,
.form-select:focus {
    border-color: var(--orange);
}

.helper-text {
    font-size: 11px;
    color: var(--text-dim);
    margin-top: 4px;
    line-height: 1.4;
}
</style>
