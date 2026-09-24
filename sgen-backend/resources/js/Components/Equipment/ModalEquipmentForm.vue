<script setup lang="ts">
import { ref, watch } from 'vue';

export interface DepartmentOption {
    id: number;
    nombre: string;
}

export interface EmployeeOption {
    id: number;
    nombre_completo: string;
    cargo?: string | null;
}

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
            formStatus.value = 'En Uso';
            formDeptId.value = '';
            formEmpId.value = '';
            formSerial.value = '';
            formBrand.value = '';
            formModel.value = '';
        }
    },
    { immediate: true }
);

const handleSubmit = () => {
    emit('save', {
        nombre: formName.value,
        id: formId.value,
        codigo_inventario: formId.value,
        tipo: formType.value,
        estado: formStatus.value,
        departamento_id: formDeptId.value !== '' ? formDeptId.value : null,
        empleado_id: formEmpId.value !== '' ? formEmpId.value : null,
        numero_serie: formSerial.value,
        marca: formBrand.value,
        modelo: formModel.value || formName.value,
    });
};
</script>

<template>
    <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-head">
                <h3 class="modal-title">
                    {{ editEquipment ? 'Editar Activo' : 'Registrar Nuevo Activo' }}
                </h3>
                <button class="icon-close" @click="emit('close')" type="button">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-form">
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
                    <div class="form-group">
                        <label class="form-label" for="selectEquipType">Tipo de Activo</label>
                        <select class="form-select" id="selectEquipType" v-model="formType">
                            <option value="Computadora">Computadora</option>
                            <option value="Impresora">Impresora</option>
                            <option value="TELEFONO">Teléfono</option>
                            <option value="CAMARA">Cámara</option>
                            <option value="Servidor">Servidor</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="selectEquipStatus">Estado Inicial</label>
                        <select class="form-select" id="selectEquipStatus" v-model="formStatus">
                            <option value="En Uso">En Uso</option>
                            <option value="Disponible">Disponible</option>
                            <option value="Reparación">En Reparación</option>
                            <option value="Baja">Fuera de Servicio (Baja)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="inputEquipDept">Departamento Asignado</label>
                        <select class="form-select" id="inputEquipDept" v-model="formDeptId">
                            <option value="">-- Sin Departamento --</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.nombre }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="inputEquipEmp">Usuario Asignado</label>
                        <select class="form-select" id="inputEquipEmp" v-model="formEmpId">
                            <option value="">-- Sin Usuario Asignado --</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.nombre_completo }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-cancel" @click="emit('close')" type="button">Cancelar</button>
                    <button class="btn-primary" type="submit">Guardar Activo</button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: grid;
    place-items: center;
    z-index: 100;
    padding: 16px;
}

.modal {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    width: 100%;
    max-width: 520px;
    padding: 24px;
    box-shadow: none !important;
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.icon-close {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 20px;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
}

.icon-close:hover {
    color: var(--text);
}

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 520px) {
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}

.form-input,
.form-select {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    color: var(--text);
    font-size: 13px;
    padding: 8px 12px;
    outline: none;
    box-shadow: none !important;
    transition: border-color 0.2s ease;
}

.form-input:focus,
.form-select:focus {
    border-color: var(--orange);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
    padding-top: 16px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.btn-cancel {
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    font-size: 13px;
    padding: 8px 16px;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.btn-primary {
    background: var(--orange);
    border: var(--stroke-w) solid var(--orange);
    color: #ffffff;
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    box-shadow: none !important;
    transition: opacity 0.2s ease;
}

.btn-primary:hover {
    opacity: 0.9;
}
</style>
