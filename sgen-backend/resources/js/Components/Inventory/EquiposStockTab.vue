<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Equipment } from '@/Types/inventory';
import { useClientPagination } from '@/Composables/useClientPagination';
import InventoryControlsBar from './Common/InventoryControlsBar.vue';
import { BaseBadge, BaseButton, BaseCard, BaseDataTable, BasePagination, type DataTableColumn } from '@/Components/UI';

const props = withDefaults(
    defineProps<{
        equipos: Equipment[];
        search?: string;
    }>(),
    {
        search: '',
    }
);

const emit = defineEmits<{
    (e: 'update:search', val: string): void;
    (e: 'view-equipment', eq: Equipment): void;
}>();

const viewMode = ref<'table' | 'cards'>('table');
const isDense = ref(false);

const filteredEquipos = computed(() => {
    const term = (props.search || '').trim().toLowerCase();
    if (!term) return props.equipos;
    return props.equipos.filter((eq) =>
        (eq.marca || '').toLowerCase().includes(term) ||
        (eq.modelo || '').toLowerCase().includes(term) ||
        (eq.codigo_inventario || '').toLowerCase().includes(term) ||
        (eq.numero_serie || '').toLowerCase().includes(term) ||
        (eq.tipo_equipo || eq.tipo || '').toLowerCase().includes(term) ||
        (eq.departamento_nombre || '').toLowerCase().includes(term)
    );
});

const columns: DataTableColumn[] = [
    { key: 'codigo_inventario', label: 'CÓDIGO', width: '130px', sortable: true },
    { key: 'tipo_equipo', label: 'TIPO', width: '120px', sortable: true },
    { key: 'modelo', label: 'EQUIPO / MODELO', sortable: true },
    { key: 'numero_serie', label: 'SERIAL', width: '150px' },
    { key: 'estado', label: 'ESTADO', width: '130px', sortable: true },
    { key: 'acciones', label: 'ACCIONES', width: '90px', align: 'center' },
];

const {
    perPage,
    currentPage,
    totalPages,
    paginatedItems: paginatedEquipos,
    setPage,
    setPerPage,
} = useClientPagination(filteredEquipos, 10);
</script>

