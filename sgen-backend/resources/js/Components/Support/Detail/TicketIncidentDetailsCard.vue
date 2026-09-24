<script setup lang="ts">
import type { TicketDetail } from '@/types/support';

defineProps<{
    ticket: TicketDetail;
}>();

const emit = defineEmits<{
    (e: 'open-assign-tech'): void;
    (e: 'open-edit-date'): void;
}>();
</script>

<template>
    <section class="detail-panel">
        <div class="panel-header-row">
            <div class="panel-title-group">
                <div class="panel-icon-pill" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <h2 class="panel-title">DETALLES DEL INCIDENTE</h2>
            </div>
        </div>

        <div class="panel-body-pad">
            <div class="data-two-cols">
                <div class="data-item">
                    <span class="data-kicker">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Fecha de reporte
                    </span>
                    <div class="data-val-big">{{ ticket.report_date }}</div>
                </div>

                <div class="data-item">
                    <span class="data-kicker">
                        <svg viewBox="0 0 24 24">
                            <path d="m4 6 7-1 7 7-6 6-7-7z"></path>
                            <circle cx="8.5" cy="8.5" r="1" fill="currentColor"></circle>
                        </svg>
                        Categoría
                    </span>
                    <div class="data-val-big">{{ ticket.category }}</div>
                </div>
            </div>

            <!-- CAJAS DE REPORTADO POR Y TÉCNICO ASIGNADO -->
            <div class="people-grid">
                <div class="person-box">
                    <div class="person-avatar orange-badge">
                        {{ ticket.requester.substring(0, 2).toUpperCase() }}
                    </div>
                    <div class="person-meta">
                        <span class="person-role-tag">Reportado por</span>
                        <div class="person-name-row">
                            <span class="custom-link">{{ ticket.requester }}</span>
                        </div>
                        <span class="person-dept-sub">{{ ticket.requester_dept }}</span>
                    </div>
                </div>

                <div class="person-box">
                    <div class="person-avatar blue-badge">
                        {{ ticket.tech_initial || 'T' }}
                    </div>
                    <div class="person-meta">
                        <span class="person-role-tag">Técnico asignado</span>
                        <div class="person-name-row">
                            <span class="custom-link">{{ ticket.tech_name }}</span>
                            <button
                                class="data-edit-inline"
                                type="button"
                                title="Reasignar técnico"
                                @click="emit('open-assign-tech')"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </button>
                        </div>
                        <span class="person-dept-sub">{{ ticket.department }}</span>
                    </div>
                </div>
            </div>

            <div class="data-two-cols">
                <div class="data-item">
                    <span class="data-kicker">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 14 14"></polyline>
                        </svg>
                        Estado actual
                    </span>
                    <div class="data-val-big">
                        <span :class="['status-indicator-dot', ticket.status_variant]"></span>
                        <span>{{ ticket.status_label }}</span>
                    </div>
                </div>

                <div class="data-item">
                    <span class="data-kicker">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Fecha de cierre
                    </span>
                    <div class="data-val-big">
                        <span>{{ ticket.close_date }}</span>
                        <button
                            class="data-edit-inline"
                            type="button"
                            title="Modificar fecha de cierre"
                            @click="emit('open-edit-date')"
                        >
                            <svg viewBox="0 0 24 24">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="data-item">
                <span class="data-kicker">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 15 15"></polyline>
                    </svg>
                    Tiempo de atención
                </span>
                <div class="data-val-big">{{ ticket.attention_time }}</div>
            </div>

            <div class="data-item">
                <span class="data-kicker">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Descripción del problema
                </span>
                <div class="desc-container">
                    {{ ticket.description || ticket.title }}
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.detail-panel {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.panel-header-row {
    padding: 18px 22px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.panel-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.panel-title {
    margin: 0;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
    letter-spacing: -0.01em;
}
.panel-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.panel-icon-pill svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.panel-body-pad {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.data-two-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.data-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.data-kicker {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}
.data-kicker svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.data-val-big {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.people-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.person-box {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.person-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}
.orange-badge {
    background: rgba(249, 115, 22, 0.15);
    color: var(--orange);
    border: var(--stroke-w) solid rgba(249, 115, 22, 0.3);
}
.blue-badge {
    background: rgba(59, 130, 246, 0.15);
    color: var(--blue);
    border: var(--stroke-w) solid rgba(59, 130, 246, 0.3);
}
.person-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow: hidden;
}
.person-role-tag {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-dim);
    text-transform: uppercase;
}
.person-name-row {
    display: flex;
    align-items: center;
    gap: 6px;
}
.custom-link {
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.person-dept-sub {
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.data-edit-inline {
    background: transparent;
    border: none;
    color: var(--text-dim);
    cursor: pointer;
    padding: 2px;
    display: grid;
    place-items: center;
}
.data-edit-inline:hover {
    color: var(--primary);
}
.data-edit-inline svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.status-indicator-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #9ca3af;
}
.status-indicator-dot.in-progress {
    background: #3b82f6;
}
.status-indicator-dot.resolved {
    background: #10b981;
}
.status-indicator-dot.pending {
    background: #f59e0b;
}
.status-indicator-dot.critical {
    background: #ef4444;
}
.desc-container {
    padding: 12px 14px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: 10px;
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
    white-space: pre-wrap;
}
</style>
