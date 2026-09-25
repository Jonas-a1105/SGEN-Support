<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseDataTable, type DataTableColumn } from '@/Components/UI';
import type { DepartmentItem } from '@/Composables/useDepartmentFilters';

defineProps<{
    departments: DepartmentItem[];
}>();

const emit = defineEmits<{
    (e: 'edit', department: DepartmentItem): void;
    (e: 'delete', department: DepartmentItem): void;
}>();

const columns: DataTableColumn[] = [
    { key: 'code', label: 'CÓDIGO', width: '90px' },
    { key: 'name', label: 'DEPARTAMENTO', sortable: true },
    { key: 'location', label: 'UBICACIÓN FÍSICA' },
    { key: 'manager', label: 'RESPONSABLE / JEFE' },
    { key: 'equipos', label: 'EQUIPOS', width: '90px', align: 'center', sortable: true },
    { key: 'empleados', label: 'EMPLEADOS', width: '95px', align: 'center', sortable: true },
    { key: 'inventoryPercent', label: 'INVENTARIO ASIGNADO', width: '170px' },
    { key: 'actions', label: 'ACCIONES', width: '120px', align: 'right' },
];

const getColorClass = (color: string) => {
    if (color === '#f97316') return 'accent-orange';
    if (color === '#ec4899') return 'accent-pink';
    if (color === '#10b981') return 'accent-emerald';
    if (color === '#06b6d4') return 'accent-cyan';
    if (color === '#8b5cf6') return 'accent-purple';
    if (color === '#f59e0b') return 'accent-amber';
    if (color === '#f43f5e') return 'accent-rose';
    return 'accent-indigo';
};

const getProgressWidthClass = (percent: number) => {
    const pct = Math.min(100, Math.max(0, percent));
    const rounded = Math.round(pct / 5) * 5;
    return `pct-${rounded}`;
};
</script>

<template>
    <section class="departments-table-wrap" aria-label="Tabla de departamentos">
        <BaseDataTable
            :columns="columns"
            :items="departments"
            :row-key="(dept) => dept.numericId"
            :row-class="(dept) => getColorClass(dept.color)"
            empty-title="No se encontraron departamentos"
            empty-subtitle="Prueba con otros términos de búsqueda."
        >
            <template #cell-code="{ item }">
                <span class="badge-code">{{ item.code }}</span>
            </template>

            <template #cell-name="{ item }">
                <div class="dept-title-cell">
                    <div class="dept-icon-mini">
                        <svg viewBox="0 0 24 24">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="2"></line>
                            <line x1="15" y1="22" x2="15" y2="2"></line>
                        </svg>
                    </div>
                    <Link :href="`/departamentos/${item.numericId}`" class="dept-link">
                        <strong class="dept-name-text">{{ item.name }}</strong>
                    </Link>
                </div>
            </template>

            <template #cell-location="{ item }">
                <span class="text-muted-cell">{{ item.location }}</span>
            </template>

            <template #cell-manager="{ item }">
                <strong v-if="item.manager" class="manager-text">{{ item.manager }}</strong>
                <span v-else class="unassigned-text">No asignado</span>
            </template>

            <template #cell-equipos="{ item }">
                <span class="badge-code">{{ item.equipos }}</span>
            </template>

            <template #cell-empleados="{ item }">
                <span class="badge-code">{{ item.empleados }}</span>
            </template>

            <template #cell-inventoryPercent="{ item }">
                <div class="progress-cell">
                    <div class="progress-track">
                        <div class="progress-fill" :class="getProgressWidthClass(item.inventoryPercent)"></div>
                    </div>
                    <span class="progress-num">{{ item.inventoryPercent }}%</span>
                </div>
            </template>

            <template #cell-actions="{ item }">
                <div class="table-actions-group" @click.stop>
                    <Link
                        :href="`/departamentos/${item.numericId}`"
                        class="action-mini-btn view"
                        title="Ver detalle del departamento"
                        @click.stop
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </Link>
                    <button
                        class="action-mini-btn edit"
                        @click.stop="emit('edit', item)"
                        type="button"
                        title="Editar departamento"
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button
                        class="action-mini-btn delete"
                        @click.stop="emit('delete', item)"
                        type="button"
                        title="Eliminar departamento"
                    >
                        <svg viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </BaseDataTable>
    </section>
</template>

<style scoped>
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

.dept-title-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.dept-icon-mini {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: grid;
    place-items: center;
}

.dept-icon-mini svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.dept-link {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s ease;
}

.dept-link:hover .dept-name-text {
    color: var(--brand);
}

.dept-name-text {
    color: var(--text);
    font-size: 13px;
}

.text-muted-cell {
    color: var(--text-muted);
}

.manager-text {
    color: var(--text);
}

.unassigned-text {
    color: var(--text-dim);
    font-size: 12px;
}

.progress-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 130px;
}

.progress-track {
    flex: 1;
    height: 5px;
    background: var(--stroke-subtle);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 4px;
}

.progress-num {
    font-size: 11px;
    font-weight: 700;
    color: var(--text);
}

.table-actions-group {
    display: inline-flex;
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

/* PALETA SEMÁNTICA POR CLASE */
.accent-indigo .dept-icon-mini { background: rgba(79, 70, 229, 0.12); color: #4f46e5; }
.accent-indigo .progress-fill { background: #4f46e5; }

.accent-orange .dept-icon-mini { background: rgba(249, 115, 22, 0.12); color: #f97316; }
.accent-orange .progress-fill { background: #f97316; }

.accent-pink .dept-icon-mini { background: rgba(236, 72, 153, 0.12); color: #ec4899; }
.accent-pink .progress-fill { background: #ec4899; }

.accent-emerald .dept-icon-mini { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.accent-emerald .progress-fill { background: #10b981; }

.accent-cyan .dept-icon-mini { background: rgba(6, 182, 212, 0.12); color: #06b6d4; }
.accent-cyan .progress-fill { background: #06b6d4; }

.accent-purple .dept-icon-mini { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
.accent-purple .progress-fill { background: #8b5cf6; }

.accent-amber .dept-icon-mini { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.accent-amber .progress-fill { background: #f59e0b; }

.accent-rose .dept-icon-mini { background: rgba(244, 63, 94, 0.12); color: #f43f5e; }
.accent-rose .progress-fill { background: #f43f5e; }

/* ANCHOS DE PROGRESO */
.pct-0 { width: 0%; }
.pct-5 { width: 5%; }
.pct-10 { width: 10%; }
.pct-15 { width: 15%; }
.pct-20 { width: 20%; }
.pct-25 { width: 25%; }
.pct-30 { width: 30%; }
.pct-35 { width: 35%; }
.pct-40 { width: 40%; }
.pct-45 { width: 45%; }
.pct-50 { width: 50%; }
.pct-55 { width: 55%; }
.pct-60 { width: 60%; }
.pct-65 { width: 65%; }
.pct-70 { width: 70%; }
.pct-75 { width: 75%; }
.pct-80 { width: 80%; }
.pct-85 { width: 85%; }
.pct-90 { width: 90%; }
.pct-95 { width: 95%; }
.pct-100 { width: 100%; }
</style>
