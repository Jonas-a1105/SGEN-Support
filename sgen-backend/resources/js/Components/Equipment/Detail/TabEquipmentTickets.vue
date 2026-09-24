<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton, BaseEmptyState } from '@/Components/UI';
import type { EquipmentTicket } from './types';

const props = defineProps<{
    tickets: EquipmentTicket[];
    equipmentId: number;
}>();

const getTicketStatusVariant = (st: string): 'warning' | 'info' | 'neutral' | 'success' => {
    switch (st.toLowerCase()) {
        case 'pendiente':
            return 'warning';
        case 'en_proceso':
            return 'info';
        case 'resuelto':
            return 'success';
        default:
            return 'neutral';
    }
};
</script>

<template>
    <div class="tab-panel-support">
        <div v-if="tickets.length > 0" class="tickets-table-card">
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>ASUNTO / REPORTE</th>
                            <th>ESTADO</th>
                            <th>PRIORIDAD</th>
                            <th>FECHA</th>
                            <th class="text-right">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in tickets" :key="t.id">
                            <td>
                                <BaseBadge variant="code" size="md">#{{ t.id }}</BaseBadge>
                            </td>
                            <td>
                                <div class="ticket-info-cell">
                                    <strong class="ticket-title">{{ t.titulo }}</strong>
                                    <span class="ticket-desc">{{ t.descripcion }}</span>
                                </div>
                            </td>
                            <td>
                                <BaseBadge :variant="getTicketStatusVariant(t.estado)" size="sm">
                                    {{ t.estado }}
                                </BaseBadge>
                            </td>
                            <td>
                                <span class="priority-tag">{{ t.prioridad }}</span>
                            </td>
                            <td class="text-muted-cell">{{ t.fecha }}</td>
                            <td class="text-right">
                                <Link :href="`/soportes/${t.id}`" class="action-btn-circle" title="Ver Ticket">
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
            title="Sin tickets reportados"
            description="Este equipo opera con normalidad y no presenta incidencias de soporte registradas."
        >
            <Link :href="`/soportes/crear?equipo_id=${equipmentId}`">
                <BaseButton variant="primary" size="md">
                    Crear Ticket para este Equipo
                </BaseButton>
            </Link>
        </BaseEmptyState>
    </div>
</template>

<style scoped>
.tickets-table-card {
    background: var(--bg-card);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.detail-table th {
    background: var(--bg-sub);
    padding: var(--space-3) var(--space-4);
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--stroke);
}

.detail-table td {
    padding: var(--space-3) var(--space-4);
    border-bottom: 1px solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.detail-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.text-right {
    text-align: right;
}

.text-muted-cell {
    color: var(--text-muted);
}

.ticket-info-cell {
    display: flex;
    flex-direction: column;
}

.ticket-title {
    font-weight: 700;
    color: var(--text);
}

.ticket-desc {
    font-size: 12px;
    color: var(--text-dim);
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.priority-tag {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
}

.action-btn-circle {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-pill);
    background: var(--bg-sub);
    border: 1px solid var(--stroke);
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
}

.action-btn-circle svg {
    width: 14px;
    height: 14px;
}

.action-btn-circle:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
    background: var(--stroke);
}
</style>
