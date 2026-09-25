<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton } from '@/Components/UI';
import { formatDate, formatDateTime } from '@/Utils/formatters';
import { useMaintenanceTimer } from '@/Composables/useMaintenanceTimer';
import type { MaintenanceDetail } from './types';

const props = defineProps<{
    maintenance: MaintenanceDetail;
}>();

const emit = defineEmits<{
    (e: 'complete'): void;
    (e: 'postpone'): void;
    (e: 'cancel'): void;
    (e: 'edit'): void;
}>();

const { timerStatus } = useMaintenanceTimer(
    () => props.maintenance.fecha,
    () => props.maintenance.duracion,
    () => props.maintenance.estado
);

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'completado': return 'success';
        case 'en_proceso': return 'info';
        case 'pendiente': return 'warning';
        case 'pospuesto': return 'neutral';
        case 'cancelado': return 'danger';
        default: return 'neutral';
    }
};

const getTypeBadgeVariant = (type: string) => {
    switch (type) {
        case 'preventivo': return 'brand';
        case 'correctivo': return 'warning';
        case 'predictivo': return 'info';
        default: return 'neutral';
    }
};

const triggerPrint = () => {
    window.open(`/mantenimientos/${props.maintenance.id}/pdf`, '_blank');
};
</script>

<template>
    <div class="maint-header-wrapper">
        <!-- Overdue Alert Banner -->
        <div v-if="maintenance.isOverdue" class="maint-alert-banner">
            <div class="maint-alert-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="maint-alert-content">
                <strong class="maint-alert-title">Mantenimiento con Plazo Expirado</strong>
                <span class="maint-alert-desc">
                    La fecha programada ({{ formatDateTime(maintenance.fecha) }}) ha concluido sin marcarse como completado.
                </span>
            </div>
            <BaseButton
                variant="primary"
                size="sm"
                @click="emit('complete')"
            >
                Marcar Realizado
            </BaseButton>
        </div>

        <!-- Page Header -->
        <header class="maint-header">
            <div class="maint-header-left">
                <Link href="/mantenimientos" class="maint-back-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Volver a Mantenimientos</span>
                </Link>

                <div class="maint-title-row">
                    <h1 class="maint-main-title">
                        Mantenimiento {{ maintenance.tipoMantenimiento }}
                    </h1>
                    <BaseBadge :variant="getStatusBadgeVariant(maintenance.estado)" size="md">
                        {{ maintenance.estado.replace('_', ' ') }}
                    </BaseBadge>
                    <BaseBadge :variant="getTypeBadgeVariant(maintenance.tipoMantenimiento)" size="md">
                        {{ maintenance.tipoMantenimiento }}
                    </BaseBadge>
                    <span class="maint-pill-duration">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        {{ maintenance.duracion || 60 }} min
                    </span>
                    <div v-if="timerStatus" class="maint-timer-chip" :class="timerStatus.class">
                        <span class="timer-dot"></span>
                        <span class="timer-text">{{ timerStatus.text }}</span>
                    </div>
                </div>

                <p class="maint-meta-sub">
                    ID: #{{ maintenance.id }} • Registrado el {{ formatDate(maintenance.createdAt || maintenance.fecha) }}
                </p>
            </div>

            <div class="maint-header-actions">
                <template v-if="maintenance.estado !== 'completado' && maintenance.estado !== 'cancelado'">
                    <BaseButton
                        variant="primary"
                        size="md"
                        @click="emit('complete')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Marcar Realizado</span>
                    </BaseButton>

                    <BaseButton
                        variant="warning"
                        size="md"
                        @click="emit('postpone')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 14 10"></polyline>
                        </svg>
                        <span>Posponer</span>
                    </BaseButton>

                    <BaseButton
                        variant="danger"
                        size="md"
                        @click="emit('cancel')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Cancelar</span>
                    </BaseButton>

                    <BaseButton variant="subtle" size="md" @click="emit('edit')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Editar</span>
                    </BaseButton>
                </template>

                <BaseButton variant="subtle" size="md" @click="triggerPrint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>Orden de Trabajo (PDF)</span>
                </BaseButton>
            </div>
        </header>
    </div>
</template>

<style scoped>
.maint-header-wrapper {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Alert Banner */
.maint-alert-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(239, 68, 68, 0.08);
    border: var(--stroke-w) solid rgba(239, 68, 68, 0.3);
    border-radius: var(--panel-radius, 12px);
    padding: 16px 20px;
    box-shadow: none !important;
}

.maint-alert-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-alert-icon svg {
    width: 20px;
    height: 20px;
}

.maint-alert-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.maint-alert-title {
    color: #ef4444;
    font-size: 14px;
    font-weight: 700;
}

.maint-alert-desc {
    color: var(--text-muted);
    font-size: 13px;
}

/* Page Header */
.maint-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.maint-header-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.maint-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.2s ease;
}

.maint-back-btn svg {
    width: 14px;
    height: 14px;
}

.maint-back-btn:hover {
    color: var(--blue, #3b82f6);
}

.maint-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.maint-main-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    text-transform: capitalize;
}

.maint-pill-duration {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    box-shadow: none !important;
}

.maint-pill-duration svg {
    width: 13px;
    height: 13px;
}

.maint-timer-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    font-family: monospace;
    border: var(--stroke-w) solid transparent;
}

.maint-timer-chip.timer-scheduled {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.25);
}

.maint-timer-chip.timer-running {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.maint-timer-chip.timer-overdue {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.25);
}

.maint-timer-chip.timer-completed {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.maint-timer-chip.timer-cancelled {
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border-color: var(--stroke);
}

.timer-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}

.maint-meta-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

.maint-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-icon {
    width: 15px;
    height: 15px;
}
</style>
