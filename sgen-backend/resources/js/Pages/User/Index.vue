<script setup lang="ts">
import { ref, toRef, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton, BaseEmptyState } from '@/Components/UI';
import { useUserFilters, type UserItem } from '@/Composables/useUserFilters';
import { useToast } from '@/Composables/useToast';
import UserKpiRow, { type UserKpis } from '@/Components/User/UserKpiRow.vue';
import UserToolbar from '@/Components/User/UserToolbar.vue';
import UserTable from '@/Components/User/UserTable.vue';
import UserCard from '@/Components/User/UserCard.vue';
import UserFormView from '@/Components/User/UserFormView.vue';
import ModalUserDelete from '@/Components/User/ModalUserDelete.vue';
import ModalCredencialTemporal from '@/Components/User/ModalCredencialTemporal.vue';
import type { DepartmentLookup, EmployeeLookup } from '@/Types';

const props = defineProps<{
    users: UserItem[];
    kpis: UserKpis;
    departamentos: DepartmentLookup[];
    empleados: EmployeeLookup[];
    filters?: {
        search?: string;
        rol?: string;
    };
}>();

const usersRef = toRef(props, 'users');
const { search, selectedRole, viewMode, isDense, filteredUsers } = useUserFilters(usersRef);
const page = usePage();
const { addToast } = useToast();

// Credencial temporal tras restablecimiento: caja persistente solamente (no
// toast de corta vida; la misma escena IT debe verse mientras el admin la copia).
const tempPassword = ref<{ user_id: number; password: string } | null>(null);

watch(
    () => (page.props.flash as { temp_password?: { user_id: number; password: string } | null })?.temp_password,
    (payload) => {
        if (payload?.password) {
            tempPassword.value = payload;
        }
    },
    { immediate: true }
);

function cerrarCredencialTemporal(): void {
    tempPassword.value = null;
}

function copiarCredencial(): void {
    if (tempPassword.value?.password !== undefined) {
        navigator.clipboard.writeText(tempPassword.value.password).catch(() => {});
    }
}

// Subviews: 'directory' | 'form'
const activeView = ref<'directory' | 'form'>('directory');
const editingUser = ref<UserItem | null>(null);

// Delete modal
const showDeleteModal = ref(false);
const deletingUser = ref<UserItem | null>(null);

function openCreateForm() {
    editingUser.value = null;
    activeView.value = 'form';
}

function openEditForm(user: UserItem) {
    editingUser.value = user;
    activeView.value = 'form';
}

function openDeleteModal(user: UserItem) {    deletingUser.value = user;
    showDeleteModal.value = true;
}

function handleSaveUser(payload: {
    id?: number;
    username: string;
    password?: string;
    rol: string;
    departamento_id: number | null;
    empleado_id: number | null;
}) {
    if (payload.id) {
        router.put(`/usuarios/${payload.id}`, payload, {
            onSuccess: () => {
                activeView.value = 'directory';
            },
            onError: (errors) => {
                const firstErr = Object.values(errors)[0] || 'Error al actualizar el usuario.';
                addToast({ type: 'error', title: firstErr });
            },
        });
    } else {
        router.post('/usuarios', payload, {
            onSuccess: () => {
                activeView.value = 'directory';
            },
            onError: (errors) => {
                const firstErr = Object.values(errors)[0] || 'Error al registrar el usuario.';
                addToast({ type: 'error', title: firstErr });
            },
        });
    }
}

function handleConfirmDelete(id: number) {
router.delete(`/usuarios/${id}`, {
onSuccess: () => {
showDeleteModal.value = false;
},
onError: () => addToast({ type: 'error', title: 'No se pudo eliminar el usuario.' }),
});
}

function handleResetPassword(user: UserItem) {
    router.post(`/usuarios/${user.id}/restablecer`, {}, {
        preserveScroll: true,
        onSuccess: () => addToast({ type: 'success', title: 'Contraseña restablecida' }),
        onError: () => addToast({ type: 'error', title: 'No se pudo restablecer la contraseña.' }),
    });
}

function handleToggleActive(user: UserItem) {
    router.post(`/usuarios/${user.id}/alternar-estado`, {}, {
        preserveScroll: true,
        onSuccess: () => addToast({ type: 'success', title: 'Estado actualizado' }),
        onError: () => addToast({ type: 'error', title: 'No se pudo cambiar el estado del usuario.' }),
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Gestión de Usuarios" />

        <div class="module-wrapper">
            <!-- VISTA 1: DIRECTORIO DE USUARIOS -->
            <template v-if="activeView === 'directory'">
                <BasePageHeader
                    title="Gestión de Usuarios"
                    subtitle="Administra el acceso y roles de tu equipo."
                >
                    <template #icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </template>
                    <template #actions>
                        <BaseButton variant="primary" @click="openCreateForm">
                            + Nuevo Usuario
                        </BaseButton>
                    </template>
                </BasePageHeader>

                <!-- KPIs -->
                <UserKpiRow :kpis="kpis" />

                <!-- Toolbar -->
                <UserToolbar
                    v-model:search="search"
                    v-model:view-mode="viewMode"
                    v-model:is-dense="isDense"
                />

                <!-- Table View -->
                <UserTable
                    v-if="viewMode === 'table'"
                    :users="filteredUsers"
                    :is-dense="isDense"
                    @edit="openEditForm"
                    @delete="openDeleteModal"
                    @reset-password="handleResetPassword"
                    @toggle-active="handleToggleActive"
                />

                <!-- Cards View -->
                <div v-else class="users-cards-grid">
                    <UserCard
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        @edit="openEditForm"
                        @delete="openDeleteModal"
                        @reset-password="handleResetPassword"
                        @toggle-active="handleToggleActive"
                    />
                    <BaseEmptyState
                        v-if="filteredUsers.length === 0"
                        title="No se encontraron usuarios"
                        subtitle="Prueba con otros términos de búsqueda o revisa los filtros."
                    />
                </div>
            </template>

            <!-- VISTA 2: FORMULARIO CREAR / EDITAR CON LIVE DIGITAL CREDENTIAL -->
            <template v-else>
                <UserFormView
                    :user="editingUser"
                    :departamentos="departamentos"
                    :empleados="empleados"
                    @back="activeView = 'directory'"
                    @save="handleSaveUser"
                />
            </template>

            <!-- Modal Delete -->
            <ModalUserDelete
                :show="showDeleteModal"
                :user="deletingUser"
                @close="showDeleteModal = false"
                @confirm="handleConfirmDelete"
            />

            <!-- Credencial temporal tras restablecimiento: permanente, copiable; no desaparece sola -->
            <ModalCredencialTemporal
                :payload="tempPassword"
                :username="deletingUser?.username ?? ''"
                @close="cerrarCredencialTemporal"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.module-wrapper {
    width: 100%;
}

.users-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
</style>
