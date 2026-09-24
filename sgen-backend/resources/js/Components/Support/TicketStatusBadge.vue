<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    status: 'resolved' | 'process' | 'critical' | 'pending' | string;
    label?: string;
}

const props = defineProps<Props>();

const displayLabel = computed(() => {
    if (props.label) return props.label;
    return matchStatus(props.status);
});

const matchStatus = (st: string) => {
    switch (st) {
        case 'resolved':
            return 'Resuelto';
        case 'process':
            return 'En Proceso';
        case 'critical':
            return 'Crítico';
        case 'pending':
        default:
            return 'Pendiente';
    }
};
</script>

<template>
    <span class="status-badge" :class="[status]">
        <span class="badge-dot"></span>
        <span class="badge-text">{{ displayLabel }}</span>
    </span>
</template>

<style scoped>
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 3px 10px;
    border-radius: 999px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text-muted);
    line-height: 1.2;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

.status-badge.resolved {
    background: rgba(22, 163, 74, 0.12);
    color: var(--green);
    border-color: rgba(22, 163, 74, 0.35);
}

.status-badge.process {
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    border-color: rgba(37, 99, 235, 0.35);
}

.status-badge.critical {
    background: rgba(220, 38, 38, 0.12);
    color: var(--red);
    border-color: rgba(220, 38, 38, 0.35);
}

.status-badge.pending {
    background: rgba(202, 138, 4, 0.12);
    color: var(--yellow);
    border-color: rgba(202, 138, 4, 0.35);
}
</style>
