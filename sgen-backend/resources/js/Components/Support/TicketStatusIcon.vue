<script setup lang="ts">
interface Props {
    status: 'resolved' | 'process' | 'critical' | 'pending' | string;
}

defineProps<Props>();
</script>

<template>
    <div class="ticket-status-icon-wrap" :class="[`status-${status}`]" :title="'Estado: ' + status">
        <svg v-if="status === 'resolved'" viewBox="0 0 24 24" class="status-svg">
            <path d="m5 12 4 4L19 6" />
        </svg>
        <svg v-else-if="status === 'process'" viewBox="0 0 24 24" class="status-svg">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
        </svg>
        <svg v-else-if="status === 'critical'" viewBox="0 0 24 24" class="status-svg">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
        <svg v-else viewBox="0 0 24 24" class="status-svg">
            <circle cx="12" cy="12" r="9" />
            <polyline points="12 7 12 12 15 15" />
        </svg>
    </div>
</template>

<style scoped>
.ticket-status-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    flex-shrink: 0;
    color: var(--text-muted);
}

.status-svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.status-resolved {
    color: var(--green);
    border-color: rgba(22, 163, 74, 0.3);
    background: rgba(22, 163, 74, 0.08);
}

.status-process {
    color: var(--blue);
    border-color: rgba(37, 99, 235, 0.3);
    background: rgba(37, 99, 235, 0.08);
}

.status-critical {
    color: var(--red);
    border-color: rgba(220, 38, 38, 0.3);
    background: rgba(220, 38, 38, 0.08);
}

.status-pending {
    color: var(--yellow);
    border-color: rgba(202, 138, 4, 0.3);
    background: rgba(202, 138, 4, 0.08);
}
</style>
