<script setup lang="ts">
defineProps<{
    summaryText: string;
}>();
</script>

<template>
    <div class="preview-summary-card">
        <div class="preview-summary-text">
            <span class="kicker">RESUMEN PREVIO</span>
            <strong>{{ summaryText }}</strong>
        </div>
        <div class="mini-bars-illustration" aria-hidden="true">
            <div class="mini-bar b1"></div>
            <div class="mini-bar b2"></div>
            <div class="mini-bar b3"></div>
            <div class="mini-bar b4"></div>
            <div class="mini-bar b5"></div>
        </div>
    </div>
</template>

<style scoped>
.preview-summary-card {
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: 14px;
    background: var(--bg-sub);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: none !important;
}

.preview-summary-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.preview-summary-text .kicker {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--orange);
}

.preview-summary-text strong {
    font-size: 12px;
    color: var(--text);
    line-height: 1.35;
}

.mini-bars-illustration {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 28px;
    flex-shrink: 0;
}

.mini-bar {
    width: 4px;
    border-radius: 2px;
    background: var(--orange);
    opacity: 0.8;
}

.mini-bar.b1 { height: 10px; }
.mini-bar.b2 { height: 18px; }
.mini-bar.b3 { height: 26px; }
.mini-bar.b4 { height: 14px; }
.mini-bar.b5 { height: 22px; }
</style>