<template>
    <div>
        <!-- AVISO CONTEXTUAL ITAM: ALMACÉN TI VS FLOTA ACTIVA -->
        <div class="warehouse-info-banner">
            <div class="banner-icon">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
            </div>
            <div class="banner-text">
                <span>
                    <strong>Stock en Almacén TI:</strong> Mostrando únicamente los equipos disponibles en resguardo (sin asignar), listos para entrega o reserva. Para auditar y gestionar toda la flota de activos asignados a usuarios y departamentos, visita el módulo de
                    <Link href="/equipos" class="banner-link">Equipos TI &rarr;</Link>
                </span>
            </div>
        </div>

        <!-- BUSCADOR GLOBAL -->
        <div class="global-search-card search-card-spaced">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
            <input
                :value="search"
                type="text"
                class="global-search-input"
                id="equiposSearchInput"
                placeholder="Buscar por código de equipo, marca, modelo, serial, departamento..."
                autocomplete="off"
                @input="emit('update:search', ($event.target as HTMLInputElement).value)"
            />
        </div>

        <!-- BARRA DE VISTA: SWITCH Y TABLA/CARDS -->
        <InventoryControlsBar
            v-model:is-dense="isDense"
            v-model:view-mode="viewMode"
            :total-count="equipos.length"
            total-label="Total Equipos"
        />

        <!-- TABLA DE EQUIPOS EN STOCK (BaseDataTable) -->
        <div v-if="viewMode === 'table'" class="data-container-card">
            <BaseDataTable
                :columns="columns"
                :items="filteredEquipos"
                :dense="isDense"
                paginate
                :per-page="10"
                item-label="equipos"
                empty-title="No se encontraron equipos"
                empty-subtitle="No hay equipos registrados que coincidan con la búsqueda."
            >
                <template #cell-codigo_inventario="{ item }">
                    <BaseBadge variant="code">{{ item.codigo_inventario || 'N/A' }}</BaseBadge>
                </template>

                <template #cell-tipo_equipo="{ item }">
                    <BaseBadge variant="info">{{ item.tipo_equipo || item.tipo || 'Equipo' }}</BaseBadge>
                </template>

                <template #cell-modelo="{ item }">
                    <strong class="table-item-title">{{ item.marca }} {{ item.modelo }}</strong>
                </template>

                <template #cell-numero_serie="{ item }">
                    <span class="table-serial-code">{{ item.numero_serie || '--' }}</span>
                </template>

                <template #cell-estado="{ item }">
                    <BaseBadge variant="success">● {{ item.estado || 'Disponible' }}</BaseBadge>
                </template>

                <template #cell-acciones="{ item }">
                    <button class="tbl-btn" type="button" title="Ver ficha del equipo" @click="emit('view-equipment', item)">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </template>
            </BaseDataTable>
        </div>

        <!-- CARDS VIEW DE EQUIPOS -->
        <div v-else class="cards-view-wrapper">
            <div class="cards-grid">
                <BaseCard
                    v-for="eq in paginatedEquipos"
                    :key="eq.id"
                    padding="md"
                    class="card-item-box"
                >
                    <div class="card-item-header">
                        <BaseBadge variant="code">{{ eq.codigo_inventario || 'N/A' }}</BaseBadge>
                        <BaseBadge variant="success">● {{ eq.estado || 'Disponible' }}</BaseBadge>
                    </div>
                    <h4 class="card-item-title">{{ eq.marca }} {{ eq.modelo }}</h4>
                    <div class="card-item-meta">
                        <BaseBadge variant="info">{{ eq.tipo_equipo || eq.tipo || 'Hardware' }}</BaseBadge>
                        <span class="table-serial-code">S/N: {{ eq.numero_serie || '--' }}</span>
                    </div>
                    <div class="card-item-footer">
                        <BaseButton variant="primary" size="sm" class="w-full" @click="emit('view-equipment', eq)">
                            Ver Ficha Equipo
                        </BaseButton>
                    </div>
                </BaseCard>
            </div>

            <div v-if="filteredEquipos.length > perPage" class="cards-pagination-wrap">
                <BasePagination
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :total-items="filteredEquipos.length"
                    :per-page="perPage"
                    item-label="equipos"
                    @page-change="setPage"
                    @per-page-change="setPerPage"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-card-spaced {
    margin-bottom: var(--space-4);
}

.table-item-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}

.table-serial-code {
    font-family: var(--font-mono, monospace);
    font-size: 12px;
    color: var(--text-muted);
}

.tbl-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: inline-grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.tbl-btn:hover {
    color: var(--brand);
    border-color: var(--brand);
    background: var(--stroke-subtle);
}

.tbl-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.cards-view-wrapper {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-4);
}

.card-item-box {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    transition: transform var(--transition-fast), border-color var(--transition-fast);
}

.card-item-box:hover {
    transform: translateY(-2px);
    border-color: var(--stroke-hover);
}

.card-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-item-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.card-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.card-item-footer {
    margin-top: auto;
    padding-top: var(--space-2);
}

.cards-pagination-wrap {
    padding-top: var(--space-3);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.w-full {
    width: 100%;
}

.warehouse-info-banner {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    margin-bottom: var(--space-4);
    background: rgba(var(--brand-rgb, 59, 130, 246), 0.08);
    border: 1px solid rgba(var(--brand-rgb, 59, 130, 246), 0.22);
    border-radius: var(--panel-radius, 12px);
    color: var(--text);
    font-size: 13px;
    line-height: 1.5;
}

.banner-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand);
    flex-shrink: 0;
}

.banner-text {
    flex: 1;
}

.banner-link {
    color: var(--brand);
    font-weight: 600;
    text-decoration: underline;
    margin-left: 4px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    transition: opacity var(--transition-fast);
}

.banner-link:hover {
    opacity: 0.8;
}
</style>
