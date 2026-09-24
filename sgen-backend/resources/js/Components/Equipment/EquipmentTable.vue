<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { EquipmentItem } from '@/Composables/useEquipmentFilters';

defineProps<{
    equipos: EquipmentItem[];
}>();

const emit = defineEmits<{
    (e: 'view', item: EquipmentItem): void;
    (e: 'edit', item: EquipmentItem): void;
    (e: 'delete', item: EquipmentItem): void;
}>();

const getStatusClass = (status: string) => {
    switch (status) {
        case 'En Uso':
            return 'en-uso';
        case 'Disponible':
            return 'disponible';
        case 'Reparación':
            return 'reparacion';
        default:
            return 'baja';
    }
};
</script>

<template>
    <section class="equipment-table-card" aria-label="Tabla de equipos">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>EQUIPO</th>
                        <th>TIPO</th>
                        <th>UBICACIÓN</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in equipos" :key="item.numericId">
                        <td>
                            <span class="badge-code">{{ item.id }}</span>
                        </td>
                        <td>
                            <div class="item-name-cell">
                                <Link :href="`/equipos/${item.numericId}`" class="equip-link">
                                    <strong class="item-name-text">{{ item.name }}</strong>
                                </Link>
                                <span v-if="item.assignedTo" class="item-assigned-text">{{ item.assignedTo }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-model-outline">{{ item.type }}</span>
                        </td>
                        <td class="text-muted-cell">{{ item.dept }}</td>
                        <td>
                            <span class="status-pill" :class="getStatusClass(item.status)">
                                {{ item.status }}
                            </span>
                        </td>
                        <td>
                            <div class="table-actions-group">
                                <Link
                                    :href="`/equipos/${item.numericId}`"
                                    class="action-mini-btn view"
                                    title="Ver ficha completa del equipo"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </Link>
                                <button
                                    class="action-mini-btn edit"
                                    @click="emit('edit', item)"
                                    type="button"
                                    title="Editar equipo"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button
                                    class="action-mini-btn delete"
                                    @click="emit('delete', item)"
                                    type="button"
                                    title="Eliminar equipo"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
.equipment-table-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
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
    font-size: 13px;
    text-align: left;
}

.custom-table th {
    padding: 12px 16px;
    color: var(--text-dim);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-bottom: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
}

.custom-table td {
    padding: 12px 16px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.custom-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.badge-code {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
}

.item-name-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.equip-link {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s ease;
}

.equip-link:hover .item-name-text {
    color: var(--brand);
}

.item-name-text {
    color: var(--text);
    font-size: 13px;
}

.item-assigned-text {
    font-size: 11px;
    color: var(--text-dim);
}

.badge-model-outline {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    color: var(--text-muted);
}

.text-muted-cell {
    color: var(--text-muted);
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    border: var(--stroke-w) solid transparent;
}

.status-pill.en-uso {
    background: rgba(37, 99, 235, 0.12);
    color: #3b82f6;
    border-color: rgba(37, 99, 235, 0.25);
}

.status-pill.disponible {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.status-pill.reparacion {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.25);
}

.status-pill.baja {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.25);
}

.table-actions-group {
    display: flex;
    gap: 6px;
}

.action-mini-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.action-mini-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.action-mini-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.action-mini-btn.view:hover {
    color: var(--brand);
    border-color: var(--brand);
}

.action-mini-btn.delete:hover {
    color: var(--red);
    border-color: var(--red);
}
</style>
