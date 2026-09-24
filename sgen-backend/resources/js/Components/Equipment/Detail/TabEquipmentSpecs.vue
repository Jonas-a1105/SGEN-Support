<script setup lang="ts">
import type { EquipmentDetail } from './types';

defineProps<{
    equipment: EquipmentDetail;
}>();
</script>

<template>
    <div class="tab-panel-specs">
        <div class="hardware-specs-grid">
            <div class="spec-card">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                        <rect x="9" y="9" width="6" height="6"></rect>
                    </svg>
                </div>
                <div class="spec-info">
                    <span class="spec-label">Procesador</span>
                    <strong class="spec-val">{{ equipment.processor || 'No especificado' }}</strong>
                </div>
            </div>

            <div class="spec-card">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 19v-3"></path>
                        <path d="M10 19v-3"></path>
                        <path d="M14 19v-3"></path>
                        <path d="M18 19v-3"></path>
                        <rect x="2" y="5" width="20" height="11" rx="2"></rect>
                    </svg>
                </div>
                <div class="spec-info">
                    <span class="spec-label">Memoria RAM</span>
                    <strong class="spec-val">{{ equipment.ram || 'No especificada' }}</strong>
                </div>
            </div>

            <div class="spec-card">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                </div>
                <div class="spec-info">
                    <span class="spec-label">Almacenamiento</span>
                    <strong class="spec-val">{{ equipment.storage || 'No especificado' }}</strong>
                </div>
            </div>

            <div class="spec-card">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="spec-info">
                    <span class="spec-label">Sistema Operativo</span>
                    <strong class="spec-val">{{ equipment.os || 'No especificado' }}</strong>
                </div>
            </div>
        </div>

        <div class="network-details-box">
            <h4 class="section-subheading">Configuración de Red y Conectividad</h4>
            <div class="network-grid">
                <div class="net-item">
                    <span class="net-label">Dirección IP Asignada</span>
                    <span class="net-val mono">{{ equipment.ipAddress || 'Sin IP configurada' }}</span>
                </div>
                <div v-if="equipment.driver" class="net-item">
                    <span class="net-label">Driver / Controlador</span>
                    <span class="net-val">{{ equipment.driver }}</span>
                </div>
                <div v-if="equipment.toner" class="net-item">
                    <span class="net-label">Tóner / Insumo Compatible</span>
                    <span class="net-val">{{ equipment.toner }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hardware-specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--space-4);
}

.spec-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    background: var(--bg-sub);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
}

.spec-icon-box {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: rgba(var(--brand-rgb), 0.1);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.spec-icon-box svg {
    width: 20px;
    height: 20px;
}

.spec-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.spec-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.spec-val {
    font-size: 14px;
    color: var(--text);
}

.network-details-box {
    margin-top: var(--space-6);
    padding-top: var(--space-5);
    border-top: 1px solid var(--stroke-subtle);
}

.section-subheading {
    margin: 0 0 var(--space-4) 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.network-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
}

.net-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    background: var(--bg-sub);
    padding: var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid var(--stroke-subtle);
}

.net-label {
    font-size: 11px;
    color: var(--text-dim);
    font-weight: 700;
}

.net-val {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.net-val.mono {
    font-family: var(--font-mono);
}
</style>
