<script setup lang="ts">
export interface TabItem {
    key: string;
    label: string;
    icon?: string;
    count?: number | null;
}

interface Props {
    tabs: TabItem[];
    modelValue: string;
    variant?: 'pills' | 'underline';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'pills',
});

const emit = defineEmits<{
    (e: 'update:modelValue', key: string): void;
    (e: 'change', key: string): void;
}>();

const selectTab = (key: string) => {
    if (key !== props.modelValue) {
        emit('update:modelValue', key);
        emit('change', key);
    }
};
</script>

<template>
    <nav class="base-tabs-nav" :class="`variant-${variant}`" role="tablist" aria-label="Navegación de pestañas">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            role="tab"
            class="tab-btn"
            :class="{ 'is-active': tab.key === modelValue }"
            :aria-selected="tab.key === modelValue"
            @click="selectTab(tab.key)"
        >
            <!-- Slot for custom tab icon -->
            <slot :name="`icon-${tab.key}`">
                <i v-if="tab.icon" :class="tab.icon" class="tab-icon-i" />
            </slot>

            <span class="tab-label">{{ tab.label }}</span>

            <!-- Counter Pill -->
            <span
                v-if="tab.count !== undefined && tab.count !== null && tab.count > 0"
                class="tab-counter-pill"
            >
                {{ tab.count }}
            </span>
        </button>
    </nav>
</template>

<style scoped>
.base-tabs-nav {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
    padding: var(--space-3, 10px) var(--space-4, 16px);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
    overflow-x: auto;
    scrollbar-width: thin;
    border-radius: var(--radius-lg, 12px) var(--radius-lg, 12px) 0 0;
}

.tab-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2, 8px);
    padding: 8px 16px;
    border-radius: var(--radius-md, 8px);
    border: none;
    background: transparent;
    color: var(--text-dim);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast, 0.15s ease);
    white-space: nowrap;
    user-select: none;
    box-shadow: none !important;
}

.tab-btn:hover {
    color: var(--text);
    background: var(--stroke-subtle);
}

.tab-btn.is-active {
    background: var(--bg-card);
    color: var(--brand);
}

.tab-counter-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: var(--radius-pill, 9999px);
    font-size: 11px;
    font-weight: 700;
    background: var(--stroke);
    color: var(--text);
    transition: all var(--transition-fast, 0.15s ease);
}

.tab-btn.is-active .tab-counter-pill {
    background: rgba(var(--brand-rgb, 59, 130, 246), 0.18);
    color: var(--brand);
}

.tab-icon-i {
    font-size: 14px;
}

/* Variant Underline */
.variant-underline {
    background: transparent;
    padding: 0;
    gap: var(--space-6, 24px);
    border-radius: 0;
}

.variant-underline .tab-btn {
    border-radius: 0;
    padding: 12px 4px;
    border-bottom: 2px solid transparent;
    background: transparent;
}

.variant-underline .tab-btn.is-active {
    border-bottom-color: var(--brand);
    color: var(--brand);
    background: transparent;
}
</style>
