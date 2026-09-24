<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    equipment: EquipmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const reassignForm = ref({
    departamento_id: props.equipment.departmentId ?? '',
    empleado_id: props.equipment.employeeId ?? '',
    ubicacion_fisica: props.equipment.physicalLocation ?? '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            reassignForm.value = {
                departamento_id: props.equipment.departmentId ?? '',
                empleado_id: props.equipment.employeeId ?? '',
                ubicacion_fisica: props.equipment.physicalLocation ?? '',
            };
        }
    }
);

const handleSaveReassignment = () => {
    isSubmitting.value = true;
    router.put(
        `/equipos/${props.equipment.id}`,
        {
            departamento_id: reassignForm.value.departamento_id || null,
            empleado_id: reassignForm.value.empleado_id || null,
            ubicacion_fisica: reassignForm.value.ubicacion_fisica || null,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                emit('close');
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Reasignar Ubicación y Custodio"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSaveReassignment" class="modal-form-stack">
            <div class="form-group">
                <label class="form-label" for="select-reassign-dept">Departamento Destino</label>
                <select
                    id="select-reassign-dept"
                    v-model="reassignForm.departamento_id"
                    class="form-control-select"
                >
                    <option value="">Sin departamento asignado</option>
                    <option
                        v-for="d in equipment.departamentos"
                        :key="d.id"
                        :value="d.id"
                    >
                        {{ d.nombre }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="select-reassign-emp">Custodio Asignado</label>
                <select
                    id="select-reassign-emp"
                    v-model="reassignForm.empleado_id"
                    class="form-control-select"
                >
                    <option value="">Sin custodio asignado</option>
                    <option
                        v-for="e in equipment.empleados"
                        :key="e.id"
                        :value="e.id"
                    >
                        {{ e.nombre }} ({{ e.cargo }})
                    </option>
                </select>
            </div>

            <div class="form-group">
                <BaseInput
                    v-model="reassignForm.ubicacion_fisica"
                    label="Ubicación Física Específica"
                    placeholder="Ej. Oficina 302, Mesa 4"
                />
            </div>

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="isSubmitting">
                    Guardar Reasignación
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

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
