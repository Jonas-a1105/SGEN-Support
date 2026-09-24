<script setup lang="ts">
import { BaseSearchToolbar, BaseToggleSwitch } from '@/Components/UI';

const search = defineModel<string>('search', { default: '' });
const viewMode = defineModel<'table' | 'cards'>('viewMode', { default: 'table' });
const isDense = defineModel<boolean>('isDense', { default: false });
</script>

<template>
    <BaseSearchToolbar
        v-model="search"
        placeholder="Buscar por usuario, colaborador o departamento..."
    >
        <template #prepend>
            <BaseToggleSwitch
                v-model="isDense"
                label="Compacto"
                title="Alternar modo compacto"
            />
        </template>

        <template #actions>
            <div class="view-mode-toggle-group">
                <button
                    class="view-mode-btn"
                    :class="{ active: viewMode === 'table' }"
                    type="button"
                    title="Vista en tabla"
                    @click="viewMode = 'table'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="8" y1="6" x2="21" y2="6" />
                        <line x1="8" y1="12" x2="21" y2="12" />
                        <line x1="8" y1="18" x2="21" y2="18" />
                        <line x1="3" y1="6" x2="3.01" y2="6" />
                        <line x1="3" y1="12" x2="3.01" y2="12" />
                        <line x1="3" y1="18" x2="3.01" y2="18" />
                    </svg>
                    <span>Tabla</span>
                </button>
                <button
                    class="view-mode-btn"
                    :class="{ active: viewMode === 'cards' }"
                    type="button"
                    title="Vista en tarjetas"
                    @click="viewMode = 'cards'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    <span>Cards</span>
                </button>
            </div>
        </template>
    </BaseSearchToolbar>
</template>

<style scoped>
.view-mode-toggle-group {
    display: flex;
    gap: 4px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    padding: 3px;
    box-shadow: none !important;
}

.view-mode-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}

.view-mode-btn svg {
    width: 14px;
    height: 14px;
}

.view-mode-btn.active {
    background: var(--brand, var(--blue, #2563eb));
    color: #ffffff;
}
</style>
