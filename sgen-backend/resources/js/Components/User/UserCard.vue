<script setup lang="ts">
import type { UserItem } from '@/Composables/useUserFilters';

defineProps<{
    user: UserItem;
}>();

const emit = defineEmits<{
    (e: 'edit', user: UserItem): void;
    (e: 'delete', user: UserItem): void;
}>();

function getRoleToneClass(rol: string): string {
    const map: Record<string, string> = {
        admin: 'role-admin',
        tecnico: 'role-tecnico',
        consultor: 'role-consultor',
        operador: 'role-operador',
    };
    return map[rol.toLowerCase()] || 'role-consultor';
}
</script>

<template>
    <div class="user-card">
        <div class="card-top-bar" :class="getRoleToneClass(user.rol)"></div>

        <div class="card-inner-pad">
            <div class="card-header-row">
                <div class="avatar-circle" :class="getRoleToneClass(user.rol)">
                    {{ user.avatar_initials }}
                </div>
                <div class="header-copy">
                    <h3 class="user-title">{{ user.username }}</h3>
                    <span v-if="user.empleado_nombre" class="employee-name">
                        {{ user.empleado_nombre }}
                    </span>
                    <span v-else class="employee-name-muted">Sin vincular</span>
                </div>
            </div>

            <div class="pills-row">
                <span class="role-pill" :class="getRoleToneClass(user.rol)">
                    {{ user.rol_label }}
                </span>
                <span class="dept-pill">{{ user.departamento_nombre }}</span>
            </div>

            <div class="card-footer-row">
                <span class="status-indicator">
                    <span class="status-dot"></span>
                    Activo
                </span>
                <div class="actions-group">
                    <button class="btn-card-action" type="button" title="Editar" @click="emit('edit', user)">
                        <svg viewBox="0 0 24 24"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" /></svg>
                    </button>
                    <button class="btn-card-action delete" type="button" title="Eliminar" @click="emit('delete', user)">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.user-card {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--card-radius, 14px);
    overflow: hidden;
    position: relative;
    box-shadow: none !important;
    transition: border-color 0.15s ease;
}
.user-card:hover {
    border-color: #4f46e5;
}
.card-top-bar {
    height: 4px;
    width: 100%;
}
.card-inner-pad {
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.card-header-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-weight: 700 !important;
    font-size: 14px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    flex-shrink: 0;
    box-shadow: none !important;
}
.header-copy {
    overflow: hidden;
}
.user-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.employee-name {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.employee-name-muted {
    font-size: 11px;
    color: var(--text-dim, #60636d);
}

.pills-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.role-pill {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600 !important;
    border: var(--stroke-w, 2px) solid transparent;
}
.dept-pill {
    padding: 3px 8px;
    border-radius: 6px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.card-footer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    padding-top: 12px;
}
.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #10b981;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}

.actions-group {
    display: flex;
    gap: 6px;
}
.btn-card-action {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}
.btn-card-action svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.btn-card-action:hover {
    color: var(--text, #f4f4f6);
    border-color: #4f46e5;
}
.btn-card-action.delete:hover {
    color: #ef4444;
    border-color: #ef4444;
}

.role-admin { background: rgba(16, 185, 129, 0.12); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
.role-tecnico { background: rgba(37, 99, 235, 0.12); color: #2563eb; border-color: rgba(37, 99, 235, 0.3); }
.role-consultor { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); }
.role-operador { background: rgba(245, 158, 11, 0.12); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3); }
</style>
