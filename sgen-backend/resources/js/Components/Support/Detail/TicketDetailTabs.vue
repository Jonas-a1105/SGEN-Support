<script setup lang="ts">
export type TabKey = 'tabGeneral' | 'tabAttachments' | 'tabComments' | 'tabLog' | 'tabRating';

interface Props {
    activeTab: TabKey;
    commentsCount?: number;
    attachmentsCount?: number;
}

defineProps<Props>();
const emit = defineEmits<{ (e: 'update:activeTab', val: TabKey): void }>();
</script>

<template>
    <nav class="ticket-tabs-card" aria-label="Secciones del ticket">
        <button
            :class="['ticket-tab', { active: activeTab === 'tabGeneral' }]"
            type="button"
            @click="emit('update:activeTab', 'tabGeneral')"
        >
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Información General</span>
        </button>

        <button
            :class="['ticket-tab', { active: activeTab === 'tabAttachments' }]"
            type="button"
            @click="emit('update:activeTab', 'tabAttachments')"
        >
            <svg viewBox="0 0 24 24">
                <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            </svg>
            <span>Archivos Adjuntos</span>
            <span v-if="attachmentsCount" class="tab-badge">{{ attachmentsCount }}</span>
        </button>

        <button
            :class="['ticket-tab', { active: activeTab === 'tabComments' }]"
            type="button"
            @click="emit('update:activeTab', 'tabComments')"
        >
            <svg viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Comentarios</span>
            <span v-if="commentsCount" class="tab-badge">{{ commentsCount }}</span>
        </button>

        <button
            :class="['ticket-tab', { active: activeTab === 'tabLog' }]"
            type="button"
            @click="emit('update:activeTab', 'tabLog')"
        >
            <svg viewBox="0 0 24 24">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg>
            <span>Bitácora Técnica</span>
        </button>

        <button
            :class="['ticket-tab', { active: activeTab === 'tabRating' }]"
            type="button"
            @click="emit('update:activeTab', 'tabRating')"
        >
            <svg viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>Calificación</span>
        </button>
    </nav>
</template>

<style scoped>
.ticket-tabs-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
}
.ticket-tabs-card::-webkit-scrollbar {
    display: none;
}
.ticket-tab {
    height: 42px;
    padding: 0 18px;
    border-radius: 12px;
    border: var(--stroke-w) solid transparent;
    background: transparent;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.ticket-tab:hover {
    color: var(--text);
    background: var(--stroke-subtle);
}
.ticket-tab.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
}
.ticket-tab svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}
.tab-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
}
.ticket-tab:not(.active) .tab-badge {
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border: 1px solid var(--stroke);
}
</style>
