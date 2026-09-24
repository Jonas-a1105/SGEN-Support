<script setup lang="ts">
import { computed } from 'vue';
import { BaseDateRangePicker, BaseCombobox, type ComboboxOption } from '@/Components/UI';
import type { ReportScope, TicketsFilters, Category } from './types';

const props = defineProps<{
    scope: ReportScope;
    filters: TicketsFilters;
    categories: Category[];
}>();

const emit = defineEmits<{
    (e: 'update:filters', filters: TicketsFilters): void;
}>();

const categoryOptions = computed<ComboboxOption[]>(() => [
    { value: '', label: 'Todas las categorías' },
    ...props.categories.map((cat) => ({ value: String(cat.id), label: cat.name })),
]);

const estadoOptions: ComboboxOption[] = [
    { value: '', label: 'Cualquier estado' },
    { value: 'pendiente', label: 'Pendiente', badge: 'Pendiente', badgeVariant: 'warning' },
    { value: 'en_proceso', label: 'En Proceso', badge: 'En Proceso', badgeVariant: 'info' },
    { value: 'resuelto', label: 'Resuelto / Entregado', badge: 'Resuelto', badgeVariant: 'success' },
];

const prioridadOptions: ComboboxOption[] = [
    { value: '', label: 'Todas las prioridades' },
    { value: 'critica', label: 'Crítica', badge: 'Crítica', badgeVariant: 'danger' },
    { value: 'alta', label: 'Alta', badge: 'Alta', badgeVariant: 'warning' },
    { value: 'media', label: 'Media', badge: 'Media', badgeVariant: 'info' },
    { value: 'baja', label: 'Baja', badge: 'Baja', badgeVariant: 'neutral' },
];

const updateFilter = <K extends keyof TicketsFilters>(key: K, val: TicketsFilters[K]) => {
    emit('update:filters', {
        ...props.filters,
        [key]: val,
    });
};
</script>

<template>
    <div class="filter-form-wrapper">
        <!-- FILTRO: SOPORTES -->
        <template v-if="scope === 'soportes'">
            <div class="form-row-2cols">
                <div class="form-group-block">
                    <span class="form-field-kicker">RANGO DE FECHAS</span>
                    <BaseDateRangePicker
                        :start-date="filters.fecha_inicio"
                        :end-date="filters.fecha_fin"
                        :show-presets="false"
                        @update:start-date="updateFilter('fecha_inicio', $event)"
                        @update:end-date="updateFilter('fecha_fin', $event)"
                    />
                </div>

                <div class="form-group-block">
                    <span class="form-field-kicker">CATEGORÍA</span>
                    <BaseCombobox
                        :model-value="filters.categoria_id"
                        :options="categoryOptions"
                        placeholder="Todas las categorías"
                        :clearable="false"
                        @update:model-value="updateFilter('categoria_id', String($event || ''))"
                    />
                </div>
            </div>

            <div class="form-row-2cols">
                <div class="form-group-block">
                    <span class="form-field-kicker">ESTADO</span>
                    <BaseCombobox
                        :model-value="filters.estado"
                        :options="estadoOptions"
                        placeholder="Cualquier estado"
                        :searchable="false"
                        :clearable="false"
                        @update:model-value="updateFilter('estado', String($event || ''))"
                    />
                </div>

                <div class="form-group-block">
                    <span class="form-field-kicker">PRIORIDAD</span>
                    <BaseCombobox
                        :model-value="filters.prioridad"
                        :options="prioridadOptions"
                        placeholder="Todas las prioridades"
                        :searchable="false"
                        :clearable="false"
                        @update:model-value="updateFilter('prioridad', String($event || ''))"
                    />
                </div>
            </div>
        </template>

        <!-- FILTRO: INVENTARIO -->
        <template v-else-if="scope === 'inventario'">
            <div class="scope-info-box">
                <div class="scope-info-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                        <line x1="12" y1="22.08" x2="12" y2="12" />
                    </svg>
                </div>
                <div>
                    <h4 class="scope-info-title">Reporte Integral de Stock</h4>
                    <p class="scope-info-desc">Exporta el inventario de almacén con cantidades, valores unitarios, asignaciones y estado de stock bajo.</p>
                </div>
            </div>
        </template>

        <!-- FILTRO: MANTENIMIENTO -->
        <template v-else-if="scope === 'mantenimiento'">
            <div class="scope-info-box">
                <div class="scope-info-icon maintenance">
                    <svg viewBox="0 0 24 24">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                </div>
                <div>
                    <h4 class="scope-info-title">Reporte de Mantenimiento Técnico</h4>
                    <p class="scope-info-desc">Historial completo de órdenes preventivas y correctivas, costos devengados y próximos vencimientos de equipos.</p>
                </div>
            </div>
        </template>

        <!-- FILTRO: RENDIMIENTO -->
        <template v-else>
            <div class="scope-info-box">
                <div class="scope-info-icon performance">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 20V10" />
                        <path d="M12 20V4" />
                        <path d="M6 20v-6" />
                    </svg>
                </div>
                <div>
                    <h4 class="scope-info-title">Analítica de Rendimiento Técnico</h4>
                    <p class="scope-info-desc">Métricas de tiempo promedio de respuesta, SLA cumplidos y rendimiento por técnico asignado.</p>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.filter-form-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-row-2cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group-block {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-field-kicker {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}



.scope-info-box {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    box-shadow: none !important;
}

.scope-info-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(16, 185, 129, 0.14);
    color: #10b981;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.scope-info-icon.maintenance {
    background: rgba(245, 158, 11, 0.14);
    color: #f59e0b;
}

.scope-info-icon.performance {
    background: rgba(99, 102, 241, 0.14);
    color: #6366f1;
}

.scope-info-icon svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.scope-info-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 4px;
}

.scope-info-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

@media (max-width: 768px) {
    .form-row-2cols {
        grid-template-columns: 1fr;
    }
}
</style>
