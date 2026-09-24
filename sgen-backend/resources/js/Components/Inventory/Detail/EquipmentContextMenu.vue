<script setup lang="ts">
defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'toggle'): void;
    (e: 'history'): void;
    (e: 'print'): void;
    (e: 'duplicate'): void;
}>();
</script>

<template>
    <div class="context-dropdown-wrap">
        <button
            class="btn-menu-trigger"
            id="btnEquipMenuToggle"
            type="button"
            @click="emit('toggle')"
        >
            ⋮
        </button>
        <div
            class="context-dropdown-menu"
            id="equipMenuDropdown"
            :class="{ show: isOpen }"
        >
            <button class="ctx-item" id="ctxHistory" type="button" @click="emit('history')">
                <svg viewBox="0 0 24 24" class="svg-icon-standard">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 14 14" />
                </svg>
                <span>Ver Historial de Cambios</span>
            </button>
            <button class="ctx-item" id="ctxPrint" type="button" @click="emit('print')">
                <svg viewBox="0 0 24 24" class="svg-icon-standard">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                <span>Imprimir Ficha</span>
            </button>
            <button class="ctx-item" id="ctxDuplicate" type="button" @click="emit('duplicate')">
                <svg viewBox="0 0 24 24" class="svg-icon-standard">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                </svg>
                <span>Duplicar Equipo</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.context-dropdown-wrap {
    position: relative;
}

.btn-menu-trigger {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 16px;
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.btn-menu-trigger:hover {
    background: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.context-dropdown-menu {
    position: absolute;
    right: 0;
    top: 42px;
    width: 220px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-md);
    padding: var(--space-2);
    display: none;
    flex-direction: column;
    gap: var(--space-1);
    z-index: 70;
}

.context-dropdown-menu.show {
    display: flex;
}

.ctx-item {
    height: 36px;
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: 0 var(--space-3);
    border-radius: var(--radius-sm);
    border: none;
    background: transparent;
    color: var(--text);
    font-size: 13px;
    cursor: pointer;
    text-align: left;
    width: 100%;
    transition: all var(--transition-fast);
}

.ctx-item:hover {
    background: var(--stroke-subtle);
    color: var(--orange);
}

.svg-icon-standard {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
