<script setup lang="ts">
import BaseModal from '@/Components/UI/BaseModal.vue';
import { BaseAvatar, BaseBadge } from '@/Components/UI';
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
    <BaseModal
        :is-open="show && !!session"
        title="Detalle de Sesión"
        max-width="md"
        @close="emit('close')"
    >
        <div v-if="session" class="session-modal-content">
            <div class="user-header-block">
                <BaseAvatar :name="session.username" :initials="session.avatar_initials" size="lg" />
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
                        <BaseBadge :variant="session.is_active ? 'success' : 'neutral'" size="sm">
                            {{ session.status }}
                        </BaseBadge>
                    </span>
                </div>
            </div>

            <div class="form-actions-row">
                <button class="btn-cancel" type="button" @click="emit('close')">Cerrar</button>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.session-modal-content {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-header-block {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-bottom: 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
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

.session-detail-rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 10px;
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
    font-size: 13px;
    font-weight: 600 !important;
    color: var(--text, #f4f4f6);
}

.active-text {
    color: #10b981;
}
</style>
