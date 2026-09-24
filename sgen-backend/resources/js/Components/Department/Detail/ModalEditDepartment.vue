<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput, BaseTextarea, BaseCombobox } from '@/Components/UI';
import type { ComboboxOption } from '@/Components/UI';
import type { DepartmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    department: DepartmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const editForm = ref({
    nombre: props.department.nombre,
    ubicacion: props.department.ubicacion || '',
    descripcion: props.department.descripcion || '',
    jefe_area_id: (props.department.jefeAreaId ? String(props.department.jefeAreaId) : '') as string | number,
});

const isSubmitting = ref(false);

const leaderOptions = computed<ComboboxOption[]>(() => {
    const list: ComboboxOption[] = [
        { value: '', label: 'Sin jefe asignado' },
    ];
    (props.department.empleados || []).forEach((emp) => {
        list.push({
            value: emp.id,
            label: emp.fullName,
            sublabel: emp.cargo,
        });
    });
    (props.department.candidatosEmpleados || []).forEach((cand) => {
        if (!list.some((item) => item.value === cand.id)) {
            list.push({
                value: cand.id,
                label: cand.nombre,
                sublabel: cand.cargo,
            });
        }
    });
    return list;
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            editForm.value = {
                nombre: props.department.nombre,
                ubicacion: props.department.ubicacion || '',
                descripcion: props.department.descripcion || '',
                jefe_area_id: props.department.jefeAreaId ? String(props.department.jefeAreaId) : '',
            };
        }
    }
);

const handleSaveDepartment = () => {
    isSubmitting.value = true;
    router.put(
        `/departamentos/${props.department.id}`,
        {
            nombre: editForm.value.nombre,
            ubicacion: editForm.value.ubicacion,
            descripcion: editForm.value.descripcion,
            jefe_area_id: editForm.value.jefe_area_id ? Number(editForm.value.jefe_area_id) : null,
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
        title="Editar Información del Departamento"
        max-width="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSaveDepartment" class="modal-form-stack">
            <BaseInput
                v-model="editForm.nombre"
                label="Nombre del Departamento *"
                placeholder="Ej. Tecnología y Sistemas"
                required
            />

            <BaseInput
                v-model="editForm.ubicacion"
                label="Ubicación Física"
                placeholder="Ej. Torre Corporativa, Piso 4"
            />

            <BaseCombobox
                v-model="editForm.jefe_area_id"
                label="Jefe / Responsable del Área"
                placeholder="Seleccione un jefe de área..."
                search-placeholder="Buscar colaborador..."
                :options="leaderOptions"
                :searchable="true"
                clearable
            />

            <BaseTextarea
                v-model="editForm.descripcion"
                label="Descripción / Alcance"
                placeholder="Describa las responsabilidades y funciones clave de esta área..."
                :rows="4"
            />

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="isSubmitting">
                    Guardar Cambios
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
