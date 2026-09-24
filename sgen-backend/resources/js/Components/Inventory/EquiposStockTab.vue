<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Equipment } from '@/Types/inventory';
import { useClientPagination } from '@/Composables/useClientPagination';
import TablePaginationFooter from './Common/TablePaginationFooter.vue';
import InventoryControlsBar from './Common/InventoryControlsBar.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

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

        <!-- TABLA DE EQUIPOS EN STOCK -->
        <div v-if="viewMode === 'table'" class="data-container-card" id="tableWrapEquipos">
            <div class="table-responsive">
                <table class="custom-table" :class="{ dense: isDense }">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>TIPO</th>
                            <th>EQUIPO / MODELO</th>
                            <th>SERIAL</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyEquipos">
                        <tr v-for="eq in paginatedEquipos" :key="eq.id">
                            <td><BaseBadge variant="code">{{ eq.codigo_inventario || 'N/A' }}</BaseBadge></td>
                            <td><BaseBadge variant="info">{{ eq.tipo_equipo || eq.tipo || 'Equipo' }}</BaseBadge></td>
                            <td><strong class="table-item-title">{{ eq.marca }} {{ eq.modelo }}</strong></td>
                            <td><span class="table-serial-code">{{ eq.numero_serie || '--' }}</span></td>
                            <td>
                                <BaseBadge variant="success">
                                    ● {{ eq.estado || 'Disponible' }}
                                </BaseBadge>
                            </td>
                            <td>
                                <button class="tbl-btn" type="button" title="Ver ficha del equipo" @click="emit('view-equipment', eq)">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="paginatedEquipos.length === 0">
                            <td colspan="6" class="table-empty-cell">
                                No se encontraron equipos en stock.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <TablePaginationFooter
                :showing-count="paginatedEquipos.length"
                :total-count="filteredEquipos.length"
                item-label="equipos"
                :current-page="currentPage"
                :total-pages="totalPages"
                :per-page="perPage"
                @change-page="setPage"
                @change-per-page="setPerPage"
            />
        </div>

        <!-- CARDS VIEW DE EQUIPOS -->
        <div v-else class="cards-grid">
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
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--text-dim);
}

.table-empty-cell {
    text-align: center;
    color: var(--text-muted);
    padding: var(--space-6);
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
}

.card-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-item-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
}

.card-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-muted);
}

.card-item-footer {
    margin-top: auto;
    padding-top: var(--space-2);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.w-full {
    width: 100%;
}
</style>
