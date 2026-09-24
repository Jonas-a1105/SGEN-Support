<script setup lang="ts">
import BaseButton from './BaseButton.vue';

interface Props {
    title?: string;
    description?: string;
    icon?: string;
    actionLabel?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'No se encontraron registros',
    description: 'No hay elementos que coincidan con los criterios de búsqueda o filtros aplicados.',
    icon: '',
    actionLabel: '',
});

const emit = defineEmits<{
    (e: 'action'): void;
}>();

const isFontAwesome = (ic?: string): boolean => {
    if (!ic) return false;
    return ic.startsWith('fa-') || ic.includes('fa ') || ic.includes('fa-solid') || ic.includes('fa-regular') || ic.includes('fa-brands');
};
</script>

<template>
    <div class="base-empty-state">
        <div class="empty-icon-box">
            <slot name="icon">
                <i v-if="isFontAwesome(icon)" :class="icon" aria-hidden="true" />
                <svg v-else-if="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="icon" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
            </slot>
        </div>

        <h4 class="empty-title">{{ title }}</h4>
        <p v-if="description" class="empty-desc">{{ description }}</p>

        <div v-if="$slots.actions || actionLabel" class="empty-actions">
            <slot name="actions">
                <BaseButton
                    v-if="actionLabel"
                    variant="secondary"
                    size="sm"
                    @click="emit('action')"
                >
                    {{ actionLabel }}
                </BaseButton>
            </slot>
        </div>
    </div>
</template>

<style scoped>
.base-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 42px 20px;
    background: transparent;
    border-radius: var(--panel-radius);
    width: 100%;
}

.empty-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 9999px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    display: grid;
    place-items: center;
    color: var(--text-dim);
    margin-bottom: 14px;
}

.empty-icon-box svg {
    width: 22px;
    height: 22px;
}

.empty-icon-box i {
    font-size: 20px;
    color: var(--text-dim);
}

.empty-title {
    margin: 0 0 6px 0;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
}

.empty-desc {
    margin: 0 0 16px 0;
    font-size: 13px;
    color: var(--text-muted);
    max-width: 360px;
    line-height: 1.4;
}

.empty-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
</style>
