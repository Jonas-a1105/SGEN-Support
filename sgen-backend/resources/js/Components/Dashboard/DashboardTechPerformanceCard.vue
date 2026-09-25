<script setup lang="ts">
import BaseCard from '@/Components/UI/BaseCard.vue';
import type { TechPerformanceItem } from '@/Types/DashboardMetrics';

defineProps<{
    technicians: TechPerformanceItem[];
}>();
</script>

<template>
    <BaseCard class="tech-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Rendimiento Técnico</h2>
        </div>
        <div class="tech-list">
            <div v-for="tech in technicians" :key="tech.name" class="tech-row">
                <div class="tech-info">
                    <div class="tech-name">{{ tech.name }}</div>
                    <div class="score-bar">
                        <svg class="score-svg" viewBox="0 0 100 4" preserveAspectRatio="none">
                            <rect class="score-track" x="0" y="0" width="100" height="4" rx="2" />
                            <rect
                                class="score-fill"
                                x="0"
                                y="0"
                                :width="Math.min(Math.max(tech.percentage, 0), 100)"
                                height="4"
                                rx="2"
                            />
                        </svg>
                    </div>
                </div>
                <div class="tech-score">{{ tech.score }} pts</div>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.tech-card {
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

.tech-list {
    padding: 8px 18px 16px;
}

.tech-row {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.tech-row:last-child {
    border-bottom: none;
}

.tech-info {
    min-width: 0;
}

.tech-name {
    font-size: 13px;
    color: var(--text);
    font-weight: 500;
}

.score-bar {
    height: 4px;
    margin-top: 6px;
    border-radius: 4px;
    overflow: hidden;
}

.score-svg {
    width: 100%;
    height: 4px;
    display: block;
}

.score-track {
    fill: var(--stroke-subtle);
}

.score-fill {
    fill: var(--orange);
    transition: width 0.4s ease;
}

.tech-score {
    font-size: 13px;
    font-weight: 600;
    color: var(--orange);
}
</style>
