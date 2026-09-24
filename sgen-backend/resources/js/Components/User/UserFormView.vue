<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { UserItem } from '@/Composables/useUserFilters';
import UserCredentialPreview from './UserCredentialPreview.vue';

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
const departmentId = ref<number | null>(null);
const employeeId = ref<number | null>(null);

watch(
    () => props.user,
    (u) => {
        if (u) {
            username.value = u.username;
            password.value = '';
            role.value = u.rol;
            linkOrg.value = u.departamento_id !== null || u.empleado_id !== null;
            departmentId.value = u.departamento_id;
            employeeId.value = u.empleado_id;
        } else {
            username.value = '';
            password.value = '';
            role.value = 'tecnico';
            linkOrg.value = false;
            departmentId.value = null;
            employeeId.value = null;
        }
    },
    { immediate: true }
);

// Department selection can filter employees
const filteredEmployees = computed(() => {
    if (!departmentId.value) {
        return props.empleados;
    }
    return props.empleados.filter(
        (e) => e.departamento_id === departmentId.value || e.departamento_id === null
    );
});

// Live preview computations
const previewDisplayName = computed(() => {
    if (employeeId.value) {
        const emp = props.empleados.find((e) => e.id === employeeId.value);
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
    const dept = props.departamentos.find((d) => d.id === departmentId.value);
    return dept ? dept.nombre : 'Sin Departamento';
});

const previewInitials = computed(() => {
    if (employeeId.value) {
        const emp = props.empleados.find((e) => e.id === employeeId.value);
        if (emp) {
            return `${emp.nombre.charAt(0)}${emp.apellido.charAt(0)}`.toUpperCase();
        }
    }
    const clean = username.value.trim();
    if (!clean) return 'U';
    return clean.substring(0, 2).toUpperCase();
});

const isVerified = computed(() => {
    return linkOrg.value && employeeId.value !== null;
});

function handleEmployeeSelect(event: Event) {
    const val = (event.target as HTMLSelectElement).value;
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
        departamento_id: linkOrg.value ? departmentId.value : null,
        empleado_id: linkOrg.value ? employeeId.value : null,
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
                            class="form-input has-icon"
                            placeholder="Contraseña segura"
                            :required="!user"
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="selectRole">Nivel de Acceso (Rol)</label>
                    <select id="selectRole" v-model="role" class="form-select" required>
                        <option value="tecnico">Técnico</option>
                        <option value="admin">Administrador</option>
                        <option value="consultor">Consultor</option>
                        <option value="operador">Operador</option>
                    </select>
                </div>

                <!-- Checkbox Vincular Organización -->
                <label class="checkbox-card-wrap">
                    <input v-model="linkOrg" type="checkbox" />
                    <div class="checkbox-copy-block">
                        <span class="checkbox-title">Vincular a la organización</span>
                        <span class="checkbox-desc">Asignar departamento y vincular a un empleado existente.</span>
                    </div>
                </label>

                <!-- Selectores Dinámicos -->
                <div v-if="linkOrg" class="org-selectors-wrap">
                    <div class="form-group">
                        <label class="form-label" for="selectDepartment">Departamento Asignado</label>
                        <select id="selectDepartment" v-model="departmentId" class="form-select">
                            <option :value="null">Sin Asignar</option>
                            <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="selectEmployee">Colaborador Existente</label>
                        <select
                            id="selectEmployee"
                            v-model="employeeId"
                            class="form-select"
                            @change="handleEmployeeSelect"
                        >
                            <option :value="null">Sin Vinculación Directa</option>
                            <option v-for="e in filteredEmployees" :key="e.id" :value="e.id">
                                {{ e.nombre }} {{ e.apellido }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-actions-row">
                    <button class="btn-submit" type="submit">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                        <span>{{ user ? 'Actualizar Usuario' : 'Crear Usuario' }}</span>
                    </button>
                    <button class="btn-cancel" type="button" @click="emit('back')">Cancelar</button>
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
.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
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
    background: #4f46e5;
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
    color: var(--text, #f4f4f6);
    margin: 0 0 2px 0;
}
.module-subtitle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin: 0;
}
.btn-back {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text, #f4f4f6);
    border-radius: 10px;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    cursor: pointer;
    box-shadow: none !important;
}

.form-grid-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 860px) {
    .form-grid-layout {
        grid-template-columns: 1fr;
    }
}

.form-card-panel {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    padding: 24px;
    box-shadow: none !important;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 18px;
}
.form-label {
    font-size: 12px;
    font-weight: 600 !important;
    color: var(--text, #f4f4f6);
}
.form-input,
.form-select {
    width: 100%;
    height: 42px;
    border-radius: 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text, #f4f4f6);
    padding: 0 14px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
    box-shadow: none !important;
}
.form-input:focus,
.form-select:focus {
    border-color: #4f46e5;
}
.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}
.input-with-icon svg {
    position: absolute;
    left: 14px;
    width: 16px;
    height: 16px;
    stroke: var(--text-muted, #8e9199);
    fill: none;
    stroke-width: 2;
    pointer-events: none;
}
.form-input.has-icon {
    padding-left: 40px;
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
    margin-bottom: 18px;
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
    margin-bottom: 18px;
}

.form-actions-row {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}
.btn-submit {
    background: #4f46e5;
    border: var(--stroke-w, 2px) solid #4f46e5;
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700 !important;
    cursor: pointer;
    box-shadow: none !important;
}
.btn-submit svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
}
.btn-cancel {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text, #f4f4f6);
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    cursor: pointer;
    box-shadow: none !important;
}
</style>
