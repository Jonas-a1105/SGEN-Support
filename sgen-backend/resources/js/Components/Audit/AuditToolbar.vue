<script setup lang="ts">
import { BaseSearchToolbar, BaseButton } from '@/Components/UI';

const search = defineModel<string>('search', { default: '' });

const emit = defineEmits<{
    (e: 'export'): void;
}>();
</script>

<template>
    <BaseSearchToolbar
        v-model="search"
        placeholder="Buscar por usuario o ID..."
    >
        <template #actions>
            <div class="date-range-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Últimos 30 días</span>
            </div>

            <BaseButton variant="primary" size="sm" @click="emit('export')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-svg">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Exportar Log</span>
            </BaseButton>
        </template>
    </BaseSearchToolbar>
</template>

<style scoped>
.date-range-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    padding: 6px 12px;
    font-size: 12px;
    color: var(--text-muted);
    box-shadow: none !important;
}

.date-range-badge svg {
    width: 14px;
    height: 14px;
}

.btn-svg {
    width: 14px;
    height: 14px;
    margin-right: 6px;
}
</style>
