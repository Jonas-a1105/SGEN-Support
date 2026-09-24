<script setup lang="ts">
export interface LeadershipCandidate {
    id: number;
    name: string;
    role: string;
    init: string;
}

defineProps<{
    isOpen: boolean;
    candidates: LeadershipCandidate[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', candidate: LeadershipCandidate): void;
}>();
</script>

<template>
    <div v-if="isOpen" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-head">
                <h3 class="modal-title">Designar Jefe de Departamento</h3>
                <button class="icon-close" @click="emit('close')" type="button">×</button>
            </div>
            <div class="candidates-list">
                <div
                    v-for="cand in candidates"
                    :key="cand.id"
                    class="candidate-row"
                    @click="emit('select', cand)"
                    role="button"
                    tabindex="0"
                >
                    <div class="candidate-avatar">{{ cand.init }}</div>
                    <div class="candidate-copy">
                        <strong class="candidate-name">{{ cand.name }}</strong>
                        <span class="candidate-role">{{ cand.role }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: grid;
    place-items: center;
    z-index: 1000;
    padding: 16px;
}

.modal {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    max-width: 480px;
    width: 100%;
    overflow: hidden;
    box-shadow: none !important;
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.modal-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
    margin: 0;
}

.icon-close {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 20px;
    cursor: pointer;
}

.candidates-list {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 320px;
    overflow-y: auto;
}

.candidate-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.candidate-row:hover {
    border-color: var(--blue);
}

.candidate-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    flex-shrink: 0;
}

.candidate-copy {
    display: flex;
    flex-direction: column;
}

.candidate-name {
    font-size: 13px;
    color: var(--text);
}

.candidate-role {
    font-size: 11px;
    color: var(--text-dim);
}
</style>
