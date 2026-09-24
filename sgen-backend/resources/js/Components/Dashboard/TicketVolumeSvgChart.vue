<script setup lang="ts">
import { ref, computed } from 'vue';
import type { TicketVolumeData } from '@/types/DashboardMetrics';

const props = defineProps<{
    data: TicketVolumeData;
}>();

const emit = defineEmits<{
    (e: 'point-clicked', point: { month: string; val: number; year: number }): void;
}>();

interface ChartPoint {
    x: number;
    y: number;
    val: number;
    month: string;
    barHeight: number;
}

const hoveredIndex = ref<number | null>(null);

const maxVal = 40;
const baseY = 165;
const topY = 20;
const chartHeight = baseY - topY;
const plotLeft = 60;
const plotRight = 680;
const plotWidth = plotRight - plotLeft;
const barWidth = 32;

const chartPoints = computed<ChartPoint[]>(() => {
    const values = props.data.values || [];
    const months = props.data.months || [];
    const len = values.length;
    if (len === 0) return [];

    const step = len > 1 ? plotWidth / (len - 1) : 0;

    return values.map((val, idx) => {
        const x = plotLeft + idx * step;
        const normalizedVal = Math.min(Math.max(val, 0), maxVal);
        const barHeight = (normalizedVal / maxVal) * chartHeight;
        const y = baseY - barHeight;
        return {
            x,
            y,
            val,
            month: months[idx] || '',
            barHeight,
        };
    });
});

const pathD = computed(() => {
    const pts = chartPoints.value;
    if (pts.length === 0) return '';
    if (pts.length === 1) return `M ${pts[0].x} ${pts[0].y}`;

    let d = `M ${pts[0].x} ${pts[0].y}`;
    for (let i = 0; i < pts.length - 1; i++) {
        const p0 = pts[i];
        const p1 = pts[i + 1];
        const cpX1 = p0.x + (p1.x - p0.x) / 3;
        const cpX2 = p1.x - (p1.x - p0.x) / 3;
        d += ` C ${cpX1} ${p0.y}, ${cpX2} ${p1.y}, ${p1.x} ${p1.y}`;
    }
    return d;
});

const hoveredPoint = computed<ChartPoint | null>(() => {
    if (hoveredIndex.value === null) return null;
    return chartPoints.value[hoveredIndex.value] || null;
});

const onPointClick = (pt: ChartPoint) => {
    emit('point-clicked', {
        month: pt.month,
        val: pt.val,
        year: props.data.year,
    });
};
</script>

