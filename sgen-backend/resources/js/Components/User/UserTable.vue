<script setup lang="ts">
import type { UserItem } from '@/Composables/useUserFilters';

defineProps<{
    users: UserItem[];
    isDense?: boolean;
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
    <div class="users-table-card" :class="{ 'is-dense': isDense }">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th class="th-user">USUARIO</th>
                        <th class="th-role">ROL & DEPTO</th>
                        <th class="th-status">ESTADO</th>
                        <th class="th-actions">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="table-row">
                        <td class="td-user">
                            <div class="user-info-wrap">
                                <div class="avatar-box" :class="getRoleToneClass(user.rol)">
                                    {{ user.avatar_initials }}
                                </div>
                                <div>
                                    <strong class="user-name-title">{{ user.username }}</strong>
                                    <span v-if="user.empleado_nombre" class="user-sub-desc">
                                        {{ user.empleado_nombre }}
                                        <span v-if="user.is_verified_employee" class="verified-dot" title="Colaborador vinculado">●</span>
                                    </span>
                                    <span v-else class="user-sub-desc-muted">Sin colaborador vinculado</span>
                                </div>
                            </div>
                        </td>

                        <td class="td-role">
                            <div class="role-dept-wrap">
                                <span class="role-badge" :class="getRoleToneClass(user.rol)">
                                    {{ user.rol_label }}
                                </span>
                                <span class="dept-text">{{ user.departamento_nombre }}</span>
                            </div>
                        </td>

                        <td class="td-status">
                            <span class="status-pill-active">
                                <span class="status-pulse-dot"></span>
                                Activo
                            </span>
                        </td>

                        <td class="td-actions">
                            <div class="actions-group">
                                <button class="btn-action edit" type="button" title="Editar usuario" @click="emit('edit', user)">
                                    <svg viewBox="0 0 24 24"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" /></svg>
                                </button>
                                <button class="btn-action delete" type="button" title="Eliminar usuario" @click="emit('delete', user)">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="4" class="empty-cell">
                            <div class="empty-state-box">
                                <div class="empty-icon-circle">
                                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
                                </div>
                                <strong class="empty-title">No se encontraron usuarios</strong>
                                <span class="empty-subtitle">Prueba con otros términos de búsqueda.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.users-table-card {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--card-radius, 14px);
    overflow: hidden;
    box-shadow: none !important;
}
.table-responsive {
    overflow-x: auto;
}
.custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
}
.custom-table thead th {
    background: var(--bg-sub, #1e2024);
    color: var(--text-muted, #8e9199);
    font-size: 11px;
    letter-spacing: 0.05em;
    padding: 12px 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke, #31343a);
}
.custom-table tbody td {
    padding: 14px 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    vertical-align: middle;
}
.is-dense .custom-table tbody td {
    padding: 8px 16px;
}
.custom-table tbody tr:last-child td {
    border-bottom: none;
}
.th-user { width: 35%; }
.th-role { width: 35%; }
.th-status { width: 15%; }
.th-actions { width: 15%; text-align: right; }
.td-actions { text-align: right; }

.user-info-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}
.avatar-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-weight: 700 !important;
    font-size: 13px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    box-shadow: none !important;
}
.user-name-title {
    font-size: 14px;
    color: var(--text, #f4f4f6);
    display: block;
}
.user-sub-desc {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
    display: flex;
    align-items: center;
    gap: 4px;
}
.user-sub-desc-muted {
    font-size: 11px;
    color: var(--text-dim, #60636d);
}
.verified-dot {
    color: #10b981;
    font-size: 10px;
}

.role-dept-wrap {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.role-badge {
    align-self: flex-start;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600 !important;
    border: var(--stroke-w, 2px) solid transparent;
}
.dept-text {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.role-admin { background: rgba(16, 185, 129, 0.12); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
.role-tecnico { background: rgba(37, 99, 235, 0.12); color: #2563eb; border-color: rgba(37, 99, 235, 0.3); }
.role-consultor { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); }
.role-operador { background: rgba(245, 158, 11, 0.12); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3); }

.status-pill-active {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    font-size: 11px;
    border: var(--stroke-w, 2px) solid rgba(16, 185, 129, 0.3);
}
.status-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}

.actions-group {
    display: inline-flex;
    gap: 6px;
}
.btn-action {
    width: 32px;
    height: 32px;
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
.btn-action svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.btn-action.edit:hover {
    color: var(--text, #f4f4f6);
    border-color: #4f46e5;
}
.btn-action.delete:hover {
    color: #ef4444;
    border-color: #ef4444;
}

.empty-cell {
    padding: 48px 20px;
    text-align: center;
}
.empty-state-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.empty-icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    display: grid;
    place-items: center;
    color: var(--text-muted, #8e9199);
}
.empty-icon-circle svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.empty-title {
    font-size: 14px;
    color: var(--text, #f4f4f6);
}
.empty-subtitle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
</style>
