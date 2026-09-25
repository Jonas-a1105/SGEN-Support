<script setup lang="ts">
import type { LogEntry } from '@/Types/support';

defineProps<{
    logEntries: LogEntry[];
}>();
</script>

<template>
    <div v-if="logEntries.length > 0" class="log-entries-history">
        <div v-for="entry in logEntries" :key="entry.id" class="log-history-card">
            <div class="entry-header">
                <span class="entry-author">{{ entry.author }}</span>
                <span class="entry-date">{{ entry.date }}</span>
            </div>
            <div class="entry-content">{{ entry.content }}</div>
        </div>
    </div>
</template>

<style scoped>
.log-entries-history {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.log-history-card {
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    background: var(--bg-card);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: none !important;
}
.entry-header {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-muted);
}
.entry-author {
    font-weight: 700;
    color: var(--text);
}
.entry-content {
    font-size: 13px;
    color: var(--text);
    line-height: 1.45;
    white-space: pre-wrap;
}
</style>
