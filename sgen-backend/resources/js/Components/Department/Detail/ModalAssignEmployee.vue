<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseCombobox } from '@/Components/UI';
import type { ComboboxOption } from '@/Components/UI';
import type { DepartmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    department: DepartmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const selectedEmployeeId = ref<number | ''>('');
const isSubmitting = ref(false);

const employeeOptions = computed<ComboboxOption[]>(() => {
    return (props.department.candidatosEmpleados || []).map((cand) => ({
        value: cand.id,
        label: cand.nombre,
        sublabel: cand.cargo,
    }));
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            selectedEmployeeId.value = '';
        }
    }
);

const handleAssign = () => {
    if (!selectedEmployeeId.value) return;

    isSubmitting.value = true;
    router.post(
        `/departamentos/${props.department.id}/empleados`,
        { empleado_id: selectedEmployeeId.value },
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                emit('close');
                selectedEmployeeId.value = '';
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Vincular Colaborador al Departamento"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleAssign" class="modal-form-stack">
            <BaseCombobox
                :model-value="selectedEmployeeId"
                label="Seleccionar Colaborador Disponible"
                placeholder="Seleccione un colaborador..."
                search-placeholder="Buscar colaborador por nombre o cargo..."
                :options="employeeOptions"
                :searchable="true"
                required
                :help-text="`El colaborador seleccionado pasará a pertenecer oficialmente al departamento ${department.nombre}.`"
                @update:model-value="(val) => { selectedEmployeeId = val ? Number(val) : ''; }"
            />

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="!selectedEmployeeId || isSubmitting">
                    Confirmar Asignación
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

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
