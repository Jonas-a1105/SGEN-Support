<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import type { TicketDetail } from '@/types/support';

interface Props {
    ticket: TicketDetail;
}

const props = defineProps<Props>();

const isMenuOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.header-menu-container')) {
        isMenuOpen.value = false;
    }
};

const handleDownloadPdf = () => {
    isMenuOpen.value = false;
    window.print();
};

const handleGoBack = () => {
    isMenuOpen.value = false;
    router.visit('/soportes');
};

onMounted(() => window.addEventListener('click', closeMenu));
onUnmounted(() => window.removeEventListener('click', closeMenu));
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
                <div class="ticket-badge-box" title="Identificador de ticket">
                    #{{ ticket.id }}
                </div>
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
                <div :class="['status-badge', ticket.status_variant]">
                    {{ ticket.status_label }}
                </div>
                <div v-if="ticket.sla_on_time" class="status-badge sla" title="Cumplimiento del Acuerdo de Nivel de Servicio">
                    <svg viewBox="0 0 24 24" class="sla-icon">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>A TIEMPO (SLA)</span>
                </div>

                <div class="header-menu-container">
                    <button
                        class="action-more-btn"
                        type="button"
                        title="Más opciones de ticket"
                        aria-label="Más opciones"
                        @click.stop="toggleMenu"
                    >
                        <svg viewBox="0 0 24 24" class="more-icon">
                            <circle cx="12" cy="12" r="1.5"></circle>
                            <circle cx="12" cy="5" r="1.5"></circle>
                            <circle cx="12" cy="19" r="1.5"></circle>
                        </svg>
                    </button>

                    <!-- MENÚ DESPLEGABLE CONTEXTUAL -->
                    <div v-if="isMenuOpen" class="ticket-menu-dropdown">
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
                    </div>
                </div>
            </div>
        </section>
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
}
.ticket-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
}
.ticket-badge-box {
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text);
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: -0.02em;
    flex-shrink: 0;
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
.status-badge {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 6px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid transparent;
}
.status-badge.resuelto,
.status-badge.resolved {
    background: rgba(22, 163, 74, 0.15);
    color: var(--color-green, #16a34a);
    border-color: rgba(22, 163, 74, 0.3);
}
.status-badge.proceso,
.status-badge.process {
    background: rgba(37, 99, 235, 0.15);
    color: var(--color-blue, #2563eb);
    border-color: rgba(37, 99, 235, 0.3);
}
.status-badge.pendiente,
.status-badge.pending {
    background: rgba(217, 119, 6, 0.15);
    color: var(--color-yellow, #d97706);
    border-color: rgba(217, 119, 6, 0.3);
}
.status-badge.critica,
.status-badge.critical {
    background: rgba(220, 38, 38, 0.15);
    color: var(--color-red, #dc2626);
    border-color: rgba(220, 38, 38, 0.3);
}
.status-badge.sla {
    background: rgba(79, 70, 229, 0.15);
    color: var(--primary);
    border-color: rgba(79, 70, 229, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.sla-icon {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.header-menu-container {
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
.ticket-menu-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 180px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    padding: 6px;
    z-index: 40;
    display: flex;
    flex-direction: column;
    gap: 4px;
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
