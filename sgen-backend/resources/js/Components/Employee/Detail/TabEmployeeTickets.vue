<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton, BaseEmptyState } from '@/Components/UI';
import type { EmployeeTicket } from './types';

defineProps<{
    tickets: EmployeeTicket[];
    employeeId: number;
}>();

const getTicketStatusVariant = (estado: string): 'success' | 'warning' | 'info' | 'danger' | 'neutral' => {
    switch (estado.toLowerCase()) {
        case 'resuelto':
        case 'cerrado':
            return 'success';
        case 'en_proceso':
            return 'info';
        case 'pendiente':
            return 'warning';
        case 'cancelado':
            return 'danger';
        default:
            return 'neutral';
    }
};
</script>

<template>
    <div class="tab-panel-tickets">
        <div v-if="tickets.length > 0" class="table-card">
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>ASUNTO / INCIDENCIA</th>
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
                                <div class="ticket-cell">
                                    <strong class="ticket-subject">{{ t.titulo }}</strong>
                                    <span class="ticket-snippet">{{ t.descripcion }}</span>
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
            title="Sin tickets asociados"
            description="No se registran solicitudes o incidencias de soporte creadas por este colaborador."
        >
            <Link :href="`/soportes/crear?empleado_id=${employeeId}`">
                <BaseButton variant="primary" size="md">
                    Crear Ticket para este Colaborador
                </BaseButton>
            </Link>
        </BaseEmptyState>
    </div>
</template>

<style scoped>
.tab-panel-tickets {
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

.ticket-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 320px;
}

.ticket-subject {
    font-size: 13px;
    color: var(--text);
}

.ticket-snippet {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.priority-tag {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border: var(--stroke-w) solid var(--stroke);
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
