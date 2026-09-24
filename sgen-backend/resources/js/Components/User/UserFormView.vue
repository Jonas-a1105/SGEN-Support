<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { UserItem } from '@/Composables/useUserFilters';
import UserCredentialPreview from './UserCredentialPreview.vue';
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';

interface DepartmentLookup {
    id: number;
    nombre: string;
}

interface EmployeeLookup {
    id: number;
    nombre: string;
    apellido: string;
    departamento_id: number | null;
}

const props = defineProps<{
    user?: UserItem | null;
    departamentos: DepartmentLookup[];
    empleados: EmployeeLookup[];
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'save', payload: {
        id?: number;
        username: string;
        password?: string;
        rol: string;
        departamento_id: number | null;
        empleado_id: number | null;
    }): void;
}>();

const username = ref('');
const password = ref('');
const role = ref('tecnico');
const linkOrg = ref(false);
const departmentId = ref<number | ''>('');
const employeeId = ref<number | ''>('');

watch(
    () => props.user,
    (u) => {
        if (u) {
            username.value = u.username;
            password.value = '';
            role.value = u.rol;
            linkOrg.value = u.departamento_id !== null || u.empleado_id !== null;
            departmentId.value = u.departamento_id ?? '';
            employeeId.value = u.empleado_id ?? '';
        } else {
            username.value = '';
            password.value = '';
            role.value = 'tecnico';
            linkOrg.value = false;
            departmentId.value = '';
            employeeId.value = '';
        }
    },
    { immediate: true }
);

const roleOptions = [
    { value: 'tecnico', label: 'Técnico' },
    { value: 'admin', label: 'Administrador' },
    { value: 'consultor', label: 'Consultor' },
    { value: 'operador', label: 'Operador' },
];

const filteredEmployees = computed(() => {
    if (!departmentId.value) {
        return props.empleados;
    }
    return props.empleados.filter(
        (e) => e.departamento_id === departmentId.value || e.departamento_id === null
    );
});

const departmentOptions = computed(() => [
    { value: '', label: 'Sin Asignar' },
    ...props.departamentos.map((d) => ({
        value: d.id,
        label: d.nombre,
    })),
]);

const employeeOptions = computed(() => [
    { value: '', label: 'Sin Vinculación Directa' },
    ...filteredEmployees.value.map((e) => ({
        value: e.id,
        label: `${e.nombre} ${e.apellido}`,
    })),
]);

// Live preview computations
const previewDisplayName = computed(() => {
    if (employeeId.value) {
        const emp = props.empleados.find((e) => e.id === Number(employeeId.value));
        if (emp) {
            return `${emp.nombre} ${emp.apellido}`;
        }
    }
    return username.value.trim() || 'Nombre Usuario';
});

const previewHandle = computed(() => {
    return username.value.trim() ? `@${username.value.trim()}` : '@username';
});

const previewRoleLabel = computed(() => {
    const map: Record<string, string> = {
        admin: 'ADMINISTRADOR',
        tecnico: 'TÉCNICO',
        consultor: 'CONSULTOR',
        operador: 'OPERADOR',
    };
    return map[role.value.toLowerCase()] || 'TÉCNICO';
});

const previewDeptName = computed(() => {
    if (!linkOrg.value || !departmentId.value) {
        return 'Sin Departamento';
    }
    const dept = props.departamentos.find((d) => d.id === Number(departmentId.value));
    return dept ? dept.nombre : 'Sin Departamento';
});

const previewInitials = computed(() => {
    if (employeeId.value) {
        const emp = props.empleados.find((e) => e.id === Number(employeeId.value));
        if (emp) {
            return `${emp.nombre.charAt(0)}${emp.apellido.charAt(0)}`.toUpperCase();
        }
    }
    const clean = username.value.trim();
    if (!clean) return 'U';
    return clean.substring(0, 2).toUpperCase();
});

const isVerified = computed(() => {
    return linkOrg.value && employeeId.value !== '';
});

function handleEmployeeSelect(val: string | number | null) {
    if (val) {
        const emp = props.empleados.find((e) => e.id === Number(val));
        if (emp && emp.departamento_id) {
            departmentId.value = emp.departamento_id;
        }
    }
}

