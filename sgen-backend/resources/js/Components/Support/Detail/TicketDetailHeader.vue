<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { BaseBadge, BaseDropdown } from '@/Components/UI';
import type { BadgeVariant } from '@/Utils/badgeVariants';
import type { TicketDetail } from '@/Types/support';

interface Props {
    ticket: TicketDetail;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'start-ticket'): void;
    (e: 'open-pause'): void;
    (e: 'resume-ticket'): void;
    (e: 'open-resolve'): void;
    (e: 'reopen-ticket'): void;
}>();

const badgeVariant = computed<BadgeVariant>(() => {
    const raw = (props.ticket.raw_status || props.ticket.status_variant || props.ticket.status || '').toLowerCase();
    if (raw.includes('cerrado') || raw.includes('closed')) return 'neutral';
    if (raw.includes('resuelto') || raw.includes('resolved')) return 'success';
    if (raw.includes('espera') || raw.includes('waiting')) return 'warning';
    if (raw.includes('proceso') || raw.includes('process')) return 'info';
    if (raw.includes('critica') || raw.includes('critical')) return 'danger';
    if (raw.includes('pendiente') || raw.includes('pending')) return 'warning';
    return 'neutral';
});

const rawStatus = computed(() => {
    return (props.ticket.raw_status || props.ticket.status || '').toLowerCase();
});

const isPending = computed(() => {
    return rawStatus.value === 'pendiente' || rawStatus.value === 'pending' || rawStatus.value === 'critical';
});

const isInProcess = computed(() => {
    return rawStatus.value === 'en_proceso' || rawStatus.value === 'process';
});

const isWaiting = computed(() => {
    return rawStatus.value === 'en_espera' || rawStatus.value === 'waiting';
});

const isResolved = computed(() => {
    return rawStatus.value === 'resuelto' || rawStatus.value === 'resolved';
});

const handleDownloadPdf = () => {
    window.open(`/soportes/${props.ticket.id}/pdf`, '_blank');
};

const handleGoBack = () => {
    router.visit('/soportes');
};
</script>

<template>
    <div class="detail-header-wrapper">
        <!-- BARRA SUPERIOR DE RETORNO -->
        <div class="top-nav-bar">
            <Link href="/soportes" class="back-link" title="Volver al listado de soportes">
                <span class="back-arrow">←</span>
                <span>Volver a Soportes</span>
            </Link>
        </div>

        <!-- TARJETA PRINCIPAL DEL TICKET -->
        <section class="ticket-header-card">
            <div class="ticket-header-left">
                <BaseBadge variant="code" size="md">
                    #{{ ticket.id }}
                </BaseBadge>
                <div class="ticket-meta-title-group">
                    <h1 class="ticket-main-title">{{ ticket.title }}</h1>
                    <div class="ticket-tags-subline">
                        <span class="subline-item">
                            <svg viewBox="0 0 24 24" class="subline-icon">
                                <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                            <span>{{ ticket.department }}</span>
                        </span>
                        <span class="subline-dot">•</span>
                        <span class="subline-item">
                            <svg viewBox="0 0 24 24" class="subline-icon">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                            <span>{{ ticket.priority_label }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="ticket-header-right">
                <!-- ACCIONES RÁPIDAS DEL CICLO DE VIDA -->
                <div class="lifecycle-actions">
                    <button
                        v-if="isPending"
                        class="action-btn btn-primary"
                        type="button"
                        @click="emit('start-ticket')"
                        title="Iniciar atención y mover a 'En Proceso'"
                    >
                        <svg viewBox="0 0 24 24" class="btn-icon">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span>Iniciar Atención</span>
                    </button>

                    <button
                        v-if="isInProcess"
                        class="action-btn btn-warning"
                        type="button"
                        @click="emit('open-pause')"
                        title="Pausar ticket y pausar cálculo de SLA"
                    >
                        <svg viewBox="0 0 24 24" class="btn-icon">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                        <span>Pausar</span>
                    </button>

                    <button
                        v-if="isInProcess"
                        class="action-btn btn-success"
                        type="button"
                        @click="emit('open-resolve')"
                        title="Resolver ticket y registrar diagnóstico/solución"
                    >
                        <svg viewBox="0 0 24 24" class="btn-icon">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Resolver</span>
                    </button>

                    <button
                        v-if="isWaiting"
                        class="action-btn btn-primary"
                        type="button"
                        @click="emit('resume-ticket')"
                        title="Reanudar atención técnica"
                    >
                        <svg viewBox="0 0 24 24" class="btn-icon">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span>Reanudar</span>
                    </button>

                    <button
                        v-if="isResolved"
                        class="action-btn btn-outline"
                        type="button"
                        @click="emit('reopen-ticket')"
                        title="Reabrir ticket para continuar soporte"
                    >
                        <svg viewBox="0 0 24 24" class="btn-icon">
                            <path d="M23 4v6h-6"></path>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        <span>Reabrir</span>
                    </button>
                </div>

                <BaseBadge :variant="badgeVariant" size="md">
                    {{ ticket.status_label }}
                </BaseBadge>

                <BaseBadge v-if="ticket.sla_on_time" variant="accent" size="md" dot>
                    A TIEMPO (SLA)
                </BaseBadge>

                <BaseDropdown align="right">
                    <template #trigger>
                        <button
                            class="action-more-btn"
                            type="button"
                            title="Más opciones de ticket"
                            aria-label="Más opciones"
                        >
                            <svg viewBox="0 0 24 24" class="more-icon">
                                <circle cx="12" cy="12" r="1.5"></circle>
                                <circle cx="12" cy="5" r="1.5"></circle>
                                <circle cx="12" cy="19" r="1.5"></circle>
                            </svg>
                        </button>
                    </template>
                    <button class="dropdown-item" type="button" @click="handleDownloadPdf">
                        <svg viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                        <span>Descargar PDF</span>
                    </button>
                    <button class="dropdown-item" type="button" @click="handleGoBack">
                        <svg viewBox="0 0 24 24">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Volver</span>
                    </button>
                </BaseDropdown>
            </div>
        </section>

        <!-- AVISO DE TICKET PAUSADO -->
        <div v-if="isWaiting && ticket.motivo_pausa" class="pause-reason-alert">
            <svg viewBox="0 0 24 24" class="alert-icon">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div class="alert-text">
                <strong>Ticket en Pausa:</strong>
                <span>{{ ticket.motivo_pausa }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.detail-header-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.top-nav-bar {
    display: flex;
    align-items: center;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.18s ease;
}

.back-link:hover {
    color: var(--text);
}

.back-arrow {
    font-size: 16px;
}

.ticket-header-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: none !important;
}

.ticket-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
}

