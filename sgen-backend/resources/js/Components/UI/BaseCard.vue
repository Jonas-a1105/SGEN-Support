<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
    padding?: 'none' | 'sm' | 'md' | 'lg';
    variant?: 'glass' | 'subtle';
    rounded?: 'card' | 'panel' | 'pill';
    interactive?: boolean;
}

withDefaults(defineProps<Props>(), {
    padding: 'md',
    variant: 'glass',
    rounded: 'panel',
    interactive: false,
});
</script>

<template>
    <div
        :class="[
            'base-card',
            `card-variant-${variant}`,
            `card-pad-${padding}`,
            `card-rounded-${rounded}`,
            { 'is-interactive': interactive },
        ]"
    >
        <header v-if="$slots.header || title" class="base-card-header">
            <slot name="header">
                <div class="base-card-title-wrap">
                    <h3 v-if="title" class="base-card-title">{{ title }}</h3>
                    <p v-if="subtitle" class="base-card-subtitle">{{ subtitle }}</p>
                </div>
            </slot>
            <div v-if="$slots.actions" class="base-card-actions">
                <slot name="actions" />
            </div>
        </header>

        <div class="base-card-body">
            <slot />
        </div>

        <footer v-if="$slots.footer" class="base-card-footer">
            <slot name="footer" />
        </footer>
    </div>
</template>

<style scoped>
.base-card {
    border: var(--stroke-w) solid var(--stroke);
    position: relative;
    overflow: hidden;
    transition: border-color var(--transition-fast), border-width var(--transition-fast);
}

.card-variant-glass {
    background: var(--bg-card);
}

.card-variant-subtle {
    background: var(--bg-sub);
}

.card-rounded-card {
    border-radius: var(--card-radius);
}

.card-rounded-panel {
    border-radius: var(--panel-radius);
}

.card-rounded-pill {
    border-radius: var(--radius-pill);
}

.card-pad-none {
    padding: 0;
}

.card-pad-sm {
    padding: var(--space-3);
}

.card-pad-md {
    padding: var(--space-5);
}

.card-pad-lg {
    padding: var(--space-6);
}

.is-interactive {
    cursor: pointer;
}

.is-interactive:hover {
    border-color: var(--stroke-hover);
}

.base-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-3);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: var(--space-3);
    margin-bottom: var(--space-4);
}

.base-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    line-height: 1.25;
}

.base-card-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    margin: var(--space-1) 0 0 0;
}

.base-card-actions {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.base-card-body {
    width: 100%;
}

.base-card-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: var(--space-2);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    padding-top: var(--space-3);
    margin-top: var(--space-4);
}
</style>
