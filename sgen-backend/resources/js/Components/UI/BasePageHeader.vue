<script setup lang="ts">
interface Props {
    title: string;
    subtitle?: string;
    icon?: string;
}

withDefaults(defineProps<Props>(), {
    subtitle: '',
    icon: '',
});
</script>

<template>
    <section class="base-page-header">
        <div class="header-main-col">
            <div class="header-icon-box">
                <slot name="icon">
                    <svg v-if="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="icon" />
                    </svg>
                </slot>
            </div>
            <div class="header-titles">
                <h1 class="header-title">{{ title }}</h1>
                <p v-if="subtitle" class="header-subtitle">{{ subtitle }}</p>
            </div>
        </div>

        <div v-if="$slots.actions || $slots.meta" class="header-actions-col">
            <div v-if="$slots.meta" class="header-meta">
                <slot name="meta" />
            </div>
            <div v-if="$slots.actions" class="header-actions">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>

<style scoped>
.base-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.header-main-col {
    display: flex;
    align-items: center;
    gap: 14px;
}

.header-icon-box {
    width: 44px;
    height: 44px;
    border-radius: var(--panel-radius);
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: var(--brand);
    flex-shrink: 0;
    box-shadow: none !important;
}

.header-icon-box svg {
    width: 22px;
    height: 22px;
}

.header-titles {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.header-title {
    margin: 0;
    font-size: 20px !important;
    font-weight: 700 !important;
    color: var(--text);
    line-height: 1.2;
    letter-spacing: -0.01em;
}

.header-subtitle {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.3;
}

.header-actions-col {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 640px) {
    .base-page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-actions-col {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
