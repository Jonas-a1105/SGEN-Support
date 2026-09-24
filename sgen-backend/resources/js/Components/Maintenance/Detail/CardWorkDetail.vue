<script setup lang="ts">
import { computed } from 'vue';
import { BaseCard } from '@/Components/UI';

const props = defineProps<{
    descripcion: string;
    checklist: Array<string | { text?: string; checked?: boolean }> | null;
    isCompleted: boolean;
}>();

const normalizedChecklist = computed(() => {
    if (!props.checklist || !Array.isArray(props.checklist)) {
        return [];
    }
    return props.checklist.map((item, idx) => {
        if (typeof item === 'string') {
            return { id: idx, text: item, checked: false };
        }
        return { id: idx, text: item.text || `Actividad ${idx + 1}`, checked: !!item.checked };
    });
});
</script>

<template>
    <BaseCard class="maint-section-card" padding="lg">
        <div class="maint-card-header">
            <div class="maint-card-header-left">
                <div class="maint-card-icon-wrap purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h2 class="maint-card-title">Descripción del Trabajo</h2>
                    <p class="maint-card-sub">Alcance detallado de la intervención técnica</p>
                </div>
            </div>
        </div>

        <div class="maint-desc-body">
            {{ descripcion || 'Sin descripción especificada para este mantenimiento.' }}
        </div>

        <!-- Checklist section if exists -->
        <div v-if="normalizedChecklist.length > 0" class="maint-checklist-section">
            <h3 class="checklist-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Checklist de Actividades Técnicas
            </h3>
            <ul class="checklist-list">
                <li
                    v-for="item in normalizedChecklist"
                    :key="item.id"
                    class="checklist-item"
                    :class="{ completed: item.checked || isCompleted }"
                >
                    <div class="checklist-check-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="checklist-text">{{ item.text }}</span>
                </li>
            </ul>
        </div>
    </BaseCard>
</template>

<style scoped>
.maint-section-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
}

.maint-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.maint-card-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.maint-card-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-card-icon-wrap.purple {
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
}

.maint-card-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.maint-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.maint-card-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.maint-desc-body {
    padding: 16px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.6;
    color: var(--text);
    white-space: pre-line;
}

.maint-checklist-section {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.checklist-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.checklist-title svg {
    width: 16px;
    height: 16px;
    color: var(--blue, #3b82f6);
}

.checklist-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 8px;
    font-size: 13px;
    color: var(--text);
}

.checklist-check-icon {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: transparent;
    flex-shrink: 0;
}

.checklist-check-icon svg {
    width: 14px;
    height: 14px;
}

.checklist-item.completed .checklist-check-icon {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

.checklist-item.completed .checklist-text {
    text-decoration: line-through;
    color: var(--text-muted);
}
</style>
