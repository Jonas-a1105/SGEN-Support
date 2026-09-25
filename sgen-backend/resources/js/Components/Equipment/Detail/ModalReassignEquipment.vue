<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput, BaseCombobox } from '@/Components/UI';
import type { ComboboxOption } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    equipment: EquipmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const departmentOptions = computed<ComboboxOption[]>(() => [
    { value: '', label: 'Sin departamento asignado' },
    ...(props.equipment.departamentos || []).map((d) => ({
        value: d.id,
        label: d.nombre,
    })),
]);

const employeeOptions = computed<ComboboxOption[]>(() => [
    { value: '', label: 'Sin custodio asignado' },
    ...(props.equipment.empleados || []).map((e) => ({
        value: e.id,
        label: e.nombre ?? e.nombre_completo ?? '—',
        sublabel: e.cargo ?? undefined,
    })),
]);

const reassignForm = ref({
    departamento_id: (props.equipment.departmentId ? String(props.equipment.departmentId) : '') as string | number,
    empleado_id: (props.equipment.employeeId ? String(props.equipment.employeeId) : '') as string | number,
    ubicacion_fisica: props.equipment.physicalLocation ?? '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            reassignForm.value = {
                departamento_id: props.equipment.departmentId ? String(props.equipment.departmentId) : '',
                empleado_id: props.equipment.employeeId ? String(props.equipment.employeeId) : '',
                ubicacion_fisica: props.equipment.physicalLocation ?? '',
            };
        }
    }
);

const handleSaveReassignment = () => {
    isSubmitting.value = true;
    const empId = reassignForm.value.empleado_id ? Number(reassignForm.value.empleado_id) : null;
    const deptId = reassignForm.value.departamento_id ? Number(reassignForm.value.departamento_id) : null;
    const targetStatus = empId ? 'en_uso' : (props.equipment.status === 'en_reparacion' || props.equipment.status === 'fuera_de_servicio' ? props.equipment.status : 'disponible');

    router.put(
        `/equipos/${props.equipment.id}`,
        {
            departamento_id: deptId,
            empleado_id: empId,
            ubicacion_fisica: reassignForm.value.ubicacion_fisica || null,
            estado: targetStatus,
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
            <BaseCombobox
                v-model="reassignForm.departamento_id"
                label="Departamento Destino"
                placeholder="Seleccione un departamento..."
                search-placeholder="Buscar departamento..."
                :options="departmentOptions"
                :searchable="true"
                clearable
            />

            <BaseCombobox
                v-model="reassignForm.empleado_id"
                label="Custodio Asignado"
                placeholder="Seleccione un custodio..."
                search-placeholder="Buscar colaborador..."
                :options="employeeOptions"
                :searchable="true"
                clearable
            />

            <BaseInput
                v-model="reassignForm.ubicacion_fisica"
                label="Ubicación Física Específica"
                placeholder="Ej. Oficina 302, Mesa 4"
            />

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

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
