<script setup lang="ts">
import { BaseBadge, BaseButton, BaseCard } from '@/Components/UI';
import type { DepartmentDetail } from './types';

const props = defineProps<{
    department: DepartmentDetail;
}>();

const emit = defineEmits<{
    (e: 'assign-manager'): void;
}>();

const getProgressWidthClass = (percent: number): string => {
    const pct = Math.min(100, Math.max(0, percent));
    const rounded = Math.round(pct / 5) * 5;
    return `progress-pct-${rounded}`;
};
</script>

<template>
    <div class="tab-panel-overview">
        <div class="overview-grid">
            <!-- Card: Jefe de Departamento -->
            <BaseCard title="Responsable de Área" variant="glass" padding="md">
                <template #actions>
                    <BaseBadge variant="accent" size="sm">Liderazgo</BaseBadge>
                </template>

                <div v-if="department.jefeAreaNombre" class="manager-card-content">
                    <div class="manager-profile-row">
                        <div class="manager-avatar-circle">
                            {{ department.jefeInitials }}
                        </div>
                        <div class="manager-info-col">
                            <h4 class="manager-name">{{ department.jefeAreaNombre }}</h4>
                            <p class="manager-role">{{ department.jefeCargo || 'Jefe de Departamento' }}</p>
                        </div>
                    </div>

                    <div class="manager-contact-list">
                        <div class="contact-pill-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span>{{ department.jefeEmail || 'Sin correo registrado' }}</span>
                        </div>
                        <div class="contact-pill-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span>{{ department.jefeTelefono || 'Sin teléfono registrado' }}</span>
                        </div>
                    </div>
                </div>

                <div v-else class="unassigned-manager-box">
                    <div class="unassigned-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                    </div>
                    <p class="unassigned-title">Sin Jefe de Área Asignado</p>
                    <p class="unassigned-subtitle">Este departamento no tiene actualmente un líder designado.</p>
                    <BaseButton variant="subtle" size="sm" @click="emit('assign-manager')">
                        Asignar Responsable
                    </BaseButton>
                </div>
            </BaseCard>

            <!-- Card: Detalles Generales -->
            <BaseCard title="Detalles del Departamento" variant="glass" padding="md">
                <div class="dept-details-body">
                    <div class="detail-section">
                        <span class="detail-label">DESCRIPCIÓN OPERATIVA</span>
                        <p class="detail-text">{{ department.descripcion || 'Sin descripción detallada disponible.' }}</p>
                    </div>

                    <div class="detail-metrics-row">
                        <div class="detail-metric-col">
                            <span class="detail-label">UBICACIÓN</span>
                            <span class="detail-val">{{ department.ubicacion || 'Sin asignar' }}</span>
                        </div>
                        <div class="detail-metric-col">
                            <span class="detail-label">NIVEL DE OCUPACIÓN</span>
                            <div class="progress-track-container">
                                <div class="progress-track">
                                    <div class="progress-fill" :class="getProgressWidthClass(department.inventoryPercent)"></div>
                                </div>
                                <span class="progress-pct-text">{{ department.inventoryPercent }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </BaseCard>
        </div>
    </div>
</template>

<style scoped>
.tab-panel-overview {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.overview-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 1024px) {
    .overview-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.manager-card-content {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.manager-profile-row {
    display: flex;
    align-items: center;
    gap: 14px;
}

.manager-avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.15);
    color: var(--blue, #3b82f6);
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: 16px;
    border: var(--stroke-w) solid rgba(37, 99, 235, 0.25);
    flex-shrink: 0;
}

.manager-info-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.manager-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.manager-role {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

.manager-contact-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.contact-pill-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--text);
}

.contact-pill-item svg {
    width: 14px;
    height: 14px;
    color: var(--text-muted);
    flex-shrink: 0;
}

.unassigned-manager-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 24px 16px;
    gap: 8px;
}

.unassigned-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    margin-bottom: 4px;
}

.unassigned-icon svg {
    width: 20px;
    height: 20px;
}

.unassigned-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.unassigned-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0 0 8px 0;
}

.dept-details-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.detail-section {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.detail-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.05em;
}

.detail-text {
    font-size: 13px;
    color: var(--text);
    line-height: 1.6;
    margin: 0;
}

.detail-metrics-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.detail-metric-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.detail-val {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.progress-track-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-track {
    flex: 1;
    height: 7px;
    background: var(--stroke-subtle);
    border-radius: 4px;
    overflow: hidden;
    border: var(--stroke-w) solid var(--stroke);
}

.progress-fill {
    height: 100%;
    background: var(--blue, #3b82f6);
    border-radius: 4px;
}

.progress-pct-text {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    font-family: monospace;
}

.progress-pct-0 { width: 0%; }
.progress-pct-5 { width: 5%; }
.progress-pct-10 { width: 10%; }
.progress-pct-15 { width: 15%; }
.progress-pct-20 { width: 20%; }
.progress-pct-25 { width: 25%; }
.progress-pct-30 { width: 30%; }
.progress-pct-35 { width: 35%; }
.progress-pct-40 { width: 40%; }
.progress-pct-45 { width: 45%; }
.progress-pct-50 { width: 50%; }
.progress-pct-55 { width: 55%; }
.progress-pct-60 { width: 60%; }
.progress-pct-65 { width: 65%; }
.progress-pct-70 { width: 70%; }
.progress-pct-75 { width: 75%; }
.progress-pct-80 { width: 80%; }
.progress-pct-85 { width: 85%; }
.progress-pct-90 { width: 90%; }
.progress-pct-95 { width: 95%; }
.progress-pct-100 { width: 100%; }
</style>
