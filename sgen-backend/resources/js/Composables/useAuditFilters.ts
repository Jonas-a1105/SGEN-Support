import { ref, computed, type Ref } from 'vue';

export interface AuditSessionItem {
    id: number;
    user_id: number;
    username: string;
    avatar_initials: string;
    start_formatted: string;
    start_time: string;
    end_time: string | null;
    duration: string;
    status: string;
    is_active: boolean;
}

export interface BitacoraActionItem {
    id: number;
    usuario_id: number | null;
    username: string;
    accion: string;
    entidad: string;
    entidad_id: number | null;
    enlace_tipo: string | null;
    enlace_id: number | null;
    datos_anteriores: Record<string, unknown> | null;
    datos_nuevos: Record<string, unknown> | null;
    created_at: string;
    ip_address: string;
}

export function useBitacoraFilters(actions: Ref<BitacoraActionItem[]>) {
    const search = ref('');
    const selectedEntity = ref<string>('all');

    const filteredActions = computed(() => {
        const query = search.value.trim().toLowerCase();
        const entity = selectedEntity.value;

        return actions.value.filter((act) => {
            if (entity !== 'all') {
                const actEnt = (act.entidad || act.enlace_tipo || '').toLowerCase();
                if (!actEnt.includes(entity.toLowerCase())) {
                    return false;
                }
            }

            if (!query) {
                return true;
            }

            const userMatch = (act.username || '').toLowerCase().includes(query);
            const actionMatch = (act.accion || '').toLowerCase().includes(query);
            const entityMatch = (act.entidad || act.enlace_tipo || '').toLowerCase().includes(query);
            const ipMatch = (act.ip_address || '').includes(query);

            return userMatch || actionMatch || entityMatch || ipMatch;
        });
    });

    return {
        search,
        selectedEntity,
        filteredActions,
    };
}

export function useAuditFilters(sessions: Ref<AuditSessionItem[]>) {
    const search = ref('');
    const statusFilter = ref<'all' | 'active' | 'ended'>('all');

    const filteredSessions = computed(() => {
        const query = search.value.trim().toLowerCase();

        return sessions.value.filter((s) => {
            if (statusFilter.value === 'active' && !s.is_active) {
                return false;
            }
            if (statusFilter.value === 'ended' && s.is_active) {
                return false;
            }

            if (!query) {
                return true;
            }

            const userMatch = s.username.toLowerCase().includes(query);
            const idMatch = String(s.user_id).includes(query);
            const dateMatch = s.start_formatted.includes(query);

            return userMatch || idMatch || dateMatch;
        });
    });

    return {
        search,
        statusFilter,
        filteredSessions,
    };
}
