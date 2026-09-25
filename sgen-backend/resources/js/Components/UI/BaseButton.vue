<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost' | 'outline' | 'subtle' | 'warning';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
    href?: string;
}

withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    href: undefined,
});

defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        :class="['btn-base', `btn-${variant}`, `btn-${size}`, { 'is-loading': loading, 'is-disabled': disabled }]"
        @click="$emit('click', $event)"
    >
        <span v-if="loading" class="btn-spinner" />
        <span class="btn-content">
            <slot />
        </span>
    </Link>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :class="['btn-base', `btn-${variant}`, `btn-${size}`, { 'is-loading': loading }]"
        @click="$emit('click', $event)"
    >
        <span v-if="loading" class="btn-spinner" />
        <span class="btn-content">
            <slot />
        </span>
    </button>
</template>

<style scoped>
.btn-base {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    font-weight: 600;
    font-family: var(--font-sans);
    border-radius: var(--radius-md);
    border: var(--stroke-w) solid transparent;
    cursor: pointer;
    transition: all var(--transition-fast);
    box-shadow: none !important;
    text-shadow: none !important;
    filter: none !important;
    white-space: nowrap;
}

.btn-sm {
    height: 32px;
    padding: 0 var(--space-3);
    font-size: 12px;
}

.btn-md {
    height: 38px;
    padding: 0 var(--space-4);
    font-size: 13px;
}

.btn-lg {
    height: 44px;
    padding: 0 var(--space-5);
    font-size: 14px;
}

.btn-base:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background-color: var(--orange);
    border-color: var(--orange);
    color: #ffffff;
}
.btn-primary:hover:not(:disabled) {
    background-color: var(--orange-light);
    border-color: var(--orange-light);
}

.btn-secondary {
    background-color: var(--bg-card);
    border-color: var(--stroke);
    color: var(--text);
}
.btn-secondary:hover:not(:disabled) {
    background-color: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.btn-outline {
    background-color: transparent;
    border-color: var(--stroke);
    color: var(--text);
}
.btn-outline:hover:not(:disabled) {
    background-color: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.btn-danger {
    background-color: var(--red);
    border-color: var(--red);
    color: #ffffff;
}
.btn-danger:hover:not(:disabled) {
    opacity: 0.9;
}

.btn-ghost {
    background-color: transparent;
    border-color: transparent;
    color: var(--text-muted);
}
.btn-ghost:hover:not(:disabled):not(.is-disabled) {
    background-color: var(--stroke-subtle);
    color: var(--text);
}

.btn-subtle {
    background-color: var(--stroke-subtle);
    border-color: var(--stroke);
    color: var(--text);
}
.btn-subtle:hover:not(:disabled):not(.is-disabled) {
    background-color: var(--stroke);
    border-color: var(--stroke-hover);
}

.btn-warning {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #ffffff;
}
.btn-warning:hover:not(:disabled):not(.is-disabled) {
    opacity: 0.9;
}

.btn-base:disabled,
.btn-base.is-disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.btn-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

.btn-content {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
