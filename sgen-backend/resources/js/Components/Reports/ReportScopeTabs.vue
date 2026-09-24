<script setup lang="ts">
import { BaseTabs, type TabItem } from '@/Components/UI';
import type { ReportScope } from './types';

defineProps<{
    modelValue: ReportScope;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: ReportScope): void;
}>();

const tabs: TabItem[] = [
    { key: 'soportes', label: 'Soportes' },
    { key: 'inventario', label: 'Inventario' },
    { key: 'mantenimiento', label: 'Mantenimiento' },
    { key: 'rendimiento', label: 'Rendimiento' },
];
</script>

<template>
    <BaseTabs
        :model-value="modelValue"
        :tabs="tabs"
        variant="pills"
        @update:model-value="emit('update:modelValue', $event as ReportScope)"
    />
</template>
