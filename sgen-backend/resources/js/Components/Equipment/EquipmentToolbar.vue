<script setup lang="ts">
import { BaseSearchToolbar, BaseToggleSwitch, BaseViewModeToggle } from '@/Components/UI';

defineProps<{
    currentPill: string;
    searchQuery: string;
    viewMode: 'grid' | 'table';
    isDense: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:currentPill', pill: string): void;
    (e: 'update:searchQuery', query: string): void;
    (e: 'update:viewMode', mode: 'grid' | 'table'): void;
    (e: 'toggle-dense'): void;
}>();
</script>

<template>
    <BaseSearchToolbar
        :model-value="searchQuery"
        placeholder="Buscar equipo..."
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
                >
                    Disponible
                </button>
                <button
                    class="filter-pill"
                    :class="{ active: currentPill === 'En Uso' }"
                    @click="emit('update:currentPill', 'En Uso')"
                    type="button"
                >
                    En Uso
                </button>
                <button
                    class="filter-pill"
                    :class="{ active: currentPill === 'Reparación' }"
                    @click="emit('update:currentPill', 'Reparación')"
                    type="button"
                >
                    Reparación
                </button>
                <button
                    class="filter-pill"
                    :class="{ active: currentPill === 'Baja' }"
                    @click="emit('update:currentPill', 'Baja')"
                    type="button"
                >
                    Baja
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
    background: var(--brand, var(--orange));
    border-color: var(--brand, var(--orange));
    color: #ffffff;
}
</style>
