<script setup lang="ts">
import type { AuditSessionItem } from '@/Composables/useAuditFilters';

defineProps<{
    sessions: AuditSessionItem[];
}>();

const emit = defineEmits<{
    (e: 'view', session: AuditSessionItem): void;
}>();
</script>

<template>
    <div class="audit-panel-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th class="th-user">USUARIO</th>
                        <th class="th-start">INICIO DE SESIÓN</th>
                        <th class="th-duration">DURACIÓN / ESTADO</th>
                        <th class="th-status">ESTADO</th>
                        <th class="th-actions">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="session in sessions" :key="session.id" class="table-row">
                        <td class="td-user">
                            <div class="user-info-row">
                                <div class="avatar-mini">{{ session.avatar_initials }}</div>
                                <div>
                                    <strong class="user-name-title">{{ session.username }}</strong>
                                    <span class="user-id-desc">ID: {{ session.user_id }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="td-start">
                            <div class="date-cell-wrap">
                                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                                <span>{{ session.start_formatted }}</span>
                            </div>
                        </td>

                        <td class="td-duration">
                            <span v-if="session.is_active" class="status-pill-active">
                                <span class="dot-active"></span>
                                En curso
                            </span>
                            <span v-else class="status-pill-ended">
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                                <span>Duración: {{ session.duration }}</span>
                            </span>
                        </td>

                        <td class="td-status">
                            <div
                                class="session-status-icon-circle"
                                :class="session.is_active ? 'active' : 'ended'"
                                :title="session.is_active ? 'Sesión activa en el sistema' : 'Sesión finalizada'"
                            >
                                <svg v-if="session.is_active" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                                <svg v-else viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" y1="12" x2="9" y2="12" /></svg>
                            </div>
                        </td>

                        <td class="td-actions">
                            <button class="btn-action-view" type="button" @click="emit('view', session)">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                                <span>Ver</span>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="sessions.length === 0">
                        <td colspan="5" class="empty-cell">
                            <div class="empty-state-box">
                                <div class="empty-icon-circle">
                                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
                                </div>
                                <strong class="empty-title">No se encontraron sesiones registradas</strong>
                                <span class="empty-subtitle">Ajusta los filtros o los términos de búsqueda.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.audit-panel-card {
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
.custom-table tbody tr:last-child td {
    border-bottom: none;
}
.th-user { width: 30%; }
.th-start { width: 25%; }
.th-duration { width: 22%; }
.th-status { width: 10%; text-align: center; }
.th-actions { width: 13%; text-align: right; }
.td-status { text-align: center; }
.td-actions { text-align: right; }

.user-info-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.avatar-mini {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #3b82f6;
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 700 !important;
    border: var(--stroke-w, 2px) solid #3b82f6;
    box-shadow: none !important;
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
