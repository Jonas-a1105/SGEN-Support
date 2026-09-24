<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { EmployeeItem } from '@/Composables/useEmployeeFilters';
import EmployeeCredentialPreview from './EmployeeCredentialPreview.vue';

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
        departamento_id: departmentId.value !== '' ? departmentId.value : null,
        usuario_id: userId.value !== '' ? userId.value : null,
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
                    <div class="form-group">
                        <label class="form-label" for="selectDepartment">Departamento</label>
                        <div class="input-with-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="2"></line>
                                <line x1="15" y1="22" x2="15" y2="2"></line>
                            </svg>
                            <select class="form-select" id="selectDepartment" v-model="departmentId" required>
                                <option value="" disabled>-- Seleccionar --</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="selectUserAccount">
                            <span>Cuenta de Usuario</span>
                            <span class="label-optional">OPCIONAL</span>
                        </label>
                        <div class="input-with-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            <select class="form-select" id="selectUserAccount" v-model="userId">
                                <option value="">-- No vincular --</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    Vincular a {{ user.username }} ({{ user.rol }})
                                </option>
                            </select>
                        </div>
                    </div>
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

            <!-- COLUMNA DERECHA: TARJETA DE VISTA PREVIA EN VIVO (Componente Modular) -->
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

<style scoped>
.employee-form-view {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.module-title-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
}

.module-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--blue);
    display: grid;
    place-items: center;
    color: #ffffff;
    box-shadow: none !important;
}

.module-icon-box svg {
    width: 22px;
    height: 22px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.module-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: var(--text);
    margin: 0 0 2px 0;
}

.module-subtitle {
    font-size: 12px;
    color: var(--text-dim);
    margin: 0;
}

.btn-back {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text);
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.form-grid-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}

@media (max-width: 860px) {
    .form-grid-layout {
        grid-template-columns: 1fr;
    }
}

.form-card-panel {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: none !important;
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 580px) {
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
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.label-optional {
    font-size: 9px;
    font-weight: 600;
    color: var(--text-dim);
    background: var(--stroke-subtle);
    padding: 1px 5px;
    border-radius: 4px;
}

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-with-icon svg {
    position: absolute;
    left: 12px;
    width: 16px;
    height: 16px;
    stroke: var(--text-dim);
    fill: none;
    stroke-width: 2;
    pointer-events: none;
}

.input-with-icon .form-input,
.input-with-icon .form-select {
    padding-left: 38px;
}

.form-input,
.form-select {
    width: 100%;
    height: 40px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    color: var(--text);
    font-size: 13px;
    padding: 0 14px;
    outline: none;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.form-input:focus,
.form-select:focus {
    border-color: var(--blue);
}

.form-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
    padding-top: 16px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.btn-cancel {
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.btn-submit {
    background: var(--blue);
    border: var(--stroke-w) solid var(--blue);
    color: #ffffff;
    font-size: 13px;
    padding: 8px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 600;
    box-shadow: none !important;
    transition: opacity 0.2s ease;
}

.btn-submit svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}

.btn-submit:hover {
    opacity: 0.9;
}
</style>
