<script setup lang="ts">
import type { TicketAsset } from '@/Types/support';

defineProps<{
    asset: TicketAsset;
}>();
</script>

<template>
    <section class="detail-panel asset-watermark-card">
        <svg class="asset-watermark-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>

        <div class="panel-header-row">
            <div class="panel-title-group">
                <div class="panel-icon-pill" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <h2 class="panel-title">ACTIVO AFECTADO</h2>
            </div>
        </div>

        <div class="panel-body-pad">
            <div class="asset-serial-block">
                <span class="data-kicker">Serial</span>
                <div class="asset-serial-num">{{ asset.serial }}</div>
            </div>

            <div class="asset-specs-grid">
                <div class="data-item">
                    <span class="data-kicker">Tipo</span>
                    <div class="data-val-big">{{ asset.type }}</div>
                </div>
                <div class="data-item">
                    <span class="data-kicker">Modelo</span>
                    <div class="data-val-big">{{ asset.model }}</div>
                </div>
            </div>

            <div class="asset-relation-row">
                <div class="asset-rel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="2"></line>
                        <line x1="15" y1="22" x2="15" y2="2"></line>
                        <line x1="4" y1="8" x2="20" y2="8"></line>
                        <line x1="4" y1="14" x2="20" y2="14"></line>
                    </svg>
                </div>
                <div class="data-item">
                    <span class="data-kicker">Departamento</span>
                    <div class="data-val-big">{{ asset.department }}</div>
                </div>
            </div>

            <div class="asset-relation-row">
                <div class="asset-rel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="data-item">
                    <span class="data-kicker">Asignado a</span>
                    <div class="data-val-big">
                        <span class="custom-link">{{ asset.assigned_to }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.detail-panel {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.panel-header-row {
    padding: 18px 22px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.panel-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.panel-title {
    margin: 0;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
    letter-spacing: -0.01em;
}
.panel-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.panel-icon-pill svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.panel-body-pad {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.asset-watermark-card {
    position: relative;
}
.asset-watermark-bg {
    position: absolute;
    right: -10px;
    bottom: -15px;
    width: 110px;
    height: 110px;
    color: var(--stroke);
    opacity: 0.15;
    pointer-events: none;
}
.asset-serial-block {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.asset-serial-num {
    font-family: monospace;
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
}
.asset-specs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.data-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.data-kicker {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}
.data-val-big {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.asset-relation-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.asset-rel-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    display: grid;
    place-items: center;
    color: var(--text-dim);
    flex-shrink: 0;
}
.asset-rel-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.custom-link {
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
}
</style>
