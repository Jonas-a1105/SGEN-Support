<script setup lang="ts">
import { BaseCard } from '@/Components/UI';
import { formatDate, formatDateTime } from '@/Utils/formatters';
import type { MaintenanceDetail } from './types';

defineProps<{
    maintenance: MaintenanceDetail;
}>();
</script>

<template>
    <BaseCard class="maint-sidebar-card" padding="lg">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Planificación &amp; Fechas
        </h3>

        <div class="maint-timeline">
            <div class="timeline-item">
                <div class="timeline-dot blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="timeline-content">
                    <span class="timeline-label">FECHA PROGRAMADA</span>
                    <strong class="timeline-value">{{ formatDateTime(maintenance.fecha) }}</strong>
                </div>
            </div>

            <div class="timeline-connector"></div>

            <div class="timeline-item">
                <div class="timeline-dot green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="timeline-content">
                    <span class="timeline-label">PRÓXIMO VENCIMIENTO</span>
                    <strong class="timeline-value">{{ formatDate(maintenance.proximaFecha) }}</strong>
                    <span class="timeline-subtext">
                        {{ maintenance.proximaFecha ? 'Recurrente según ciclo' : 'Servicio sin recurrencia' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="sidebar-divider"></div>

        <div class="sidebar-field-row">
            <span class="field-label">Frecuencia Planificada:</span>
            <span class="field-badge-value">{{ maintenance.frecuencia.toUpperCase() }}</span>
        </div>
        <div class="sidebar-field-row">
            <span class="field-label">Duración Estimada:</span>
            <span class="field-badge-value">{{ maintenance.duracion || 60 }} minutos</span>
        </div>
    </BaseCard>
</template>

<style scoped>
.maint-sidebar-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.sidebar-card-title svg {
    width: 16px;
    height: 16px;
    color: var(--blue, #3b82f6);
}

.maint-timeline {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.timeline-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.timeline-dot.blue {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.timeline-dot.green {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.timeline-dot svg {
    width: 15px;
    height: 15px;
}

.timeline-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.timeline-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.05em;
}

.timeline-value {
    font-size: 13px;
    color: var(--text);
}

.timeline-subtext {
    font-size: 11px;
    color: var(--text-muted);
}

.timeline-connector {
    width: 2px;
    height: 18px;
    background: var(--stroke);
    margin-left: 14px;
}

.sidebar-divider {
    height: 1px;
    background: var(--stroke-subtle);
    margin: 4px 0;
}

.sidebar-field-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
}

.field-label {
    color: var(--text-muted);
}

.field-badge-value {
    font-weight: 700;
    color: var(--text);
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
}
</style>
