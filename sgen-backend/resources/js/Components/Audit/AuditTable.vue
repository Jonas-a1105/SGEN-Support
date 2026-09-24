<script setup lang="ts">
import { BaseAvatar, BaseDataTable, type DataTableColumn } from '@/Components/UI';
import type { AuditSessionItem } from '@/Composables/useAuditFilters';

defineProps<{
    sessions: AuditSessionItem[];
}>();

const emit = defineEmits<{
    (e: 'view', session: AuditSessionItem): void;
}>();

const columns: DataTableColumn[] = [
    { key: 'usuario', label: 'USUARIO', sortable: true },
    { key: 'start', label: 'INICIO DE SESIÓN', sortable: true },
    { key: 'duration', label: 'DURACIÓN / ESTADO' },
    { key: 'status', label: 'ESTADO', width: '100px', align: 'center' },
    { key: 'actions', label: 'ACCIONES', width: '100px', align: 'right' },
];
</script>

<template>
    <div class="audit-table-wrap">
        <BaseDataTable
            :columns="columns"
            :items="sessions"
            empty-title="No se encontraron sesiones registradas"
            empty-subtitle="Ajusta los filtros o los términos de búsqueda."
        >
            <template #cell-usuario="{ item }">
                <div class="user-info-row">
                    <BaseAvatar :name="item.username" :initials="item.avatar_initials" size="sm" />
                    <div>
                        <strong class="user-name-title">{{ item.username }}</strong>
                        <span class="user-id-desc">ID: {{ item.user_id }}</span>
                    </div>
                </div>
            </template>

            <template #cell-start="{ item }">
                <div class="date-cell-wrap">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                    <span>{{ item.start_formatted }}</span>
                </div>
            </template>

            <template #cell-duration="{ item }">
                <span v-if="item.is_active" class="status-pill-active">
                    <span class="dot-active"></span>
                    En curso
                </span>
                <span v-else class="status-pill-ended">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                    <span>Duración: {{ item.duration }}</span>
                </span>
            </template>

            <template #cell-status="{ item }">
                <div
                    class="session-status-icon-circle"
                    :class="item.is_active ? 'active' : 'ended'"
                    :title="item.is_active ? 'Sesión activa en el sistema' : 'Sesión finalizada'"
                >
                    <svg v-if="item.is_active" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" /></svg>
                    <svg v-else viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                </div>
            </template>

            <template #cell-actions="{ item }">
                <button class="btn-action-view" type="button" @click="emit('view', item)">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                    <span>Ver</span>
                </button>
            </template>
        </BaseDataTable>
    </div>
</template>

<style scoped>
.user-info-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-name-title {
    font-size: 14px;
    color: var(--text, #f4f4f6);
    display: block;
}
.user-id-desc {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.date-cell-wrap {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-muted, #8e9199);
    font-size: 13px;
}
.date-cell-wrap svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

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
.dot-active {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}

.status-pill-ended {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 12px;
    background: var(--bg-sub, #1e2024);
    color: var(--text-muted, #8e9199);
    font-size: 11px;
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}
.status-pill-ended svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.session-status-icon-circle {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: inline-grid;
    place-items: center;
    border: var(--stroke-w, 2px) solid transparent;
    box-shadow: none !important;
}
.session-status-icon-circle.active {
    background: rgba(16, 185, 129, 0.15);
    border-color: rgba(16, 185, 129, 0.3);
    color: #10b981;
}
.session-status-icon-circle.ended {
    background: var(--bg-sub, #1e2024);
    border-color: var(--stroke-subtle, #23252a);
    color: var(--text-dim, #60636d);
}
.session-status-icon-circle svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
}

.btn-action-view {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    font-size: 12px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}
.btn-action-view svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.btn-action-view:hover {
    color: var(--text, #f4f4f6);
    border-color: #3b82f6;
}
</style>
