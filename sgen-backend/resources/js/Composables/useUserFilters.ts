import { ref, computed, type Ref } from 'vue';

export interface UserItem {
    id: number;
    username: string;
    rol: string;
    rol_label: string;
    departamento_id: number | null;
    departamento_nombre: string;
    empleado_id: number | null;
    empleado_nombre: string | null;
    empleado_email: string | null;
    avatar_initials: string;
    is_verified_employee: boolean;
}

export function useUserFilters(users: Ref<UserItem[]>) {
    const search = ref('');
    const selectedRole = ref<string>('all');
    const viewMode = ref<'table' | 'cards'>('table');
    const isDense = ref(false);

    const filteredUsers = computed(() => {
        const query = search.value.trim().toLowerCase();

        return users.value.filter((user) => {
            const matchesRole =
                selectedRole.value === 'all' || user.rol === selectedRole.value;

            if (!matchesRole) {
                return false;
            }

            if (!query) {
                return true;
            }

            const usernameMatch = user.username.toLowerCase().includes(query);
            const deptMatch = user.departamento_nombre.toLowerCase().includes(query);
            const empMatch = (user.empleado_nombre || '').toLowerCase().includes(query);
            const emailMatch = (user.empleado_email || '').toLowerCase().includes(query);

            return usernameMatch || deptMatch || empMatch || emailMatch;
        });
    });

    return {
        search,
        selectedRole,
        viewMode,
        isDense,
        filteredUsers,
    };
}
