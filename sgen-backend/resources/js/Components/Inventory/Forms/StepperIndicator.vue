<script setup lang="ts">
defineProps<{
    currentStep: number;
}>();

const emit = defineEmits<{
    (e: 'select-step', step: number): void;
}>();

const steps = [
    { num: 1, title: 'Información Básica', subtitle: 'Identificación del activo' },
    { num: 2, title: 'Especificaciones', subtitle: 'Hardware y Software' },
    { num: 3, title: 'Ubicación', subtitle: 'Asignación física' },
    { num: 4, title: 'Adquisición', subtitle: 'Garantía y Costos' },
];
</script>

<template>
    <div class="stepper-col">
        <div
            v-for="s in steps"
            :key="s.num"
            class="step-item-btn"
            :class="{ active: currentStep === s.num }"
            role="button"
            tabindex="0"
            @click="emit('select-step', s.num)"
            @keydown.enter="emit('select-step', s.num)"
        >
            <div class="step-num-circle">{{ s.num }}</div>
            <div>
                <strong class="step-title-text">{{ s.title }}</strong>
                <span class="step-subtitle-text">{{ s.subtitle }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stepper-col {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.step-item-btn {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--radius-md);
    border: var(--stroke-w) solid transparent;
    background: transparent;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    cursor: pointer;
    text-align: left;
    transition: all var(--transition-fast);
}

.step-item-btn.active {
    border-color: var(--blue);
    background: rgba(37, 99, 235, 0.12);
}

.step-num-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--bg-card);
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 700 !important;
}

.step-item-btn.active .step-num-circle {
    background: var(--blue);
    color: #ffffff;
}

.step-title-text {
    font-size: 13px;
    font-weight: 600 !important;
    display: block;
}

.step-subtitle-text {
    font-size: 11px;
    color: var(--text-dim);
    display: block;
}
</style>
