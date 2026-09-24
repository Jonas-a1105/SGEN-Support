<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton, BaseEmptyState, BaseSearchToolbar } from '@/Components/UI';
import type { DepartmentEquipment } from './types';

const props = defineProps<{
    equipos: DepartmentEquipment[];
}>();

const emit = defineEmits<{
    (e: 'assign'): void;
    (e: 'unlink', equipment: DepartmentEquipment): void;
}>();

const assetSearch = ref('');

const filteredAssets = computed(() => {
    const q = assetSearch.value.trim().toLowerCase();
    if (!q) return props.equipos;
    return props.equipos.filter((eq) =>
        eq.codigo.toLowerCase().includes(q) ||
        eq.nombre.toLowerCase().includes(q) ||
        eq.tipo.toLowerCase().includes(q) ||
        eq.custodio.toLowerCase().includes(q)
    );
});

const getEquipmentBadgeVariant = (estado: string): 'success' | 'info' | 'warning' | 'danger' | 'neutral' => {
    switch (estado.toLowerCase()) {
        case 'disponible':
            return 'success';
        case 'asignado':
            return 'info';
        case 'en_reparacion':
        case 'en reparacion':
            return 'warning';
        case 'de_baja':
        case 'de baja':
            return 'danger';
        default:
            return 'neutral';
    }
};
</script>

<template>
    <div class="tab-panel-assets">
        <BaseSearchToolbar
            v-model="assetSearch"
            placeholder="Buscar equipos por código, modelo, tipo o custodio..."
        >
            <BaseButton variant="primary" size="md" @click="emit('assign')">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Asignar Equipo</span>
            </BaseButton>
        </BaseSearchToolbar>

        <div v-if="filteredAssets.length > 0" class="table-card">
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO ACTIVO</th>
                            <th>DESCRIPCIÓN / MODELO</th>
                            <th>TIPO</th>
                            <th>CUSTODIO</th>
                            <th>ESTADO</th>
                            <th>UBICACIÓN</th>
                            <th class="text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="eq in filteredAssets" :key="eq.id">
                            <td>
                                <BaseBadge variant="code" size="md">{{ eq.codigo }}</BaseBadge>
                            </td>
                            <td>
                                <div class="asset-info-cell">
                                    <strong class="asset-title">{{ eq.nombre }}</strong>
                                    <span v-if="eq.numeroSerie" class="asset-sn">S/N: {{ eq.numeroSerie }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge">{{ eq.tipo }}</span>
                            </td>
                            <td>
                                <span class="custodio-text">{{ eq.custodio }}</span>
                            </td>
                            <td>
                                <BaseBadge :variant="getEquipmentBadgeVariant(eq.estado)" size="sm">
                                    {{ eq.estado }}
                                </BaseBadge>
                            </td>
                            <td class="text-muted-cell">
                                {{ eq.ubicacion || 'Área general' }}
                            </td>
                            <td class="text-right">
                                <div class="table-actions">
                                    <Link :href="`/equipos/${eq.id}`" class="action-btn-circle" title="Ver Ficha de Equipo">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </Link>
                                    <button
                                        type="button"
                                        class="action-btn-circle action-danger"
                                        title="Desvincular equipo"
                                        @click="emit('unlink', eq)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <BaseEmptyState
            v-else
            title="No se encontraron equipos"
            description="No hay equipos vinculados a este departamento que coincidan con la búsqueda."
        >
            <BaseButton variant="primary" size="md" @click="emit('assign')">
                Asignar Primer Equipo
            </BaseButton>
        </BaseEmptyState>
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

.asset-info-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.asset-title {
    font-size: 13px;
    color: var(--text);
}

.asset-sn {
    font-size: 11px;
    color: var(--text-muted);
    font-family: monospace;
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

.custodio-text {
    font-size: 12px;
    color: var(--text);
}

.text-muted-cell {
    color: var(--text-muted);
    font-size: 12px;
}

.text-right {
    text-align: right;
}

.table-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
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
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: none !important;
}

.action-btn-circle:hover {
    color: var(--blue, #3b82f6);
    border-color: var(--blue, #3b82f6);
}

.action-btn-circle.action-danger:hover {
    color: #ef4444;
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
}

.action-btn-circle svg {
    width: 14px;
    height: 14px;
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
}
</style>
