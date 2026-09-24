<script setup lang="ts">
import BaseCard from '@/Components/UI/BaseCard.vue';
import type { TopCategoryData } from '@/types/DashboardMetrics';

defineProps<{
    category: TopCategoryData;
}>();
</script>

<template>
    <BaseCard class="category-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Por Categoría</h2>
        </div>
        <div class="category-row">
            <div class="category-head">
                <span class="category-label">
                    <svg class="category-icon" viewBox="0 0 24 24">
                        <path d="m4 6 7-1 7 7-6 6-7-7z" />
                        <circle cx="8.5" cy="8.5" r="1.2" fill="currentColor" />
                    </svg>
                    {{ category.nombre }}
                </span>
                <span class="category-percent">{{ category.porcentaje }}%</span>
            </div>
            <div class="category-bar-wrap">
                <svg class="progress-svg" viewBox="0 0 100 8" preserveAspectRatio="none">
                    <rect class="category-track" x="0" y="0" width="100" height="8" rx="4" />
                    <rect
                        class="category-fill"
                        x="0"
                        y="0"
                        :width="Math.min(Math.max(category.porcentaje, 0), 100)"
                        height="8"
                        rx="4"
                    />
                </svg>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.category-card {
    display: flex;
    flex-direction: column;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 18px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.category-row {
    padding: 16px 18px 18px;
}

.category-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.category-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text);
    font-weight: 500;
}

.category-icon {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.category-percent {
    font-weight: 600;
    color: var(--text);
}

.category-bar-wrap {
    height: 8px;
    border-radius: 6px;
    overflow: hidden;
}

.progress-svg {
    width: 100%;
    height: 8px;
    display: block;
}

.category-track {
    fill: var(--stroke-subtle);
}

.category-fill {
    fill: var(--orange);
    transition: width 0.4s ease;
}
</style>