.ticket-meta-title-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.ticket-main-title {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.015em;
    line-height: 1.25;
    word-break: break-word;
}

.ticket-tags-subline {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-muted);
}

.subline-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.subline-icon {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.subline-dot {
    color: var(--text-dim);
}

.ticket-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.action-more-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.18s ease;
    box-shadow: none !important;
}

.action-more-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.more-icon {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: currentColor;
    stroke-width: 1.5;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    text-align: left;
    transition: background 0.16s ease;
}

.dropdown-item svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    color: var(--text-muted);
}

.dropdown-item:hover {
    background: var(--stroke-subtle);
}

.lifecycle-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
    border: 1px solid transparent;
    box-shadow: none;
    line-height: 1;
}

.btn-icon {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.btn-primary {
    background: var(--brand-primary, #6366f1);
    color: #ffffff;
    border-color: var(--brand-primary, #6366f1);
}

.btn-primary .btn-icon {
    fill: currentColor;
}

.btn-primary:hover {
    filter: brightness(1.1);
}

.btn-warning {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.3);
}

.btn-warning .btn-icon {
    fill: currentColor;
}

.btn-warning:hover {
    background: rgba(245, 158, 11, 0.2);
    border-color: rgba(245, 158, 11, 0.5);
}

.btn-success {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.3);
}

.btn-success:hover {
    background: rgba(16, 185, 129, 0.2);
    border-color: rgba(16, 185, 129, 0.5);
}

.btn-outline {
    background: var(--stroke-subtle);
    color: var(--text);
    border-color: var(--stroke);
}

.btn-outline:hover {
    background: var(--stroke-hover);
    border-color: var(--stroke-hover);
}

.pause-reason-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: var(--panel-radius, 12px);
    padding: 12px 18px;
    color: var(--text);
    font-size: 13px;
}

.alert-icon {
    width: 18px;
    height: 18px;
    stroke: #d97706;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.alert-text {
    display: flex;
    align-items: center;
    gap: 6px;
    line-height: 1.4;
}

.alert-text strong {
    color: #d97706;
    font-weight: 700;
}

@media (max-width: 768px) {
    .ticket-header-card {
        padding: 14px 16px;
        flex-direction: column;
        align-items: flex-start;
    }
    .ticket-header-right {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}
</style>
