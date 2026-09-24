<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseEmptyState } from '@/Components/UI';
import type { AssignedEquipment } from './types';

defineProps<{
    equipos: AssignedEquipment[];
}>();

const getEquipmentBadgeVariant = (estado: string): 'success' | 'warning' | 'danger' | 'neutral' => {
    switch (estado.toLowerCase()) {
        case 'disponible':
            return 'success';
        case 'en_uso':
        case 'asignado':
            return 'neutral';
        case 'en_mantenimiento':
            return 'warning';
        case 'dañado':
        case 'baja':
            return 'danger';
        default:
            return 'neutral';
    }
};
</script>

<template>
    <div class="tab-panel-assets">
        <div v-if="equipos.length > 0" class="table-card">
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>EQUIPO / MODELO</th>
                            <th>TIPO</th>
                            <th>SERIAL</th>
                            <th>ESTADO</th>
                            <th>UBICACIÓN</th>
                            <th class="text-right">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="eq in equipos" :key="eq.id">
                            <td>
                                <BaseBadge variant="code" size="md">{{ eq.codigo }}</BaseBadge>
                            </td>
                            <td>
                                <strong class="item-name-text">{{ eq.nombre }}</strong>
                            </td>
                            <td>
                                <span class="role-badge">{{ eq.tipo }}</span>
                            </td>
                            <td>
                                <span class="mono-text">{{ eq.numeroSerie || 'N/A' }}</span>
                            </td>
                            <td>
                                <BaseBadge :variant="getEquipmentBadgeVariant(eq.estado)" size="sm">
                                    {{ eq.estado }}
                                </BaseBadge>
                            </td>
                            <td class="text-muted-cell">{{ eq.ubicacion }}</td>
                            <td class="text-right">
                                <Link :href="`/equipos/${eq.id}`" class="action-btn-circle" title="Ver Ficha de Equipo">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <BaseEmptyState
            v-else
            title="Sin equipos en custodia"
            description="Este colaborador no tiene equipos o dispositivos tecnológicos asignados actualmente."
        />
    </div>
</template>

<style scoped>
.tab-panel-assets {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.table-card {
    border-radius: 8px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.detail-table th {
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--stroke-subtle);
    border-bottom: var(--stroke-w) solid var(--stroke);
    letter-spacing: 0.05em;
}

.detail-table td {
    padding: 14px 16px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.detail-table tr:last-child td {
    border-bottom: none;
}

.item-name-text {
    font-size: 13px;
    color: var(--text);
}

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border: var(--stroke-w) solid var(--stroke);
}

.mono-text {
    font-family: monospace;
    font-size: 12px;
    color: var(--text-muted);
}

.text-muted-cell {
    color: var(--text-muted);
    font-size: 12px;
}

.text-right {
    text-align: right;
}

.action-btn-circle {
    display: inline-grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.15s ease;
    box-shadow: none !important;
}

.action-btn-circle:hover {
    color: var(--blue, #3b82f6);
    border-color: var(--blue, #3b82f6);
}

.action-btn-circle svg {
    width: 14px;
    height: 14px;
}
</style>
