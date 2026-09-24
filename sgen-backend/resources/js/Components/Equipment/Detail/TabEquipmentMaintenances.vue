<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton, BaseEmptyState } from '@/Components/UI';
import { formatCurrency } from '@/Utils/formatters';
import type { EquipmentMaintenance } from './types';

const props = defineProps<{
    maintenances: EquipmentMaintenance[];
    equipmentId?: number;
}>();
</script>

<template>
    <div class="tab-panel-maintenance">
        <div v-if="maintenances.length > 0" class="maint-timeline-wrap">
            <div class="maint-timeline">
                <div
                    v-for="m in maintenances"
                    :key="m.id"
                    class="maint-timeline-item"
                >
                    <div class="maint-timeline-dot"></div>
                    <div class="maint-card">
                        <div class="maint-card-header">
                            <div class="maint-type-pill">
                                <span class="maint-type-title">{{ m.tipo }}</span>
                                <BaseBadge variant="neutral" size="sm">{{ m.estado }}</BaseBadge>
                            </div>
                            <span class="maint-date">{{ m.fecha }}</span>
                        </div>
                        <p class="maint-desc">{{ m.descripcion }}</p>
                        <div class="maint-footer-meta">
                            <span class="maint-tech">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                {{ m.realizadoPor }}
                            </span>
                            <span v-if="m.costo > 0" class="maint-cost">
                                Costo: {{ formatCurrency(m.costo) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <BaseEmptyState
            v-else
            title="Sin mantenimientos registrados"
            description="No hay intervenciones o mantenimientos preventivos/correctivos cargados para este equipo."
        >
            <Link :href="`/mantenimientos/crear?equipo_id=${equipmentId}`">
                <BaseButton variant="primary" size="md">
                    Registrar Mantenimiento
                </BaseButton>
            </Link>
        </BaseEmptyState>
    </div>
</template>

<style scoped>
.maint-timeline {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    position: relative;
    padding-left: var(--space-6);
}

.maint-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: var(--stroke);
}

.maint-timeline-item {
    position: relative;
    display: flex;
    align-items: flex-start;
}

.maint-timeline-dot {
    position: absolute;
    left: -20px;
    top: 14px;
    width: 10px;
    height: 10px;
    border-radius: var(--radius-pill);
    background: var(--brand);
    border: 2px solid var(--bg-card);
}

.maint-card {
    flex: 1;
    background: var(--bg-sub);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.maint-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.maint-type-pill {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.maint-type-title {
    font-weight: 700;
    color: var(--text);
    text-transform: capitalize;
}

.maint-date {
    font-size: 12px;
    color: var(--text-dim);
}

.maint-desc {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
}

.maint-footer-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-dim);
    margin-top: var(--space-2);
}

.maint-tech {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
}

.maint-tech svg {
    width: 14px;
    height: 14px;
}

.maint-cost {
    font-family: var(--font-mono);
    font-weight: 700;
    color: var(--text);
}
</style>
