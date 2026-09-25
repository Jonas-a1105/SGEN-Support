<script setup lang="ts">
import { BaseSearchToolbar, BaseToggleSwitch, BaseViewModeToggle } from '@/Components/UI';
import type { DepartmentOption } from '@/types';

defineProps<{
    currentPill: string;
    searchQuery: string;
    viewMode: 'grid' | 'table';
    isDense: boolean;
    departments?: DepartmentOption[];
    selectedDepartment?: string | number | 'all';
}>();

const emit = defineEmits<{
    (e: 'update:currentPill', pill: string): void;
    (e: 'update:selectedDepartment', dept: string | number | 'all'): void;
    (e: 'update:searchQuery', query: string): void;
    (e: 'update:viewMode', mode: 'grid' | 'table'): void;
    (e: 'toggle-dense'): void;
}>();
</script>

<template>
    <BaseSearchToolbar
        :model-value="searchQuery"
        placeholder="Buscar por código, serial, modelo, usuario..."
        search-position="right"
        @update:model-value="emit('update:searchQuery', $event)"
    >
        <template #prepend>
            <BaseToggleSwitch
                :model-value="isDense"
                title="Alternar modo de visualización compacto"
                @change="emit('toggle-dense')"
            />
        </template>

        <template #filters>
            <div class="toolbar-filter-row">
                <div class="filter-pills" role="tablist">
                    <button
                        class="filter-pill"
                        :class="{ active: currentPill === 'all' }"
                        @click="emit('update:currentPill', 'all')"
                        type="button"
                    >
                        Todos
                    </button>
                    <button
                        class="filter-pill"
                        :class="{ active: currentPill === 'Disponible' }"
                        @click="emit('update:currentPill', 'Disponible')"
                        type="button"
                        title="Equipos en Almacén TI / Reserva listos para asignar"
                    >
                        📦 En Almacén (Disponibles)
                    </button>
                    <button
                        class="filter-pill"
                        :class="{ active: currentPill === 'En Uso' }"
                        @click="emit('update:currentPill', 'En Uso')"
                        type="button"
                        title="Equipos asignados y operando en departamentos"
                    >
                        👤 En Uso (Asignados)
                    </button>
                    <button
                        class="filter-pill"
                        :class="{ active: currentPill === 'Reparación' }"
                        @click="emit('update:currentPill', 'Reparación')"
                        type="button"
                    >
                        🛠️ Reparación
                    </button>
                    <button
                        class="filter-pill"
                        :class="{ active: currentPill === 'Baja' }"
                        @click="emit('update:currentPill', 'Baja')"
                        type="button"
                    >
                        ⛔ Baja
                    </button>
                </div>

                <div v-if="departments && departments.length > 0" class="dept-filter-wrap">
                    <select
                        :value="selectedDepartment || 'all'"
                        class="dept-select"
                        @change="emit('update:selectedDepartment', ($event.target as HTMLSelectElement).value)"
                        title="Filtrar equipos por departamento asignado"
                    >
                        <option value="all">🏢 Todos los departamentos</option>
                        <option v-for="d in departments" :key="d.id" :value="d.id">
                            {{ d.nombre }}
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <template #actions>
            <BaseViewModeToggle
                :model-value="viewMode"
                @update:model-value="emit('update:viewMode', $event)"
            />
        </template>
    </BaseSearchToolbar>
</template>

<style scoped>
.toolbar-filter-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-pills {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.filter-pill {
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 20px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.filter-pill:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.filter-pill.active {
    background: var(--brand, var(--orange));
    border-color: var(--brand, var(--orange));
    color: #ffffff;
}

.dept-filter-wrap {
    display: inline-flex;
    align-items: center;
}

.dept-select {
    padding: 5px 12px;
    font-size: 12px;
    font-family: inherit;
    border-radius: 20px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    outline: none;
    cursor: pointer;
    transition: border-color 0.2s ease;
    height: 30px;
}

.dept-select:focus {
    border-color: var(--primary);
}
</style>
