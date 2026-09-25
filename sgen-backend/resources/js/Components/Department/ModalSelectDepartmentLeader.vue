<script setup lang="ts">
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseAvatar from '@/Components/UI/BaseAvatar.vue';
import type { LeadershipCandidate } from '@/Types/department';

// Re-exportación para retrocompatibilidad con consumidores que importan el tipo desde aquí.
export type { LeadershipCandidate };

defineProps<{
    isOpen: boolean;
    candidates: LeadershipCandidate[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', candidate: LeadershipCandidate): void;
}>();

const candidateName = (cand: LeadershipCandidate) => `${cand.nombre} ${cand.apellido}`.trim();
const candidateRole = (cand: LeadershipCandidate) => cand.cargo ?? 'Sin cargo asignado';
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Designar Jefe de Departamento"
        max-width="md"
        @close="emit('close')"
    >
        <div class="candidates-list">
            <div
                v-for="cand in candidates"
                :key="cand.id"
                class="candidate-row"
                @click="emit('select', cand)"
                role="button"
                tabindex="0"
            >
                <BaseAvatar :name="candidateName(cand)" size="sm" />
                <div class="candidate-copy">
                    <strong class="candidate-name">{{ candidateName(cand) }}</strong>
                    <span class="candidate-role">{{ candidateRole(cand) }}</span>
                </div>
            </div>
            <div v-if="candidates.length === 0" class="empty-notice">
                No hay candidatos disponibles para asignar.
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.candidates-list {
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

.empty-notice {
    text-align: center;
    padding: 20px;
    font-size: 13px;
    color: var(--text-dim);
}
</style>
