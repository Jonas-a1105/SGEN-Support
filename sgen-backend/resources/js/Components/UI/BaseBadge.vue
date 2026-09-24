<script setup lang="ts">
interface Props {
    variant?: 'success' | 'danger' | 'warning' | 'info' | 'accent' | 'code' | 'neutral' | 'outline';
    size?: 'sm' | 'md';
    dot?: boolean;
}

withDefaults(defineProps<Props>(), {
    variant: 'neutral',
    size: 'sm',
    dot: false,
});
</script>

<template>
    <span :class="['base-badge', `badge-${variant}`, `badge-${size}`]">
        <span v-if="dot" class="badge-dot" />
        <slot />
    </span>
</template>

<style scoped>
.base-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
    border-radius: var(--radius-pill);
    transition: all var(--transition-fast);
}

.badge-sm {
    font-size: 11px;
    padding: 3px 8px;
}

.badge-md {
    font-size: 12px;
    padding: 4px 10px;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* Variantes Semánticas Basadas en Tokens Globales */
.badge-neutral {
    background: var(--stroke-subtle);
    border: 1px solid var(--stroke);
    color: var(--text-muted);
}

.badge-code {
    background: var(--stroke-subtle);
    border: 1px solid var(--stroke);
    color: var(--text-muted);
    font-family: var(--font-mono);
    border-radius: var(--radius-sm);
}

.badge-success {
    background: rgba(22, 163, 74, 0.15);
    border: 1px solid rgba(22, 163, 74, 0.3);
    color: var(--green);
}

.badge-danger {
    background: rgba(220, 38, 38, 0.15);
    border: 1px solid rgba(220, 38, 38, 0.3);
    color: var(--red);
}

.badge-warning {
    background: rgba(217, 119, 6, 0.15);
    border: 1px solid rgba(217, 119, 6, 0.3);
    color: var(--yellow);
}

.badge-info {
    background: rgba(37, 99, 235, 0.15);
    border: 1px solid rgba(37, 99, 235, 0.3);
    color: var(--blue);
}

.badge-accent {
    background: rgba(var(--orange-rgb), 0.15);
    border: 1px solid rgba(var(--orange-rgb), 0.3);
    color: var(--orange);
}

.badge-outline {
    background: transparent;
    border: 1px solid var(--stroke);
    color: var(--text-muted);
}
</style>