function handleSubmit() {
    emit('save', {
        id: props.user?.id,
        username: username.value.trim(),
        password: password.value || undefined,
        rol: role.value,
        departamento_id: linkOrg.value && departmentId.value !== '' ? Number(departmentId.value) : null,
        empleado_id: linkOrg.value && employeeId.value !== '' ? Number(employeeId.value) : null,
    });
}
</script>

<template>
    <section class="module-wrapper user-form-view" aria-label="Formulario de usuario">
        <div class="module-header">
            <div class="module-title-wrap">
                <div class="module-icon-box">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="8.5" cy="7" r="4" /><line x1="20" y1="8" x2="20" y2="14" /><line x1="23" y1="11" x2="17" y2="11" /></svg>
                </div>
                <div>
                    <h1 class="module-title">{{ user ? 'Editar Usuario' : 'Crear Usuario' }}</h1>
                    <p class="module-subtitle">Ingresa los datos para generar la credencial digital.</p>
                </div>
            </div>
            <button class="btn-back" type="button" @click="emit('back')">
                <span>←</span>
                <span>Volver al Directorio</span>
            </button>
        </div>

        <form class="form-grid-layout" @submit.prevent="handleSubmit">
            <!-- Left Panel: Inputs -->
            <div class="form-card-panel">
                <div class="form-group">
                    <label class="form-label" for="inputUsername">Nombre de Usuario</label>
                    <input
                        id="inputUsername"
                        v-model="username"
                        type="text"
                        class="form-input"
                        placeholder="Ej: jperez"
                        required
                        autocomplete="off"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label" for="inputPassword">
                        {{ user ? 'Contraseña (dejar en blanco para no cambiar)' : 'Contraseña' }}
                    </label>
                    <div class="input-with-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                        <input
                            id="inputPassword"
                            v-model="password"
                            type="password"
                            class="form-input"
                            placeholder="Contraseña segura"
                            :required="!user"
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <BaseCombobox
                        v-model="role"
                        label="Nivel de Acceso (Rol)"
                        :options="roleOptions"
                        :searchable="false"
                        required
                    />
                </div>

                <!-- Checkbox Vincular Organización -->
                <label class="checkbox-card-wrap">
                    <input v-model="linkOrg" type="checkbox" />
                    <div class="checkbox-copy-block">
                        <span class="checkbox-title">Vincular a la organización</span>
                        <span class="checkbox-desc">Asignar departamento y vincular a un empleado existente.</span>
                    </div>
                </label>

                <!-- Selectores Dinámicos con BaseCombobox -->
                <div v-if="linkOrg" class="org-selectors-wrap">
                    <div class="form-group">
                        <BaseCombobox
                            v-model="departmentId"
                            label="Departamento Asignado"
                            placeholder="Seleccionar departamento..."
                            :options="departmentOptions"
                            clearable
                        />
                    </div>

                    <div class="form-group">
                        <BaseCombobox
                            v-model="employeeId"
                            label="Colaborador Existente"
                            placeholder="Seleccionar colaborador..."
                            :options="employeeOptions"
                            clearable
                            @update:model-value="handleEmployeeSelect"
                        />
                    </div>
                </div>

                <div class="form-actions-row">
                    <button class="btn-cancel" type="button" @click="emit('back')">Cancelar</button>
                    <button class="btn-submit" type="submit">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                        <span>{{ user ? 'Actualizar Usuario' : 'Crear Usuario' }}</span>
                    </button>
                </div>
            </div>

            <!-- Right Panel: Live Digital Credential Card (Modular Component) -->
            <UserCredentialPreview
                :display-name="previewDisplayName"
                :handle="previewHandle"
                :role-label="previewRoleLabel"
                :dept-name="previewDeptName"
                :initials="previewInitials"
                :is-verified="isVerified"
            />
        </form>
    </section>
</template>

<style scoped>
.user-form-view {
    max-width: 1100px;
    margin: 0 auto;
}

.checkbox-card-wrap {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 12px;
    cursor: pointer;
    box-shadow: none !important;
}

.checkbox-card-wrap input[type='checkbox'] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: #4f46e5;
}

.checkbox-copy-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.checkbox-title {
    font-size: 13px;
    font-weight: 600 !important;
    color: var(--text, #f4f4f6);
}

.checkbox-desc {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.org-selectors-wrap {
    padding-left: 12px;
    border-left: 2px solid rgba(79, 70, 229, 0.4);
    display: flex;
    flex-direction: column;
    gap: 14px;
}
</style>
