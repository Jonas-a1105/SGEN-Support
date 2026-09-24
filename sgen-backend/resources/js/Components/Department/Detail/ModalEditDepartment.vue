<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput, BaseTextarea } from '@/Components/UI';
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
    jefe_area_id: props.department.jefeAreaId || '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            editForm.value = {
                nombre: props.department.nombre,
                ubicacion: props.department.ubicacion || '',
                descripcion: props.department.descripcion || '',
                jefe_area_id: props.department.jefeAreaId || '',
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
            jefe_area_id: editForm.value.jefe_area_id || null,
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
            <div class="form-group">
                <BaseInput
                    v-model="editForm.nombre"
                    label="Nombre del Departamento *"
                    placeholder="Ej. Tecnología y Sistemas"
                    required
                />
            </div>

            <div class="form-group">
                <BaseInput
                    v-model="editForm.ubicacion"
                    label="Ubicación Física"
                    placeholder="Ej. Torre Corporativa, Piso 4"
                />
            </div>

            <div class="form-group">
                <label class="form-label" for="select-edit-jefe">Jefe / Responsable del Área</label>
                <select
                    id="select-edit-jefe"
                    v-model="editForm.jefe_area_id"
                    class="form-control-select"
                >
                    <option value="">Sin jefe asignado</option>
                    <option
                        v-for="emp in department.empleados"
                        :key="emp.id"
                        :value="emp.id"
                    >
                        {{ emp.fullName }} ({{ emp.cargo }})
                    </option>
                    <option
                        v-for="cand in department.candidatosEmpleados"
                        :key="cand.id"
                        :value="cand.id"
                    >
                        {{ cand.nombre }} ({{ cand.cargo }})
                    </option>
                </select>
            </div>

            <div class="form-group">
                <BaseTextarea
                    v-model="editForm.descripcion"
                    label="Descripción / Alcance"
                    placeholder="Describa las responsabilidades y funciones clave de esta área..."
                    :rows="4"
                />
            </div>

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
