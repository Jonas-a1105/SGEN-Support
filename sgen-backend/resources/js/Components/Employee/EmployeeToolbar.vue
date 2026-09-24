<script setup lang="ts">
import { BaseSearchToolbar, BaseToggleSwitch, BaseViewModeToggle } from '@/Components/UI';

export interface DeptFilterOption {
    id: number;
    nombre: string;
}

defineProps<{
    currentDept: string;
    searchQuery: string;
    viewMode: 'grid' | 'table';
    isDense: boolean;
    departments: DeptFilterOption[];
}>();

const emit = defineEmits<{
    (e: 'update:currentDept', dept: string): void;
    (e: 'update:searchQuery', query: string): void;
    (e: 'update:viewMode', mode: 'grid' | 'table'): void;
    (e: 'toggle-dense'): void;
}>();
</script>

<template>
    <BaseSearchToolbar
        :model-value="searchQuery"
        placeholder="Buscar empleado..."
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
            <div class="filter-pills" role="tablist">
                <button
                    class="filter-pill"
                    :class="{ active: currentDept === 'all' }"
                    @click="emit('update:currentDept', 'all')"
                    type="button"
                >
                    Todos
                </button>
                <button
                    v-for="dept in departments.slice(0, 4)"
                    :key="dept.id"
                    class="filter-pill"
                    :class="{ active: currentDept === dept.nombre }"
                    @click="emit('update:currentDept', dept.nombre)"
                    type="button"
                >
                    {{ dept.nombre }}
                </button>
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
}

.filter-pill:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.filter-pill.active {
    background: var(--brand, var(--blue, #2563eb));
    border-color: var(--brand, var(--blue, #2563eb));
    color: #ffffff;
}
</style>
