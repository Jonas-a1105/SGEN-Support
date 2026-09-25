<script setup lang="ts">
import { computed } from 'vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import type { InventoryHealthData } from '@/Types/DashboardMetrics';

const props = defineProps<{
    health: InventoryHealthData;
}>();

const circumference = 314;

const dashOffset = computed(() => {
    const pct = Math.min(Math.max(props.health.operative_percentage, 0), 100);
    return Math.round(circumference * (1 - pct / 100));
});
</script>

<template>
    <BaseCard class="overview-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Distribución Operativa</h2>
            <span class="subtle">Disponibilidad de material</span>
        </div>
        <div class="overview-body">
            <div class="donut-wrap">
                <div class="donut">
                    <svg viewBox="0 0 120 120">
                        <circle class="donut-track" cx="60" cy="60" r="50" />
                        <circle
                            class="donut-fill"
                            cx="60"
                            cy="60"
                            r="50"
                            stroke-dasharray="314"
                            :stroke-dashoffset="dashOffset"
                        />
                    </svg>
                    <div class="donut-center">
                        <div class="center-content">
                            <b class="percent-text">{{ health.operative_percentage }}%</b>
                            <span class="status-sub">operativo</span>
                        </div>
                    </div>
                </div>
                <div class="legend">
                    <div class="legend-row">
                        <span class="legend-dot blue"></span>
                        <span class="legend-name">En uso</span>
                        <b class="legend-val">{{ health.used }}</b>
                    </div>
                    <div class="legend-row">
                        <span class="legend-dot green"></span>
                        <span class="legend-name">Disponible</span>
                        <b class="legend-val">{{ health.available }}</b>
                    </div>
                    <div class="legend-row">
                        <span class="legend-dot yellow"></span>
                        <span class="legend-name">Reparación</span>
                        <b class="legend-val">{{ health.repair }}</b>
                    </div>
                    <div class="legend-row">
                        <span class="legend-dot red"></span>
                        <span class="legend-name">Baja</span>
                        <b class="legend-val">{{ health.down }}</b>
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.overview-card {
    display: flex;
    flex-direction: column;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 18px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.subtle {
    font-size: 13px;
    color: var(--text-muted);
}

.overview-body {
    padding: 22px 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
}

.donut-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
    width: 100%;
    max-width: 350px;
    margin: 0 auto;
}

.donut {
    width: 120px;
    height: 120px;
    position: relative;
    flex-shrink: 0;
}

.donut svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.donut-track {
    fill: none;
    stroke: var(--stroke-subtle);
    stroke-width: 10;
}

.donut-fill {
    fill: none;
    stroke: var(--orange);
    stroke-width: 10;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.6s ease;
}

.donut-center {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    text-align: center;
}

.center-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.percent-text {
    display: block;
    font-size: 20px;
    font-weight: 700;
    line-height: 1;
    color: var(--text);
}

.status-sub {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

.legend {
    display: flex;
    flex-direction: column;
    gap: 9px;
    flex: 1;
}

.legend-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--text-muted);
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-dot.blue {
    background: var(--blue);
}

.legend-dot.green {
    background: var(--green);
}

.legend-dot.yellow {
    background: var(--yellow);
}

.legend-dot.red {
    background: var(--red);
}

.legend-name {
    color: var(--text-muted);
}

.legend-val {
    margin-left: auto;
    color: var(--text);
    font-weight: 600;
}

@media (max-width: 768px) {
    .donut-wrap {
        flex-direction: column;
        gap: 20px;
    }
}
</style>
