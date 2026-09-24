<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    label: string;
    value?: string | number | null;
    subtext?: string;
    trend?: 'up' | 'down' | 'neutral' | '';
    icon?: string;
    color?: 'brand' | 'orange' | 'blue' | 'green' | 'yellow' | 'purple' | 'red' | 'cyan';
    active?: boolean;
    clickable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    value: 0,
    subtext: '',
    trend: '',
    icon: '',
    color: 'brand',
    active: false,
    clickable: false,
});

defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();

const isFontAwesome = (ic?: string): boolean => {
    if (!ic) return false;
    return ic.startsWith('fa-') || ic.includes('fa ') || ic.includes('fa-solid') || ic.includes('fa-regular') || ic.includes('fa-brands');
};

const computedTrendClass = computed(() => {
    if (props.trend) {
        return props.trend === 'neutral' ? '' : props.trend;
    }
    if (!props.subtext) return '';
    if (props.subtext.includes('↗') || props.subtext.startsWith('+')) return 'up';
    if (props.subtext.includes('↘') || props.subtext.startsWith('-')) return 'down';
    return '';
});
</script>

<template>
    <div
        :class="[
            'glass-card kpi',
            color,
            `kpi-${color}`,
            { 'is-clickable': clickable }
        ]"
        role="region"
        :aria-label="label"
        @click="clickable ? $emit('click', $event) : undefined"
    >
        <div class="card-pad">
            <div class="kpi-info">
                <div class="kpi-label">{{ label }}</div>
                <div class="kpi-value">{{ value ?? 0 }}</div>
                <div v-if="subtext" :class="['kpi-change', computedTrendClass]">{{ subtext }}</div>
            </div>

            <div class="kpi-icon">
                <slot name="icon">
                    <i v-if="isFontAwesome(icon)" :class="icon" aria-hidden="true" />
                    <svg v-else-if="icon" viewBox="0 0 24 24">
                        <path :d="icon" />
                    </svg>
                </slot>
            </div>
        </div>

        <div class="accent-bottom"></div>
    </div>
</template>

<style scoped>
.glass-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    position: relative;
    overflow: hidden;
    transition: border-width 0.2s ease, border-color var(--transition-fast, 0.15s ease), transform var(--transition-normal, 0.2s cubic-bezier(0.2, 0.8, 0.2, 1));
    box-shadow: none !important;
}

.kpi {
    cursor: default;
    border: var(--stroke-w) solid var(--stroke);
    position: relative;
    overflow: hidden;
    box-shadow: none !important;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 84px;
    width: 100%;
}

.kpi.is-clickable {
    cursor: pointer;
}

.kpi:hover {
    transform: translateY(-2px);
    border-color: var(--stroke-hover);
}

.kpi .card-pad {
    padding: 16px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
}

.kpi-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    text-align: left;
}

.kpi-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: none !important;
    font-weight: 500;
    line-height: 1.2;
    text-align: left;
    width: 100%;
}

.kpi-value {
    font-size: 24px;
    font-weight: 700 !important;
    line-height: 1.1;
    color: var(--text);
    letter-spacing: -0.02em;
    text-align: left;
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.kpi-change {
    font-size: 12px;
    margin-top: 5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    color: var(--text-muted);
    text-align: left;
}

.kpi-change.up {
    color: var(--green);
}

.kpi-change.down {
    color: var(--red);
}

.kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    background: transparent;
    flex-shrink: 0;
}

.kpi-icon :deep(svg),
.kpi-icon svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.kpi-icon i {
    font-size: 18px;
    line-height: 1;
}

/* Colores de variantes */
.kpi.blue .kpi-icon, .kpi.kpi-blue .kpi-icon { color: var(--blue); }
.kpi.yellow .kpi-icon, .kpi.kpi-yellow .kpi-icon { color: var(--yellow); }
.kpi.purple .kpi-icon, .kpi.kpi-purple .kpi-icon { color: var(--purple); }
.kpi.green .kpi-icon, .kpi.kpi-green .kpi-icon { color: var(--green); }
.kpi.orange .kpi-icon, .kpi.kpi-orange .kpi-icon { color: var(--orange); }
.kpi.brand .kpi-icon, .kpi.kpi-brand .kpi-icon { color: var(--brand); }
.kpi.red .kpi-icon, .kpi.kpi-red .kpi-icon { color: var(--red); }
.kpi.cyan .kpi-icon, .kpi.kpi-cyan .kpi-icon { color: var(--cyan); }

/* Borde inferior de 4px con animación en hover / activo */
.accent-bottom {
    height: 4px;
    background: var(--orange);
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    transform: translateY(4px);
    transition: opacity 0.25s cubic-bezier(0.2, 0.8, 0.2, 1), transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.kpi:hover .accent-bottom {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .kpi {
        min-height: 72px;
    }

    .kpi .card-pad {
        padding: 12px 14px;
        gap: 8px;
    }

    .kpi-label {
        font-size: 11px;
        margin-bottom: 3px;
    }

    .kpi-value {
        font-size: 18px;
    }

    .kpi-icon {
        width: 32px;
        height: 32px;
    }

    .kpi-icon :deep(svg),
    .kpi-icon svg {
        width: 16px;
        height: 16px;
    }
}
</style>
