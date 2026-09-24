<script setup lang="ts">
import { computed } from 'vue';
import { getInitials } from '@/Utils/strings';

interface Props {
    src?: string | null;
    name?: string | null;
    initials?: string | null;
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
    variant?: 'brand' | 'blue' | 'purple' | 'green' | 'amber' | 'rose' | 'neutral';
    shape?: 'circle' | 'rounded';
    status?: 'online' | 'offline' | 'busy' | 'away' | null;
}

const props = withDefaults(defineProps<Props>(), {
    src: null,
    name: '',
    initials: '',
    size: 'md',
    variant: 'brand',
    shape: 'circle',
    status: null,
});

const displayInitials = computed(() => {
    if (props.initials) return props.initials.toUpperCase();
    if (props.name) return getInitials(props.name);
    return '??';
});
</script>

<template>
    <div
        class="base-avatar"
        :class="[
            `avatar-${size}`,
            `shape-${shape}`,
            `variant-${variant}`,
            { 'has-image': !!src }
        ]"
        :title="name || ''"
        role="img"
        :aria-label="name || 'Avatar'"
    >
        <img
            v-if="src"
            :src="src"
            :alt="name || 'Avatar'"
            class="avatar-image"
        />
        <span v-else class="avatar-initials">
            {{ displayInitials }}
        </span>

        <span
            v-if="status"
            class="status-indicator"
            :class="`status-${status}`"
            :aria-label="`Estado: ${status}`"
        />
    </div>
</template>

<style scoped>
.base-avatar {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    user-select: none;
    font-weight: 800;
    border: 1px solid var(--stroke);
    box-shadow: none !important;
}

/* Shapes */
.shape-circle {
    border-radius: var(--radius-pill, 9999px);
}

.shape-rounded {
    border-radius: var(--radius-md, 8px);
}

/* Sizes */
.avatar-xs {
    width: 24px;
    height: 24px;
    font-size: 10px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 11px;
}

.avatar-md {
    width: 40px;
    height: 40px;
    font-size: 13px;
}

.avatar-lg {
    width: 48px;
    height: 48px;
    font-size: 16px;
}

.avatar-xl {
    width: 64px;
    height: 64px;
    font-size: 20px;
}

/* Image */
.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
}

.avatar-initials {
    letter-spacing: 0.02em;
    line-height: 1;
}

/* Variants */
.variant-brand {
    background: rgba(var(--brand-rgb, 59, 130, 246), 0.15);
    color: var(--brand, #3b82f6);
    border-color: rgba(var(--brand-rgb, 59, 130, 246), 0.3);
}

.variant-blue {
    background: rgba(37, 99, 235, 0.15);
    color: var(--blue, #2563eb);
    border-color: rgba(37, 99, 235, 0.3);
}

.variant-purple {
    background: rgba(139, 92, 246, 0.15);
    color: #8b5cf6;
    border-color: rgba(139, 92, 246, 0.3);
}

.variant-green {
    background: rgba(16, 185, 129, 0.15);
    color: var(--green, #10b981);
    border-color: rgba(16, 185, 129, 0.3);
}

.variant-amber {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.3);
}

.variant-rose {
    background: rgba(244, 63, 94, 0.15);
    color: #f43f5e;
    border-color: rgba(244, 63, 94, 0.3);
}

.variant-neutral {
    background: var(--bg-sub);
    color: var(--text-muted);
    border-color: var(--stroke);
}

/* Status Indicator */
.status-indicator {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 25%;
    height: 25%;
    min-width: 8px;
    min-height: 8px;
    border-radius: var(--radius-pill, 9999px);
    border: 2px solid var(--bg-card);
}

.status-online {
    background: var(--green, #10b981);
}

.status-offline {
    background: var(--text-dim, #6b7280);
}

.status-busy {
    background: var(--red, #ef4444);
}

.status-away {
    background: var(--yellow, #f59e0b);
}
</style>
