<script setup lang="ts">
import { BaseSearchToolbar, BaseToggleSwitch, BaseViewModeToggle } from '@/Components/UI';

defineProps<{
    searchQuery: string;
    viewMode: 'grid' | 'table';
    isDense: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:searchQuery', query: string): void;
    (e: 'update:viewMode', mode: 'grid' | 'table'): void;
    (e: 'toggle-dense'): void;
}>();
</script>

<template>
    <BaseSearchToolbar
        :model-value="searchQuery"
        placeholder="Buscar por nombre..."
        @update:model-value="emit('update:searchQuery', $event)"
    >
        <template #prepend>
            <BaseToggleSwitch
                :model-value="isDense"
                title="Alternar modo de visualización compacto"
                @change="emit('toggle-dense')"
            />
        </template>

        <template #actions>
            <BaseViewModeToggle
                :model-value="viewMode"
                @update:model-value="emit('update:viewMode', $event)"
            />
        </template>
    </BaseSearchToolbar>
</template>
