<script setup lang="ts">
import { BaseSearchToolbar, BaseToggleSwitch } from '@/Components/UI';
import type { SupportKpis } from '@/types/support';

defineProps<{
    kpis: SupportKpis;
    activePill: string;
    isCompact: boolean;
    search: string;
}>();

const emit = defineEmits<{
    (e: 'update:activePill', pill: string): void;
    (e: 'update:isCompact', compact: boolean): void;
    (e: 'update:search', query: string): void;
}>();
</script>

<template>
    <BaseSearchToolbar
        :model-value="search"
        placeholder="Buscar ticket, serial o problema..."
        search-position="right"
        @update:model-value="emit('update:search', $event)"
    >
        <template #prepend>
            <BaseToggleSwitch
                :model-value="isCompact"
                title="Alternar vista compacta / completa"
                @update:model-value="emit('update:isCompact', $event)"
            />
        </template>

        <template #filters>
            <div class="filter-pills">
                <button
                    type="button"
                    class="filter-pill"
                    :class="{ active: activePill === 'todos' }"
                    @click="emit('update:activePill', 'todos')"
                >
                    Todos <span class="pill-count">{{ kpis.total_tickets }}</span>
                </button>
                <button
                    type="button"
                    class="filter-pill"
                    :class="{ active: activePill === 'pendiente' }"
                    @click="emit('update:activePill', 'pendiente')"
                >
                    Pendientes <span class="pill-count">{{ kpis.general_queue }}</span>
                </button>
                <button
                    type="button"
                    class="filter-pill"
                    :class="{ active: activePill === 'mis-tickets' }"
                    @click="emit('update:activePill', 'mis-tickets')"
                >
                    Mis Tickets <span class="pill-count">{{ kpis.my_assignments }}</span>
                </button>
                <button
                    type="button"
                    class="filter-pill"
                    :class="{ active: activePill === 'resuelto' }"
                    @click="emit('update:activePill', 'resuelto')"
                >
                    Resueltos <span class="pill-count">{{ kpis.resolved_tickets }}</span>
                </button>
            </div>
        </template>
    </BaseSearchToolbar>
</template>

<style scoped>
.filter-pills {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-pill {
    height: 32px;
    padding: 0 12px;
    border-radius: 20px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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
    font-weight: 600;
}

.pill-count {
    font-size: 11px;
    padding: 1px 6px;
    border-radius: 10px;
    background: rgba(0, 0, 0, 0.2);
}

.filter-pill.active .pill-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}
</style>
