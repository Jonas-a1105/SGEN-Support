<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/formatters';
import type { MaintenanceItem } from './types';

defineProps<{
    items: MaintenanceItem[];
}>();

const emit = defineEmits<{
    (e: 'complete', id: number): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="table-container-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>CÓDIGO ACTIVO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>TIPO</th>
                        <th>FRECUENCIA</th>
                        <th>FECHA PROGRAMADA</th>
                        <th>TÉCNICO</th>
                        <th>ESTADO</th>
                        <th class="text-right">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>
                            <Link :href="`/mantenimientos/${item.id}`" class="maint-table-link">
                                <span class="badge-code">{{ item.equipoCodigo || 'S/C' }}</span>
                            </Link>
                        </td>
                        <td class="desc-cell">
                            <Link :href="`/mantenimientos/${item.id}`" class="maint-table-link">
                                <span class="desc-text" :title="item.descripcion">{{ item.descripcion }}</span>
                            </Link>
                        </td>
                        <td>
                            <span
                                class="badge-type-pill"
                                :class="item.tipoMantenimiento === 'preventivo' ? 'preventivo' : 'correctivo'"
                            >
                                {{ item.tipoMantenimiento }}
                            </span>
                        </td>
                        <td>
                            <span class="freq-tag">{{ item.frecuencia || 'Única' }}</span>
                        </td>
                        <td>{{ formatDate(item.fecha) }}</td>
                        <td>{{ item.tecnicoNombre || 'Sin asignar' }}</td>
                        <td>
                            <span class="status-pill" :class="item.estado">
                                <span class="status-dot"></span>
                                <span>{{ item.estado.replace('_', ' ') }}</span>
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="row-actions">
                                <Link
                                    :href="`/mantenimientos/${item.id}`"
                                    class="btn-row-action view"
                                    title="Ver detalle del mantenimiento"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </Link>
                                <button
                                    v-if="item.estado !== 'completado'"
                                    class="btn-row-action complete"
                                    type="button"
                                    title="Marcar como completado"
                                    @click="emit('complete', item.id)"
                                >
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                                </button>
                                <button
                                    class="btn-row-action delete"
                                    type="button"
                                    title="Eliminar mantenimiento"
                                    @click="emit('delete', item.id)"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.table-container-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    overflow: hidden;
    box-shadow: none !important;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.custom-table th {
    padding: 14px 18px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-dim);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    background: var(--bg-card);
}

.custom-table td {
    padding: 14px 18px;
    font-size: 13px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.custom-table tr:hover td {
    background: var(--stroke-subtle);
}

.badge-code {
    padding: 4px 8px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    border: 1px solid var(--stroke);
    font-size: 11px;
    color: var(--text-muted);
    box-shadow: none !important;
}

.desc-cell {
    max-width: 240px;
}

.desc-text {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.badge-type-pill {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
    text-transform: capitalize;
}

.badge-type-pill.preventivo {
    background: rgba(37, 99, 235, 0.15);
    color: var(--blue, #2563eb);
}

.badge-type-pill.correctivo {
    background: rgba(239, 68, 68, 0.15);
    color: var(--red, #ef4444);
}

.freq-tag {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: capitalize;
}

.status-pill {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-transform: capitalize;
}

.status-pill.pendiente {
    background: rgba(245, 158, 11, 0.15);
    color: var(--orange, #f59e0b);
}

.status-pill.en_proceso {
    background: rgba(37, 99, 235, 0.15);
    color: var(--blue, #2563eb);
}

.status-pill.completado {
    background: rgba(16, 185, 129, 0.15);
    color: var(--green, #10b981);
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.text-right {
    text-align: right;
}

.row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.btn-row-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    display: inline-grid;
    place-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.maint-table-link {
    text-decoration: none;
    color: inherit;
    display: inline-block;
}

.maint-table-link:hover .desc-text {
    color: var(--blue, #3b82f6);
}

.btn-row-action.view:hover {
    border-color: var(--blue, #3b82f6);
    color: var(--blue, #3b82f6);
    background: rgba(59, 130, 246, 0.1);
}

.btn-row-action.complete:hover {
    border-color: var(--green, #10b981);
    color: var(--green, #10b981);
    background: rgba(16, 185, 129, 0.1);
}

.btn-row-action.delete:hover {
    border-color: var(--red, #ef4444);
    color: var(--red, #ef4444);
    background: rgba(239, 68, 68, 0.1);
}

.btn-row-action svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
