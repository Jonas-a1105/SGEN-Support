<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { BaseAvatar, BaseBadge } from '@/Components/UI';
import type { EmployeeItem } from '@/Composables/useEmployeeFilters';

const props = defineProps<{
    employee: EmployeeItem;
    isDense?: boolean;
}>();

const emit = defineEmits<{
    (e: 'edit', employee: EmployeeItem): void;
    (e: 'delete', employee: EmployeeItem): void;
}>();

const navigateToDetail = () => {
    router.visit(`/personal/${props.employee.numericId}`);
};
</script>

<template>
    <article
        class="employee-card"
        :class="{ dense: isDense }"
        role="button"
        tabindex="0"
        @click="navigateToDetail"
        @keydown.enter="navigateToDetail"
    >
        <div class="card-main-content">
            <div class="card-top-identity">
                <div class="card-user-row">
                    <BaseAvatar
                        :name="employee.fullName"
                        :initials="employee.initials"
                        size="md"
                    />
                    <div class="emp-identity-info">
                        <Link :href="`/personal/${employee.numericId}`" class="emp-name-link" @click.stop>
                            <h3 class="emp-name-text">{{ employee.fullName }}</h3>
                        </Link>
                        <span class="emp-position-sub">{{ employee.position }}</span>
                    </div>
                </div>

                <!-- ESCUDO DE ACCESO VERIFICADO VS NO VERIFICADO -->
                <div
                    class="shield-status-indicator"
                    :class="employee.userAccount ? 'shield-verified' : 'shield-unverified'"
                    :title="employee.userAccount ? 'Usuario activo en el sistema' : 'Sin cuenta de usuario activa'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <polyline v-if="employee.userAccount" points="9 12 11 14 15 10"></polyline>
                        <line v-else x1="12" y1="8" x2="12" y2="12"></line>
                    </svg>
                </div>
            </div>

            <div class="card-body-meta">
                <BaseBadge variant="info" size="sm" :title="employee.dept">
                    <template #icon>
                        <svg viewBox="0 0 24 24" class="badge-icon">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="2"></line>
                            <line x1="15" y1="22" x2="15" y2="2"></line>
                        </svg>
                    </template>
                    {{ employee.dept }}
                </BaseBadge>

                <div class="meta-info-line" :title="employee.email">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span>{{ employee.email }}</span>
                </div>

                <div class="meta-info-line">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <line x1="7" y1="8" x2="17" y2="8"></line>
                        <line x1="7" y1="12" x2="13" y2="12"></line>
                        <line x1="7" y1="16" x2="10" y2="16"></line>
                    </svg>
                    <span>{{ employee.idDoc }}</span>
                </div>
            </div>
        </div>

        <div class="card-bottom-actions-row">
            <div class="access-status-wrap">
                <span class="access-tag-label">ACCESO</span>
                <BaseBadge
                    :variant="employee.userAccount ? 'success' : 'neutral'"
                    size="sm"
                >
                    {{ employee.userAccount || 'Inactivo' }}
                </BaseBadge>
            </div>

            <div class="card-action-btns">
                <Link
                    :href="`/personal/${employee.numericId}`"
                    class="btn-mini-action view"
                    title="Ver perfil completo"
                    @click.stop
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </Link>
                <button
                    class="btn-mini-action edit"
                    @click.stop="emit('edit', employee)"
                    type="button"
                    title="Editar ficha"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
                <button
                    class="btn-mini-action delete"
                    @click.stop="emit('delete', employee)"
                    type="button"
                    title="Eliminar registro"
                >
                    <svg viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>
    </article>
</template>

<style scoped>
.employee-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--card-radius);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: border-color 0.2s ease, transform 0.2s ease;
    box-shadow: none !important;
    cursor: pointer;
}

.employee-card:hover {
    border-color: var(--stroke-hover);
    transform: translateY(-2px);
}

.employee-card.dense {
    padding: 12px;
}

.card-main-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card-top-identity {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.card-user-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.emp-identity-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.emp-name-link {
    text-decoration: none;
    color: inherit;
    display: block;
    overflow: hidden;
}

.emp-name-link:hover .emp-name-text {
    color: var(--brand);
}

.emp-name-text {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.emp-position-sub {
    font-size: 11px;
    color: var(--text-muted);
}

.shield-status-indicator {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: grid;
    place-items: center;
}

.shield-status-indicator svg {
    width: 16px;
    height: 16px;
}

.shield-verified {
    color: var(--green);
}

.shield-unverified {
    color: var(--text-dim);
}

.card-body-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: var(--bg-sub);
    border-radius: 8px;
    padding: 10px;
}

.badge-icon {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.meta-info-line {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.meta-info-line svg {
    width: 13px;
    height: 13px;
    stroke: var(--text-dim);
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.card-bottom-actions-row {
    margin-top: 14px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.access-status-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}

.access-tag-label {
    font-size: 10px;
    color: var(--text-dim);
    font-weight: 700;
    letter-spacing: 0.05em;
}

.card-action-btns {
    display: flex;
    gap: 4px;
}

.btn-mini-action {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-mini-action svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.btn-mini-action:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.btn-mini-action.view:hover {
    color: var(--brand);
    border-color: var(--brand);
}

.btn-mini-action.delete:hover {
    color: var(--red);
    border-color: var(--red);
}
</style>
