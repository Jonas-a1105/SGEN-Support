<script setup lang="ts">
import { BaseAvatar, BaseDataTable, type DataTableColumn } from '@/Components/UI';
import type { UserItem } from '@/Composables/useUserFilters';

defineProps<{
    users: UserItem[];
    isDense?: boolean;
}>();

const emit = defineEmits<{
    (e: 'edit', user: UserItem): void;
    (e: 'delete', user: UserItem): void;
}>();

const columns: DataTableColumn[] = [
    { key: 'usuario', label: 'USUARIO', sortable: true },
    { key: 'rol', label: 'ROL & DEPTO', sortable: true },
    { key: 'estado', label: 'ESTADO', width: '130px' },
    { key: 'actions', label: 'ACCIONES', width: '110px', align: 'right' },
];

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
    <div :class="{ 'is-dense': isDense }">
        <BaseDataTable
            :columns="columns"
            :items="users"
            empty-title="No se encontraron usuarios"
            empty-subtitle="Prueba con otros términos de búsqueda."
        >
            <template #cell-usuario="{ item }">
                <div class="user-info-wrap">
                    <BaseAvatar :name="item.username" :initials="item.avatar_initials" size="md" />
                    <div>
                        <strong class="user-name-title">{{ item.username }}</strong>
                        <span v-if="item.empleado_nombre" class="user-sub-desc">
                            {{ item.empleado_nombre }}
                            <span v-if="item.is_verified_employee" class="verified-dot" title="Colaborador vinculado">●</span>
                        </span>
                        <span v-else class="user-sub-desc-muted">Sin colaborador vinculado</span>
                    </div>
                </div>
            </template>

            <template #cell-rol="{ item }">
                <div class="role-dept-wrap">
                    <span class="role-badge" :class="getRoleToneClass(item.rol)">
                        {{ item.rol_label }}
                    </span>
                    <span class="dept-text">{{ item.departamento_nombre }}</span>
                </div>
            </template>

            <template #cell-estado>
                <span class="status-pill-active">
                    <span class="status-pulse-dot"></span>
                    Activo
                </span>
            </template>

            <template #cell-actions="{ item }">
                <div class="actions-group">
                    <button class="btn-action edit" type="button" title="Editar usuario" @click="emit('edit', item)">
                        <svg viewBox="0 0 24 24"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" /></svg>
                    </button>
                    <button class="btn-action delete" type="button" title="Eliminar usuario" @click="emit('delete', item)">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                    </button>
                </div>
            </template>
        </BaseDataTable>
    </div>
</template>

<style scoped>
.user-info-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
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
</style>
