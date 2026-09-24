<script setup lang="ts">
defineProps<{
    managerName: string;
    managerInitials: string;
}>();

const emit = defineEmits<{
    (e: 'assign'): void;
    (e: 'clear'): void;
}>();
</script>

<template>
    <div class="manager-widget-box">
        <div class="manager-info-left">
            <div class="manager-avatar-circle">
                {{ managerInitials }}
            </div>
            <div class="manager-details">
                <strong class="manager-name-text">
                    {{ managerName || 'Sin responsable asignado' }}
                </strong>
                <span class="manager-role-desc">
                    {{ managerName ? 'Responsable asignado a la unidad operativa.' : 'Haga clic en Asignar Líder para designar al jefe de departamento.' }}
                </span>
            </div>
        </div>

        <div class="manager-actions-right">
            <button
                v-if="managerName"
                class="btn-clear-manager"
                @click="emit('clear')"
                type="button"
                title="Quitar asignación"
            >
                Quitar
            </button>
            <button
                class="btn-assign-manager"
                @click="emit('assign')"
                type="button"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                <span>{{ managerName ? 'Cambiar Líder' : 'Asignar Líder' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.manager-widget-box {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: none !important;
}

.manager-info-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.manager-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.15);
    border: var(--stroke-w) solid var(--blue);
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 700;
    color: #3b82f6;
    flex-shrink: 0;
}

.manager-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.manager-name-text {
    font-size: 14px;
    color: var(--text);
}

.manager-role-desc {
    font-size: 11px;
    color: var(--text-dim);
}

.manager-actions-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-clear-manager {
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-clear-manager:hover {
    color: var(--red);
    border-color: var(--red);
}

.btn-assign-manager {
    background: var(--blue);
    border: var(--stroke-w) solid var(--blue);
    color: #ffffff;
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-weight: 600;
    box-shadow: none !important;
    transition: opacity 0.2s ease;
}

.btn-assign-manager svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}

.btn-assign-manager:hover {
    opacity: 0.9;
}
</style>
