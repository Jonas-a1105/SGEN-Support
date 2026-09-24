<script setup lang="ts">
defineProps<{
    isDense: boolean;
    viewMode: 'table' | 'cards';
    totalCount: number;
    totalLabel: string;
}>();

const emit = defineEmits<{
    (e: 'update:isDense', val: boolean): void;
    (e: 'update:viewMode', val: 'table' | 'cards'): void;
}>();
</script>

<template>
    <div class="view-controls-bar controls-bar-spaced">
        <div class="view-mode-group">
            <label class="switch-wrap" title="Alternar modo denso">
                <input
                    :checked="isDense"
                    type="checkbox"
                    class="switch-input"
                    @change="emit('update:isDense', ($event.target as HTMLInputElement).checked)"
                />
                <div class="switch-rail"><div class="switch-dot" /></div>
            </label>
            <button
                class="view-toggle-btn"
                :class="{ active: viewMode === 'table' }"
                type="button"
                @click="emit('update:viewMode', 'table')"
            >
                <svg viewBox="0 0 24 24" class="svg-icon-standard">
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
                class="view-toggle-btn"
                :class="{ active: viewMode === 'cards' }"
                type="button"
                @click="emit('update:viewMode', 'cards')"
            >
                <svg viewBox="0 0 24 24" class="svg-icon-standard">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                </svg>
                <span>Cards</span>
            </button>
        </div>
        <div class="controls-total-text">
            {{ totalLabel }}: <strong>{{ totalCount }}</strong>
        </div>
    </div>
</template>

<style scoped>
.controls-bar-spaced {
    margin-bottom: var(--space-3);
}

.view-controls-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.view-mode-group {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.switch-wrap {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    margin-right: 6px;
}

.switch-input { display: none; }

.switch-rail {
    width: 42px;
    height: 22px;
    background: var(--stroke);
    border-radius: 12px;
    position: relative;
    transition: background 0.2s ease;
}

.switch-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--text);
    position: absolute;
    top: 4px;
    left: 4px;
    transition: transform 0.2s ease;
}

.switch-input:checked + .switch-rail {
    background: var(--orange);
}

.switch-input:checked + .switch-rail .switch-dot {
    transform: translateX(20px);
    background: #ffffff;
}

.view-toggle-btn {
    height: 32px;
    padding: 0 var(--space-3);
    border-radius: var(--radius-sm);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.view-toggle-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.view-toggle-btn.active {
    background: var(--blue);
    border-color: var(--blue);
    color: #ffffff;
}

.svg-icon-standard {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.controls-total-text {
    font-size: 12px;
    color: var(--text-muted);
}
</style>
