<script setup lang="ts">
import type { Equipment } from '@/Types/inventory';
import BaseCard from '@/Components/UI/BaseCard.vue';
import { IconCpu } from '@tabler/icons-vue';

defineProps<{
    equipment: Equipment;
}>();
</script>

<template>
    <BaseCard padding="lg" id="subpaneSpecs">
        <div class="panel-header-inline">
            <IconCpu :size="18" stroke-width="1.8" class="icon-specs-glyph" />
            <h3 class="panel-title">Especificaciones Técnicas</h3>
        </div>

        <div class="spec-grid-cards">
            <div class="spec-block">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="2" />
                        <rect x="9" y="9" width="6" height="6" />
                    </svg>
                </div>
                <div>
                    <span class="form-label">Procesador</span>
                    <strong class="spec-field-val">
                        {{ equipment.procesador || 'No especificado' }}
                    </strong>
                </div>
            </div>

            <div class="spec-block">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="6" width="20" height="12" rx="2" />
                        <line x1="6" y1="12" x2="6" y2="12" />
                        <line x1="10" y1="12" x2="10" y2="12" />
                    </svg>
                </div>
                <div>
                    <span class="form-label">Memoria RAM</span>
                    <strong class="spec-field-val">
                        {{ equipment.ram || 'No especificado' }}
                    </strong>
                </div>
            </div>

            <div class="spec-block">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24">
                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                    </svg>
                </div>
                <div>
                    <span class="form-label">Almacenamiento</span>
                    <strong class="spec-field-val">
                        {{ equipment.almacenamiento || 'No especificado' }}
                    </strong>
                </div>
            </div>

            <div class="spec-block">
                <div class="spec-icon-box">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="3" width="8" height="8" />
                        <rect x="13" y="3" width="8" height="8" />
                        <rect x="3" y="13" width="8" height="8" />
                        <rect x="13" y="13" width="8" height="8" />
                    </svg>
                </div>
                <div>
                    <span class="form-label">Sistema Operativo</span>
                    <strong class="spec-field-val">
                        {{ equipment.sistema_operativo || 'No especificado' }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- DETALLES DE RED -->
        <BaseCard variant="subtle" padding="sm" class="network-card-wrap">
            <span class="form-label network-label-spacing">Detalles de Red</span>
            <div class="network-details-row">
                <div>
                    <span class="network-field-label">Dirección IP</span>
                    <strong class="network-field-val">{{ equipment.ip_address || '--' }}</strong>
                </div>
                <div>
                    <span class="network-field-label">MAC Address</span>
                    <strong class="network-field-val">{{ equipment.mac_address || 'N/A' }}</strong>
                </div>
            </div>
        </BaseCard>
    </BaseCard>
</template>

<style scoped>
.panel-header-inline {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}

.icon-specs-glyph {
    color: var(--blue);
    font-size: 16px;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.spec-grid-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-3);
    margin-bottom: var(--space-4);
}

.spec-block {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    border-radius: var(--card-radius);
    padding: var(--space-3) var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.spec-icon-box {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--bg-card);
    display: grid;
    place-items: center;
    color: var(--text-muted);
    flex-shrink: 0;
}

.spec-icon-box svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 2px;
}

.spec-field-val {
    font-size: 13px;
    color: var(--text);
    font-weight: 600;
}

.network-card-wrap {
    margin-top: var(--space-4);
}

.network-label-spacing {
    margin-bottom: var(--space-2);
}

.network-details-row {
    display: flex;
    align-items: center;
    gap: var(--space-6);
}

.network-field-label {
    font-size: 11px;
    color: var(--text-muted);
    display: block;
    margin-bottom: 2px;
}

.network-field-val {
    font-size: 13px;
    color: var(--text);
    font-family: var(--font-mono);
}

@media (max-width: 768px) {
    .spec-grid-cards {
        grid-template-columns: 1fr;
    }
}
</style>
