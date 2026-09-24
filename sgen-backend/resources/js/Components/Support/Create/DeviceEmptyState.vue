<script setup lang="ts">
// DeviceEmptyState - Simple presentation of empty equipment state
</script>

<template>
    <div class="tf-empty-device">
        <div class="tf-empty-icon">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg>
        </div>
        <p class="tf-empty-title">Ningún equipo vinculado</p>
        <p class="tf-empty-text">Puedes buscar y vincular un activo tecnológico o continuar sin vincular.</p>
    </div>
</template>

<style scoped>
.tf-empty-device {
    padding: 32px 16px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) dashed var(--stroke, #31343a);
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: none !important;
}

.tf-empty-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--bg-card, #17181a);
    color: var(--text-dim, #60636d);
    display: grid;
    place-items: center;
    margin-bottom: 10px;
}

.tf-empty-icon svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.tf-empty-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text, #f4f4f6);
    margin: 0 0 4px 0;
}

.tf-empty-text {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
    margin: 0;
    max-width: 240px;
    line-height: 1.4;
}
</style>
