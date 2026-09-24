<script setup lang="ts">
import { computed } from 'vue';
import { BaseTabs, type TabItem } from '@/Components/UI';

const props = defineProps<{
    activeTab: 'sesiones' | 'bitacora';
    sessionsCount: number;
    actionsCount: number;
}>();

const emit = defineEmits<{
    (e: 'change', tab: 'sesiones' | 'bitacora'): void;
}>();

const tabs = computed<TabItem[]>(() => [
    { key: 'sesiones', label: 'Sesiones de Acceso', count: props.sessionsCount },
    { key: 'bitacora', label: 'Bitácora de Movimientos', count: props.actionsCount },
]);
</script>

<template>
    <div class="audit-tab-bar-wrap">
        <BaseTabs
            :model-value="activeTab"
            :tabs="tabs"
            variant="pills"
            @update:model-value="emit('change', $event as 'sesiones' | 'bitacora')"
        />
    </div>
</template>

<style scoped>
.audit-tab-bar-wrap {
    margin-bottom: 20px;
}
</style>
