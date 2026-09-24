<script setup lang="ts">
import { computed } from 'vue';
import { BaseTabs, type TabItem } from '@/Components/UI';

export type TabKey = 'tabGeneral' | 'tabAttachments' | 'tabComments' | 'tabLog' | 'tabRating';

interface Props {
    activeTab: TabKey;
    commentsCount?: number;
    attachmentsCount?: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'update:activeTab', val: TabKey): void }>();

const tabs = computed<TabItem[]>(() => [
    { key: 'tabGeneral', label: 'Información General' },
    { key: 'tabAttachments', label: 'Archivos Adjuntos', count: props.attachmentsCount },
    { key: 'tabComments', label: 'Comentarios', count: props.commentsCount },
    { key: 'tabLog', label: 'Bitácora Técnica' },
    { key: 'tabRating', label: 'Calificación' },
]);
</script>

<template>
    <div class="ticket-tabs-wrap">
        <BaseTabs
            :model-value="activeTab"
            :tabs="tabs"
            variant="pills"
            @update:model-value="emit('update:activeTab', $event as TabKey)"
        >
            <template #icon-tabGeneral>
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </template>
            <template #icon-tabAttachments>
                <svg viewBox="0 0 24 24"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
            </template>
            <template #icon-tabComments>
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </template>
            <template #icon-tabLog>
                <svg viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
            </template>
            <template #icon-tabRating>
                <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </template>
        </BaseTabs>
    </div>
</template>

<style scoped>
.ticket-tabs-wrap {
    overflow-x: auto;
}
.ticket-tabs-wrap svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}
</style>