<template>
    <div class="chart-stage">
        <svg
            class="chart-svg"
            viewBox="0 0 720 220"
            preserveAspectRatio="none"
            aria-label="Gráfica analítica de tickets"
        >
            <!-- Líneas de rejilla -->
            <g class="chart-grid">
                <line class="grid-line" x1="40" y1="20" x2="700" y2="20" />
                <line class="grid-line" x1="40" y1="56" x2="700" y2="56" />
                <line class="grid-line" x1="40" y1="92" x2="700" y2="92" />
                <line class="grid-line" x1="40" y1="128" x2="700" y2="128" />
                <line class="baseline" x1="40" y1="165" x2="700" y2="165" />

                <text class="axis-text" x="12" y="24">40</text>
                <text class="axis-text" x="12" y="60">30</text>
                <text class="axis-text" x="12" y="96">20</text>
                <text class="axis-text" x="12" y="132">10</text>
                <text class="axis-text" x="16" y="169">0</text>
            </g>

            <!-- Capa de barras -->
            <g class="bars-layer">
                <template v-for="(pt, idx) in chartPoints" :key="'bar-' + idx">
                    <rect
                        class="chart-bar-bg"
                        :x="pt.x - barWidth / 2"
                        :y="topY"
                        :width="barWidth"
                        :height="chartHeight"
                        @mouseenter="hoveredIndex = idx"
                        @mouseleave="hoveredIndex = null"
                        @click="onPointClick(pt)"
                    />
                    <rect
                        class="chart-bar"
                        :x="pt.x - barWidth / 2"
                        :y="baseY - pt.barHeight"
                        :width="barWidth"
                        :height="pt.barHeight"
                        rx="4"
                        @mouseenter="hoveredIndex = idx"
                        @mouseleave="hoveredIndex = null"
                        @click="onPointClick(pt)"
                    />
                </template>
            </g>

            <!-- Guía vertical cruzada -->
            <line
                v-if="hoveredPoint"
                class="chart-crosshair"
                :x1="hoveredPoint.x"
                :x2="hoveredPoint.x"
                y1="16"
                y2="165"
            />

            <!-- Línea spline suave -->
            <path class="chart-line" :d="pathD" />

            <!-- Capa de puntos -->
            <g class="points-layer">
                <circle
                    v-for="(pt, idx) in chartPoints"
                    :key="'dot-' + idx"
                    class="chart-point"
                    :class="{ active: hoveredIndex === idx }"
                    :cx="pt.x"
                    :cy="pt.y"
                    r="3.8"
                    @mouseenter="hoveredIndex = idx"
                    @mouseleave="hoveredIndex = null"
                    @click="onPointClick(pt)"
                />
            </g>

            <!-- Etiquetas del eje X -->
            <g class="labels-layer">
                <text
                    v-for="(pt, idx) in chartPoints"
                    :key="'label-' + idx"
                    class="axis-text"
                    :x="pt.x"
                    y="192"
                    text-anchor="middle"
                >
                    {{ pt.month }}
                </text>
            </g>

            <!-- Tooltip en SVG sin estilos inline -->
            <g
                v-if="hoveredPoint"
                class="svg-tooltip-wrap"
                :transform="`translate(${hoveredPoint.x}, ${Math.max(hoveredPoint.y - 46, 12)})`"
            >
                <rect
                    class="tooltip-box"
                    x="-58"
                    y="-6"
                    width="116"
                    height="40"
                    rx="10"
                />
                <text class="tooltip-month" x="0" y="11" text-anchor="middle">
                    {{ hoveredPoint.month }} {{ data.year }}
                </text>
                <text class="tooltip-value" x="0" y="26" text-anchor="middle">
                    {{ hoveredPoint.val }} tickets
                </text>
            </g>
        </svg>
    </div>
</template>

<style scoped>
.chart-stage {
    position: relative;
    width: 100%;
    height: 220px;
}

.chart-svg {
    width: 100%;
    height: 100%;
    display: block;
    overflow: visible;
}

.grid-line {
    stroke: var(--stroke);
    stroke-width: 1;
    stroke-dasharray: 4 6;
}

.baseline {
    stroke: var(--stroke);
    stroke-width: 1.5;
}

.axis-text {
    fill: var(--text-dim);
    font-size: 11px;
    font-family: var(--font-sans);
}

.chart-bar-bg {
    fill: transparent;
    cursor: pointer;
}

.chart-bar {
    fill: var(--orange);
    cursor: pointer;
    transition: transform var(--transition-fast);
    transform-box: fill-box;
    transform-origin: bottom center;
}

.chart-bar:hover {
    transform: scaleY(1.025);
    filter: brightness(1.1);
}

.chart-line {
    fill: none;
    stroke: var(--orange);
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.chart-point {
    fill: var(--bg-card);
    stroke: var(--orange);
    stroke-width: 2.5;
    cursor: pointer;
    transition: r var(--transition-fast), stroke-width var(--transition-fast);
}

.chart-point:hover,
.chart-point.active {
    r: 5.5;
    stroke-width: 3;
    fill: var(--orange);
}

.chart-crosshair {
    stroke: var(--stroke-hover);
    stroke-width: 1;
    stroke-dasharray: 3 3;
    pointer-events: none;
}

.tooltip-box {
    fill: var(--bg-card);
    stroke: var(--stroke);
    stroke-width: var(--stroke-w);
}

.tooltip-month {
    fill: var(--text);
    font-size: 11px;
    font-weight: 700;
    font-family: var(--font-sans);
}

.tooltip-value {
    fill: var(--orange);
    font-size: 11px;
    font-family: var(--font-sans);
}

@media (max-width: 768px) {
    .chart-stage {
        height: 200px;
    }
}
</style>
