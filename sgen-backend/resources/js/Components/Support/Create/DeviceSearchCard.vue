<script setup lang="ts">
import type { FormEquipment } from './types';

defineProps<{
    modelValue: string;
    searchResults: FormEquipment[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'submit'): void;
    (e: 'select', equipment: FormEquipment): void;
}>();
</script>

<template>
    <div class="tf-search-card">
        <label class="tf-search-label" for="busqueda_equipo">Buscar Equipo Afectado</label>
        <div class="tf-search-wrapper">
            <div class="tf-search-icon">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
            </div>
            <input
                id="busqueda_equipo"
                :value="modelValue"
                type="text"
                class="tf-search-input"
                placeholder="Serial, código o modelo..."
                @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                @keydown.enter.prevent="emit('submit')"
            />
            <button
                type="button"
                class="tf-search-btn"
                @click="emit('submit')"
            >
                Buscar
            </button>
        </div>
        <p class="tf-search-hint">Ingresa el identificador del activo para vincularlo al ticket.</p>

        <!-- Quick results dropdown if searching -->
        <div v-if="modelValue.trim().length > 1 && searchResults.length > 0" class="tf-search-results-list">
            <div
                v-for="eq in searchResults.slice(0, 5)"
                :key="eq.id"
                class="tf-search-result-item"
                @click="emit('select', eq)"
            >
                <div class="tf-result-main">
                    <b>{{ eq.type }} - {{ eq.model }}</b>
                    <span>{{ eq.code || eq.serial }} • {{ eq.department }}</span>
                </div>
                <span class="tf-result-badge">Vincular</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tf-search-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    padding: 18px;
    box-shadow: none !important;
}

.tf-search-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #8e9199);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}

.tf-search-wrapper {
    position: relative;
    display: flex;
    gap: 8px;
}

.tf-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-dim, #60636d);
    display: grid;
    place-items: center;
    pointer-events: none;
}

.tf-search-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tf-search-input {
    width: 100%;
    height: 40px;
    padding: 0 12px 0 36px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.tf-search-input:focus {
    border-color: var(--orange, #2563eb);
}

.tf-search-btn {
    height: 40px;
    padding: 0 14px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    color: var(--text, #f4f4f6);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: none !important;
    white-space: nowrap;
}

.tf-search-btn:hover {
    border-color: var(--orange, #2563eb);
}

.tf-search-hint {
    font-size: 11px;
    color: var(--text-dim, #60636d);
    margin: 8px 0 0 0;
}

.tf-search-results-list {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 180px;
    overflow-y: auto;
}

.tf-search-result-item {
    padding: 8px 12px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.tf-search-result-item:hover {
    border-color: var(--orange, #2563eb);
}

.tf-result-main {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.tf-result-main b {
    font-size: 12px;
    color: var(--text, #f4f4f6);
}

.tf-result-main span {
    font-size: 10px;
    color: var(--text-muted, #8e9199);
}

.tf-result-badge {
    font-size: 10px;
    font-weight: 600;
    color: var(--orange, #2563eb);
    padding: 2px 6px;
    background: rgba(37, 99, 235, 0.1);
    border-radius: 6px;
}
</style>
