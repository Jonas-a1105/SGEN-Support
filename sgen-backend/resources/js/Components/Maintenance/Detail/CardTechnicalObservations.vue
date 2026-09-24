<script setup lang="ts">
import { BaseCard } from '@/Components/UI';

defineProps<{
    observaciones: string;
}>();
</script>

<template>
    <BaseCard class="maint-section-card" padding="lg">
        <div class="maint-card-header">
            <div class="maint-card-header-left">
                <div class="maint-card-icon-wrap amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </div>
                <div>
                    <h2 class="maint-card-title">Observaciones Técnicas y Dictamen</h2>
                    <p class="maint-card-sub">Anotaciones operativas registradas por el personal</p>
                </div>
            </div>
        </div>

        <div class="maint-observations-callout">
            <p class="observations-text">{{ observaciones }}</p>
        </div>
    </BaseCard>
</template>

<style scoped>
.maint-section-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
}

.maint-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.maint-card-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.maint-card-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-card-icon-wrap.amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.maint-card-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.maint-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.maint-card-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.maint-observations-callout {
    padding: 16px;
    background: rgba(245, 158, 11, 0.08);
    border: var(--stroke-w) solid rgba(245, 158, 11, 0.3);
    border-radius: 10px;
}

.observations-text {
    margin: 0;
    font-size: 13px;
    color: var(--text);
    line-height: 1.6;
    white-space: pre-line;
}
</style>
