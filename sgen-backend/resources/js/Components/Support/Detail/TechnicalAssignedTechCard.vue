<script setup lang="ts">
defineProps<{
    techName: string;
    techInitial: string;
}>();

const emit = defineEmits<{
    (e: 'open-assign-tech'): void;
}>();
</script>

<template>
    <div class="assigned-tech-box">
        <span class="tech-card-header-label">Técnico Asignado</span>
        <div class="tech-info-row">
            <div class="tech-large-avatar">{{ techInitial }}</div>
            <div>
                <strong class="tech-name">{{ techName }}</strong>
                <div class="tech-status-row">
                    <span class="tech-status-dot"></span>
                    <span>Asignado</span>
                </div>
            </div>
        </div>
        <button class="btn-assign-action" type="button" @click="emit('open-assign-tech')">
            <svg viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            <span>Reasignar o Cambiar</span>
        </button>
    </div>
</template>

<style scoped>
.assigned-tech-box {
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    background: var(--bg-card);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: none !important;
}
.tech-card-header-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
}
.tech-info-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.tech-large-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.15);
    color: var(--blue);
    display: grid;
    place-items: center;
    font-size: 16px;
    font-weight: 700;
    border: var(--stroke-w) solid rgba(59, 130, 246, 0.3);
    flex-shrink: 0;
}
.tech-name {
    font-size: 14px;
    color: var(--text);
    display: block;
}
.tech-status-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #10b981;
}
.tech-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}
.btn-assign-action {
    height: 38px;
    border-radius: 10px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text);
    padding: 0 14px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.16s ease;
}
.btn-assign-action:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.btn-assign-action svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
