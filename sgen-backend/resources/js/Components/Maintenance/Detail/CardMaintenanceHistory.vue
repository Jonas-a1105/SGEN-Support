<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseCard, BaseEmptyState } from '@/Components/UI';
import { formatCurrency, formatDate } from '@/Utils/formatters';
import { getStatusBadgeVariant } from '@/Utils/badgeVariants';
import type { RelatedMaintenance } from './types';

defineProps<{
    historial: RelatedMaintenance[] | null;
}>();
</script>

<template>
    <BaseCard class="maint-section-card" padding="lg">
        <div class="maint-card-header">
            <div class="maint-card-header-left">
                <div class="maint-card-icon-wrap green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 14 14"></polyline>
                    </svg>
                </div>
                <div>
                    <h2 class="maint-card-title">Historial de Mantenimientos del Equipo</h2>
                    <p class="maint-card-sub">Otros servicios e intervenciones ejecutadas sobre este equipo</p>
                </div>
            </div>
        </div>

        <div v-if="historial && historial.length > 0" class="maint-history-table-wrap">
            <table class="maint-history-table">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>TIPO</th>
                        <th>TÉCNICO</th>
                        <th>ESTADO</th>
                        <th>COSTO</th>
                        <th class="text-right">VER</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="h in historial" :key="h.id">
                        <td class="font-medium">{{ formatDate(h.fecha) }}</td>
                        <td>
                            <span class="maint-type-tag" :class="h.tipoMantenimiento">
                                {{ h.tipoMantenimiento }}
                            </span>
                        </td>
                        <td class="text-muted">{{ h.tecnicoNombre }}</td>
                        <td>
                            <BaseBadge :variant="getStatusBadgeVariant(h.estado)" size="sm">
                                {{ h.estado.replace('_', ' ') }}
                            </BaseBadge>
                        </td>
                        <td class="font-mono">{{ formatCurrency(h.costo) }}</td>
                        <td class="text-right">
                            <Link :href="`/mantenimientos/${h.id}`" class="btn-table-eye" title="Ver mantenimiento">
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
        <BaseEmptyState
            v-else
            title="Sin otros mantenimientos registrados"
            description="Este activo no cuenta con antecedentes previos de mantenimiento en la base de datos."
        />
    </BaseCard>
</template>

<style scoped>
.maint-section-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
}

.maint-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.maint-card-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.maint-card-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-card-icon-wrap.green {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.maint-card-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.maint-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.maint-card-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.maint-history-table-wrap {
    overflow-x: auto;
}

.maint-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.maint-history-table th {
    padding: 10px 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: var(--stroke-w) solid var(--stroke);
    text-align: left;
}

.maint-history-table td {
    padding: 12px 14px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.maint-history-table tr:last-child td {
    border-bottom: none;
}

.font-medium {
    font-weight: 600;
}

.font-mono {
    font-family: monospace;
    font-weight: 600;
}

.text-muted {
    color: var(--text-muted);
}

.maint-type-tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
}

.maint-type-tag.preventivo {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.maint-type-tag.correctivo {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.maint-type-tag.predictivo {
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
}

.btn-table-eye {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: inline-grid;
    place-items: center;
    text-decoration: none;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-table-eye svg {
    width: 14px;
    height: 14px;
}

.btn-table-eye:hover {
    color: var(--blue, #3b82f6);
    border-color: var(--blue, #3b82f6);
}

.text-right {
    text-align: right;
}
</style>
