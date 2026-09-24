import { ref, computed, type Ref } from 'vue';

export interface EquipmentItem {
    id: string;
    numericId: number;
    name: string;
    type: string;
    dept: string;
    deptShort: string;
    status: string;
    rawStatus: string;
    serialNumber?: string | null;
    assignedTo?: string | null;
    location?: string | null;
    ipAddress?: string | null;
}

export function useEquipmentFilters(items: Ref<EquipmentItem[]>) {
    const currentPillFilter = ref<string>('all');
    const searchQuery = ref<string>('');
    const isDense = ref<boolean>(false);
    const activeViewMode = ref<'grid' | 'table'>('grid');

    const filteredEquipos = computed<EquipmentItem[]>(() => {
        const q = searchQuery.value.toLowerCase().trim();
        const pill = currentPillFilter.value;

        return items.value.filter((item) => {
            // Pill filter
            if (pill !== 'all') {
                if (pill === 'Reparación') {
                    if (item.status !== 'Reparación' && item.rawStatus !== 'en_reparacion') return false;
                } else if (pill === 'Baja') {
                    if (item.status !== 'Baja' && item.rawStatus !== 'fuera_de_servicio') return false;
                } else if (pill === 'En Uso') {
                    if (item.status !== 'En Uso' && item.rawStatus !== 'en_uso') return false;
                } else if (pill === 'Disponible') {
                    if (item.status !== 'Disponible' && item.rawStatus !== 'disponible') return false;
                } else if (item.status !== pill) {
                    return false;
                }
            }

            // Search query filter
            if (q) {
                const matchName = item.name.toLowerCase().includes(q);
                const matchId = item.id.toLowerCase().includes(q);
                const matchDept = item.dept.toLowerCase().includes(q);
                const matchType = item.type.toLowerCase().includes(q);
                const matchSerial = (item.serialNumber || '').toLowerCase().includes(q);
                const matchUser = (item.assignedTo || '').toLowerCase().includes(q);
                return matchName || matchId || matchDept || matchType || matchSerial || matchUser;
            }

            return true;
        });
    });

    const setPillFilter = (pill: string) => {
        currentPillFilter.value = pill;
    };

    const setViewMode = (mode: 'grid' | 'table') => {
        activeViewMode.value = mode;
    };

    const toggleDense = () => {
        isDense.value = !isDense.value;
    };

    return {
        currentPillFilter,
        searchQuery,
        isDense,
        activeViewMode,
        filteredEquipos,
        setPillFilter,
        setViewMode,
        toggleDense,
    };
}
