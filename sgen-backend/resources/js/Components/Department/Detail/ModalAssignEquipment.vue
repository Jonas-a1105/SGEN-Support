<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton } from '@/Components/UI';
import type { DepartmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    department: DepartmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const selectedEquipmentId = ref<number | ''>('');
const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            selectedEquipmentId.value = '';
        }
    }
);

const handleAssign = () => {
    if (!selectedEquipmentId.value) return;

    isSubmitting.value = true;
    router.post(
        `/departamentos/${props.department.id}/equipos`,
        { equipo_id: selectedEquipmentId.value },
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                emit('close');
                selectedEquipmentId.value = '';
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Asignar Equipo al Departamento"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleAssign" class="modal-form-stack">
            <div class="form-group">
                <label class="form-label" for="select-assign-eq">Seleccionar Equipo Disponible</label>
                <select
                    id="select-assign-eq"
                    v-model="selectedEquipmentId"
                    class="form-control-select"
                    required
                >
                    <option value="" disabled>Seleccione un equipo...</option>
                    <option
                        v-for="cand in department.candidatosEquipos"
                        :key="cand.id"
                        :value="cand.id"
                    >
                        [{{ cand.codigo }}] {{ cand.nombre }} ({{ cand.estado }})
                    </option>
                </select>
                <p class="form-help">
                    El equipo quedará registrado bajo la custodia física del departamento {{ department.nombre }}.
                </p>
            </div>

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="!selectedEquipmentId || isSubmitting">
                    Asignar Equipo
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.form-control-select {
    width: 100%;
    height: 42px;
    padding: 0 var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
}

.form-control-select:focus {
    border-color: var(--brand);
}

.form-help {
    margin: 0;
    font-size: 12px;
    color: var(--text-dim);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
