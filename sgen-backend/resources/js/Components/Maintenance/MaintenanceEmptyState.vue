<script setup lang="ts">
// Stateless empty state component for Maintenance module
</script>

<template>
    <div class="empty-state-panel">
        <div class="empty-calendar-icon">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
        </div>
        <h2 class="empty-title">Sin mantenimientos</h2>
        <p class="empty-desc">No hay registros de mantenimientos que coincidan con la búsqueda o filtro seleccionado.</p>
    </div>
</template>

<style scoped>
.empty-state-panel {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 60px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-shadow: none !important;
}

.empty-calendar-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    margin-bottom: 16px;
    color: var(--text-dim);
}

.empty-calendar-icon svg {
    width: 28px;
    height: 28px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.empty-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 6px;
}

.empty-desc {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 420px;
    margin: 0;
    line-height: 1.45;
}
</style>
