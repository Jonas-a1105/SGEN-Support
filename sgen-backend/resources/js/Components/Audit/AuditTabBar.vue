<script setup lang="ts">
defineProps<{
    activeTab: 'sesiones' | 'bitacora';
    sessionsCount: number;
    actionsCount: number;
}>();

const emit = defineEmits<{
    (e: 'change', tab: 'sesiones' | 'bitacora'): void;
}>();
</script>

<template>
    <div class="audit-tab-bar" role="tablist" aria-label="Secciones de Auditoría">
        <button
            type="button"
            role="tab"
            class="tab-btn"
            :class="{ active: activeTab === 'sesiones' }"
            :aria-selected="activeTab === 'sesiones'"
            @click="emit('change', 'sesiones')"
        >
            <div class="tab-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <span class="tab-label">Sesiones de Acceso</span>
            <span class="tab-count-pill">{{ sessionsCount }}</span>
        </button>

        <button
            type="button"
            role="tab"
            class="tab-btn"
            :class="{ active: activeTab === 'bitacora' }"
            :aria-selected="activeTab === 'bitacora'"
            @click="emit('change', 'bitacora')"
        >
            <div class="tab-icon">
                <svg viewBox="0 0 24 24">
                    <polyline points="12 8 12 12 14 14" />
                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                </svg>
            </div>
            <span class="tab-label">Bitácora de Movimientos</span>
            <span class="tab-count-pill">{{ actionsCount }}</span>
        </button>
    </div>
</template>

<style scoped>
.audit-tab-bar {
    display: inline-flex;
    align-items: center;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 4px;
    gap: 6px;
    margin-bottom: 20px;
    max-width: 100%;
    overflow-x: auto;
    box-shadow: none !important;
}

.tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 18px;
    border-radius: var(--card-radius);
    border: var(--stroke-w) solid transparent;
    background: transparent;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    box-shadow: none !important;
}

.tab-btn:hover {
    color: var(--text);
    background: var(--stroke-subtle);
}

.tab-btn.active {
    background: var(--bg-sub);
    color: var(--text);
    border-color: var(--stroke);
}

.tab-icon {
    width: 18px;
    height: 18px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.tab-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tab-btn.active .tab-icon {
    color: var(--orange);
}

.tab-count-pill {
    padding: 2px 8px;
    border-radius: 9999px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
}

.tab-btn.active .tab-count-pill {
    background: var(--accent-soft);
    border-color: var(--orange);
    color: var(--orange);
}
</style>
