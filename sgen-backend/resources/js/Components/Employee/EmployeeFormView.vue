<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { EmployeeItem } from '@/Composables/useEmployeeFilters';
import EmployeeCredentialPreview from './EmployeeCredentialPreview.vue';
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';

export interface DepartmentSelectOption {
    id: number;
    nombre: string;
}

export interface UserSelectOption {
    id: number;
    username: string;
    rol: string;
}

const props = defineProps<{
    editEmployee?: EmployeeItem | null;
    departments: DepartmentSelectOption[];
    users: UserSelectOption[];
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'save', payload: {
        nombre: string;
        apellido: string;
        cedula: string;
        cargo: string;
        email: string;
        departamento_id: number | null;
        usuario_id: number | null;
    }): void;
}>();

const firstName = ref('');
const lastName = ref('');
const idDoc = ref('');
const position = ref('');
const email = ref('');
const departmentId = ref<number | ''>('');
const userId = ref<number | ''>('');

watch(
    () => props.editEmployee,
    (emp) => {
        if (emp) {
            firstName.value = emp.firstName || '';
            lastName.value = emp.lastName || '';
            idDoc.value = emp.idDoc || '';
            position.value = emp.position || '';
            email.value = emp.email || '';
            departmentId.value = emp.departmentId || '';
            userId.value = '';
        } else {
            firstName.value = '';
            lastName.value = '';
            idDoc.value = '';
            position.value = '';
            email.value = '';
            departmentId.value = '';
            userId.value = '';
        }
    },
    { immediate: true }
);

const departmentOptions = computed(() =>
    props.departments.map((d) => ({
        value: d.id,
        label: d.nombre,
    }))
);

const userOptions = computed(() =>
    props.users.map((u) => ({
        value: u.id,
        label: u.username,
        sublabel: u.rol,
    }))
);

// Preview Computations
const previewFullName = computed(() => {
    const full = `${firstName.value} ${lastName.value}`.trim();
    return full !== '' ? full : 'Nombre Apellido';
});

const previewEmail = computed(() => {
    return email.value.trim() !== '' ? email.value : 'correo@empresa.com';
});

const previewPosition = computed(() => {
    return position.value.trim() !== '' ? position.value.toUpperCase() : 'SIN CARGO';
});

const previewDeptName = computed(() => {
    if (!departmentId.value) return '---';
    const found = props.departments.find((d) => d.id === departmentId.value);
    return found ? found.nombre : '---';
});

const previewInitials = computed(() => {
    const f = firstName.value.trim().charAt(0);
    const l = lastName.value.trim().charAt(0);
    const inits = `${f}${l}`.toUpperCase();
    return inits !== '' ? inits : 'NA';
});

const isVerifiedUser = computed(() => {
    return userId.value !== '' || (props.editEmployee && !!props.editEmployee.userAccount);
});

const handleSubmit = () => {
    emit('save', {
        nombre: firstName.value,
        apellido: lastName.value,
        cedula: idDoc.value,
        cargo: position.value,
        email: email.value,
        departamento_id: departmentId.value !== '' ? Number(departmentId.value) : null,
        usuario_id: userId.value !== '' ? Number(userId.value) : null,
    });
};
</script>

<template>
    <section class="employee-form-view" aria-label="Formulario de empleado">
        <!-- CABECERA DEL FORMULARIO -->
        <div class="module-header">
            <div class="module-title-wrap">
                <div class="module-icon-box" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </div>
                <div>
                    <h1 class="module-title">{{ editEmployee ? 'Editar Colaborador' : 'Registrar Colaborador' }}</h1>
                    <p class="module-subtitle">
                        {{ editEmployee ? `Actualiza la ficha institucional de ${editEmployee.name}.` : 'Ingresa la información personal y laboral del nuevo colaborador.' }}
                    </p>
                </div>
            </div>

            <button class="btn-back" @click="emit('back')" type="button">
                <span>←</span>
                <span>Volver al Directorio</span>
            </button>
        </div>

        <!-- LAYOUT 2 COLUMNAS: FORMULARIO Y VISTA PREVIA -->
        <form @submit.prevent="handleSubmit" class="form-grid-layout">
            <!-- COLUMNA IZQUIERDA: CAMPOS -->
            <div class="form-card-panel">
                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="inputFirstName">Nombre</label>
                        <input
                            type="text"
                            class="form-input"
                            id="inputFirstName"
                            v-model="firstName"
                            placeholder="Ej. Ana"
                            required
                            autocomplete="off"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="inputLastName">Apellido</label>
                        <input
                            type="text"
                            class="form-input"
                            id="inputLastName"
                            v-model="lastName"
                            placeholder="Ej. García"
                            required
                            autocomplete="off"
                        />
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="inputIdDoc">Cédula / Documento</label>
                        <input
                            type="text"
                            class="form-input"
                            id="inputIdDoc"
                            v-model="idDoc"
                            placeholder="Ej. V-28123456"
                            required
                            autocomplete="off"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="inputJobTitle">Cargo / Puesto</label>
                        <input
                            type="text"
                            class="form-input"
                            id="inputJobTitle"
                            v-model="position"
                            placeholder="Ej. Gerente de Ventas"
                            autocomplete="off"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="inputEmail">Correo Electrónico</label>
                    <div class="input-with-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input
                            type="email"
                            class="form-input"
                            id="inputEmail"
                            v-model="email"
                            placeholder="empleado@empresa.com"
                            required
                            autocomplete="off"
                        />
                    </div>
                </div>

                <div class="form-row-2">
                    <BaseCombobox
                        v-model="departmentId"
                        label="Departamento"
                        placeholder="-- Seleccionar --"
                        :options="departmentOptions"
                        required
                    />

                    <BaseCombobox
                        v-model="userId"
                        label="Cuenta de Usuario (Opcional)"
                        placeholder="-- No vincular --"
                        :options="userOptions"
                        clearable
                    />
                </div>

                <div class="form-actions-row">
                    <button class="btn-cancel" @click="emit('back')" type="button">Cancelar</button>
                    <button class="btn-submit" type="submit">
                        <svg viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        <span>{{ editEmployee ? 'Guardar Cambios' : 'Registrar Empleado' }}</span>
                    </button>
                </div>
            </div>

            <!-- COLUMNA DERECHA: TARJETA DE VISTA PREVIA EN VIVO -->
            <EmployeeCredentialPreview
                :full-name="previewFullName"
                :email="previewEmail"
                :position="previewPosition"
                :id-doc="idDoc"
                :dept-name="previewDeptName"
                :initials="previewInitials"
                :is-verified-user="isVerifiedUser"
            />
        </form>
    </section>
</template>
