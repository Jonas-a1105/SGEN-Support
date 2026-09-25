<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';
import type { DepartmentOption, EmployeeOption } from '@/types';

const props = defineProps<{
    show: boolean;
    editEquipment?: Record<string, unknown> | null;
    departments: DepartmentOption[];
    employees: EmployeeOption[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', payload: Record<string, unknown>): void;
}>();

const formName = ref('');
const formId = ref('');
const formType = ref('Computadora');
const formStatus = ref('En Uso');
const formDeptId = ref<number | ''>('');
const formEmpId = ref<number | ''>('');
const formSerial = ref('');
const formBrand = ref('');
const formModel = ref('');

const typeOptions = [
    { value: 'Computadora', label: 'Computadora' },
    { value: 'Impresora', label: 'Impresora' },
    { value: 'TELEFONO', label: 'Teléfono' },
    { value: 'CAMARA', label: 'Cámara' },
    { value: 'Servidor', label: 'Servidor' },
    { value: 'Otro', label: 'Otro' },
];

const statusOptions = [
    { value: 'Disponible', label: 'Disponible (En Almacén TI / Reserva)' },
    { value: 'En Uso', label: 'En Uso (Asignado a Empleado / Dpto)' },
    { value: 'Reparación', label: 'En Reparación / Taller' },
    { value: 'Baja', label: 'Fuera de Servicio (Baja Definitiva)' },
];

const departmentOptions = computed(() => [
    { value: '', label: '-- Sin Departamento (Almacén Central) --' },
    ...props.departments.map((d) => ({
        value: d.id,
        label: d.nombre,
    })),
]);

const employeeOptions = computed(() => [
    { value: '', label: '-- Sin Usuario Asignado (En Almacén) --' },
    ...props.employees.map((e) => ({
        value: e.id,
        label: e.nombre_completo,
        sublabel: e.cargo || undefined,
    })),
]);

watch(
    () => props.editEquipment,
    (equip) => {
        if (equip) {
            formName.value = (equip.name as string) || (equip.modelo as string) || '';
            formId.value = (equip.id as string) || (equip.codigo_inventario as string) || '';
            formType.value = (equip.type as string) || (equip.tipo as string) || 'Computadora';
            formStatus.value = (equip.status as string) || (equip.estado as string) || 'En Uso';
            formDeptId.value = (equip.departmentId as number) || (equip.departamento_id as number) || '';
            formEmpId.value = (equip.employeeId as number) || (equip.empleado_id as number) || '';
            formSerial.value = (equip.serialNumber as string) || (equip.numero_serie as string) || '';
            formBrand.value = (equip.brand as string) || (equip.marca as string) || '';
            formModel.value = (equip.model as string) || (equip.modelo as string) || '';
        } else {
            formName.value = '';
            formId.value = '';
            formType.value = 'Computadora';
            formStatus.value = 'Disponible';
            formDeptId.value = '';
            formEmpId.value = '';
            formSerial.value = '';
            formBrand.value = '';
            formModel.value = '';
        }
    },
    { immediate: true }
);

watch(formEmpId, (newEmpId) => {
    if (!props.editEquipment) {
        if (newEmpId && newEmpId !== '') {
            formStatus.value = 'En Uso';
        } else {
            formStatus.value = 'Disponible';
        }
    }
});

const handleSubmit = () => {
    emit('save', {
        nombre: formName.value,
        id: formId.value,
        codigo_inventario: formId.value,
        tipo: formType.value,
        estado: formStatus.value,
        departamento_id: formDeptId.value !== '' ? Number(formDeptId.value) : null,
        empleado_id: formEmpId.value !== '' ? Number(formEmpId.value) : null,
        numero_serie: formSerial.value,
        marca: formBrand.value,
        modelo: formModel.value || formName.value,
    });
};
</script>

<template>
    <BaseModal
        :is-open="show"
        :title="editEquipment ? 'Editar Activo' : 'Registrar Nuevo Activo'"
        max-width="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSubmit" class="equipment-modal-form">
            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="inputEquipName">Nombre del Equipo</label>
                    <input
                        type="text"
                        class="form-input"
                        id="inputEquipName"
                        v-model="formName"
                        placeholder="Ej: INTEL N5095"
                        required
                    />
                </div>
                <div class="form-group">
                    <label class="form-label" for="inputEquipId">Código ID</label>
                    <input
                        type="text"
                        class="form-input"
                        id="inputEquipId"
                        v-model="formId"
                        placeholder="Ej: 00273"
                    />
                </div>
            </div>

            <div class="form-row-2">
                <BaseCombobox
                    v-model="formType"
                    label="Tipo de Activo"
                    :options="typeOptions"
                    :searchable="false"
                    required
                />
                <BaseCombobox
                    v-model="formStatus"
                    label="Estado Inicial"
                    :options="statusOptions"
                    :searchable="false"
                    required
                />
            </div>

            <div class="form-row-2">
                <BaseCombobox
                    v-model="formDeptId"
                    label="Departamento Asignado"
                    placeholder="-- Sin Departamento --"
                    :options="departmentOptions"
                    clearable
                />
                <BaseCombobox
                    v-model="formEmpId"
                    label="Usuario Asignado"
                    placeholder="-- Sin Usuario Asignado --"
                    :options="employeeOptions"
                    clearable
                />
            </div>

            <div class="form-actions-row">
                <button class="btn-cancel" @click="emit('close')" type="button">Cancelar</button>
                <button class="btn-submit" type="submit">Guardar Activo</button>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.equipment-modal-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
</style>
