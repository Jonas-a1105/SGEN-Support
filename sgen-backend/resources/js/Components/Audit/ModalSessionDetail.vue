<script setup lang="ts">
import type { AuditSessionItem } from '@/Composables/useAuditFilters';

defineProps<{
    show: boolean;
    session?: AuditSessionItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();
</script>

<template>
    <div v-if="show && session" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalSessionTitle">
            <div class="modal-head">
                <div class="modal-head-title">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" /></svg>
                    <h3 id="modalSessionTitle" class="modal-title">Detalle de Sesión</h3>
                </div>
                <button class="icon-close" type="button" @click="emit('close')">×</button>
            </div>

            <div class="user-header-block">
                <div class="avatar-large">{{ session.avatar_initials }}</div>
                <div>
                    <h4 class="username-title">{{ session.username }}</h4>
                    <span class="userid-text">ID de Usuario: <strong>{{ session.user_id }}</strong></span>
                </div>
            </div>

            <div class="session-detail-rows">
                <div class="detail-row">
                    <span class="detail-label">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                        Inicio de Sesión
                    </span>
                    <span class="detail-val">{{ session.start_time }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                        Fin de Sesión
                    </span>
                    <span v-if="session.end_time" class="detail-val">{{ session.end_time }}</span>
                    <span v-else class="detail-val active-text">Sesión activa</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                        Duración
                    </span>
                    <span class="detail-val">{{ session.duration }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
                        Estado
                    </span>
                    <span class="detail-val">
                        <span class="status-badge" :class="session.is_active ? 'badge-active' : 'badge-ended'">
                            {{ session.status }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-close-modal" type="button" @click="emit('close')">Cerrar</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: grid;
    place-items: center;
    z-index: 100;
    padding: 16px;
    box-shadow: none !important;
}
.modal {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    width: 100%;
    max-width: 460px;
    padding: 24px;
    box-shadow: none !important;
}
.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}
.modal-head-title {
    display: flex;
    align-items: center;
    gap: 10px;
}
.modal-head-title svg {
    width: 18px;
    height: 18px;
    stroke: #3b82f6;
    fill: none;
    stroke-width: 2;
}
.modal-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}
.icon-close {
    background: transparent;
    border: none;
    color: var(--text-muted, #8e9199);
    font-size: 20px;
    cursor: pointer;
}
.user-header-block {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-bottom: 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    margin-bottom: 16px;
}
.avatar-large {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #3b82f6;
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 700 !important;
    border: var(--stroke-w, 2px) solid #3b82f6;
    box-shadow: none !important;
}
.username-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 2px 0;
}
.userid-text {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.userid-text strong {
    color: var(--text, #f4f4f6);
}

.session-detail-rows {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 10px;
    box-shadow: none !important;
}
.detail-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.detail-label svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.detail-val {
    font-size: 12px;
    color: var(--text, #f4f4f6);
}
.active-text {
    color: #10b981;
    font-weight: 600 !important;
}
.status-badge {
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 11px;
}
.badge-active {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: var(--stroke-w, 2px) solid rgba(16, 185, 129, 0.3);
}
.badge-ended {
    background: var(--bg-card, #17181a);
    color: var(--text-muted, #8e9199);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.modal-actions {
    margin-top: 20px;
}
.btn-close-modal {
    width: 100%;
    background: #3b82f6;
    border: var(--stroke-w, 2px) solid #3b82f6;
    color: #ffffff;
    border-radius: 10px;
    padding: 10px;
    font-size: 13px;
    font-weight: 700 !important;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}
.btn-close-modal:hover {
    background: #2563eb;
    border-color: #2563eb;
}
</style>
