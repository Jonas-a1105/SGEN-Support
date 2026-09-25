<script setup lang="ts">
import { ref } from 'vue';
import type { TicketPriority } from './types';

const props = defineProps<{
    modelValue: TicketPriority;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: TicketPriority): void;
}>();

const showMatrix = ref(false);
const selectedImpact = ref<'bajo' | 'medio' | 'alto'>('medio');
const selectedUrgency = ref<'baja' | 'media' | 'alta'>('media');

const calculatePriority = () => {
    const impact = selectedImpact.value;
    const urgency = selectedUrgency.value;

    let result: TicketPriority = 'baja';
    if (impact === 'alto' && urgency === 'alta') {
        result = 'critica';
    } else if ((impact === 'alto' && urgency === 'media') || (impact === 'medio' && urgency === 'alta')) {
        result = 'alta';
    } else if (
        (impact === 'alto' && urgency === 'baja') ||
        (impact === 'medio' && urgency === 'media') ||
        (impact === 'bajo' && urgency === 'alta')
    ) {
        result = 'media';
    } else {
        result = 'baja';
    }

    emit('update:modelValue', result);
};

const setImpact = (val: 'bajo' | 'medio' | 'alto') => {
    selectedImpact.value = val;
    calculatePriority();
};

const setUrgency = (val: 'baja' | 'media' | 'alta') => {
    selectedUrgency.value = val;
    calculatePriority();
};
</script>

<template>
    <div>
        <div class="tf-priority-header">
            <label class="tf-field-label">Nivel de Prioridad</label>
            <button
                type="button"
                class="tf-matrix-toggle-btn"
                @click="showMatrix = !showMatrix"
            >
                <svg viewBox="0 0 24 24" class="matrix-icon">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>{{ showMatrix ? 'Ocultar Matriz' : 'Matriz ITIL' }}</span>
            </button>
        </div>

        <!-- ASISTENTE DE MATRIZ IMPACTO X URGENCIA -->
        <div v-if="showMatrix" class="tf-matrix-panel">
            <div class="tf-matrix-row">
                <span class="matrix-axis-label">Impacto (Alcance):</span>
                <div class="matrix-pill-group">
                    <button
                        type="button"
                        class="matrix-pill"
                        :class="{ active: selectedImpact === 'bajo' }"
                        @click="setImpact('bajo')"
                    >
                        Bajo
                    </button>
                    <button
                        type="button"
                        class="matrix-pill"
                        :class="{ active: selectedImpact === 'medio' }"
                        @click="setImpact('medio')"
                    >
                        Medio
                    </button>
                    <button
                        type="button"
                        class="matrix-pill"
                        :class="{ active: selectedImpact === 'alto' }"
                        @click="setImpact('alto')"
                    >
                        Alto
                    </button>
                </div>
            </div>

            <div class="tf-matrix-row">
                <span class="matrix-axis-label">Urgencia (Tiempo):</span>
                <div class="matrix-pill-group">
                    <button
                        type="button"
                        class="matrix-pill"
                        :class="{ active: selectedUrgency === 'baja' }"
                        @click="setUrgency('baja')"
                    >
                        Baja
                    </button>
                    <button
                        type="button"
                        class="matrix-pill"
                        :class="{ active: selectedUrgency === 'media' }"
                        @click="setUrgency('media')"
                    >
                        Media
                    </button>
                    <button
                        type="button"
                        class="matrix-pill"
                        :class="{ active: selectedUrgency === 'alta' }"
                        @click="setUrgency('alta')"
                    >
                        Alta
                    </button>
                </div>
            </div>

            <div class="matrix-calc-result">
                <span>Resultado derivado:</span>
                <strong class="derived-priority-badge" :class="modelValue">
                    {{ modelValue.toUpperCase() }}
                </strong>
            </div>
        </div>

        <div class="tf-priority-list">
            <button
                type="button"
                class="tf-priority-btn low"
                :class="{ active: modelValue === 'baja' }"
                @click="emit('update:modelValue', 'baja')"
            >
                <div class="tf-prio-left">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 14 14" /></svg>
                    <span>Baja</span>
                </div>
                <svg v-if="modelValue === 'baja'" class="tf-prio-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
            </button>

            <button
                type="button"
                class="tf-priority-btn normal"
                :class="{ active: modelValue === 'media' }"
                @click="emit('update:modelValue', 'media')"
            >
                <div class="tf-prio-left">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                    <span>Media</span>
                </div>
                <svg v-if="modelValue === 'media'" class="tf-prio-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
            </button>

            <button
                type="button"
                class="tf-priority-btn high"
                :class="{ active: modelValue === 'alta' }"
                @click="emit('update:modelValue', 'alta')"
            >
                <div class="tf-prio-left">
                    <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                    <span>Alta</span>
                </div>
                <svg v-if="modelValue === 'alta'" class="tf-prio-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
            </button>

            <button
                type="button"
                class="tf-priority-btn critical"
                :class="{ active: modelValue === 'critica' }"
                @click="emit('update:modelValue', 'critica')"
            >
                <div class="tf-prio-left">
                    <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" /></svg>
                    <span>Crítica</span>
                </div>
                <svg v-if="modelValue === 'critica'" class="tf-prio-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.tf-priority-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.tf-field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #8e9199);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin: 0;
}

.tf-matrix-toggle-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    background: transparent;
    border: none;
    color: var(--primary, #6366f1);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
}

.tf-matrix-toggle-btn:hover {
    background: rgba(99, 102, 241, 0.1);
}

.matrix-icon {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tf-matrix-panel {
    background: var(--bg-card, #14161a);
    border: 1px solid var(--stroke, #2d3139);
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tf-matrix-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.matrix-axis-label {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
    font-weight: 500;
}

.matrix-pill-group {
    display: flex;
    gap: 4px;
}

.matrix-pill {
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    background: var(--bg-sub, #1e2024);
    border: 1px solid var(--stroke-subtle, #23252a);
    border-radius: 6px;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    transition: all 0.15s ease;
}

.matrix-pill:hover {
    color: var(--text, #f0f2f5);
}

.matrix-pill.active {
    background: var(--primary, #6366f1);
    border-color: var(--primary, #6366f1);
    color: #ffffff;
}

.matrix-calc-result {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 6px;
    border-top: 1px dashed var(--stroke, #2d3139);
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.derived-priority-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    letter-spacing: 0.05em;
}

.derived-priority-badge.baja {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.derived-priority-badge.media {
    background: rgba(37, 99, 235, 0.15);
    color: #60a5fa;
}

.derived-priority-badge.alta {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.derived-priority-badge.critica {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.tf-priority-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tf-priority-btn {
    height: 38px;
    padding: 0 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted, #8e9199);
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.tf-prio-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.tf-prio-left svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tf-prio-check {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
}

.tf-priority-btn.low.active {
    background: rgba(16, 185, 129, 0.12);
    border-color: #10b981;
    color: #10b981;
}

.tf-priority-btn.normal.active {
    background: rgba(37, 99, 235, 0.12);
    border-color: #2563eb;
    color: #2563eb;
}

.tf-priority-btn.high.active {
    background: rgba(245, 158, 11, 0.12);
    border-color: #f59e0b;
    color: #f59e0b;
}

.tf-priority-btn.critical.active {
    background: rgba(239, 68, 68, 0.12);
    border-color: #ef4444;
    color: #ef4444;
}
</style>
