<script setup lang="ts">
import type { TicketPriority } from './types';

defineProps<{
    modelValue: TicketPriority;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: TicketPriority): void;
}>();
</script>

<template>
    <div>
        <label class="tf-field-label">Nivel de Prioridad</label>
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
.tf-field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #8e9199);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 8px;
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
