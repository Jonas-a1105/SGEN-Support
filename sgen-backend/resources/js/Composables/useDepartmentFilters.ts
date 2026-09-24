import { ref, computed, type Ref } from 'vue';

export interface DepartmentItem {
    numericId: number;
    id: string;
    code: string;
    name: string;
    desc: string;
    location: string;
    equipos: number;
    empleados: number;
    inventoryPercent: number;
    color: string;
    manager?: string | null;
    managerId?: number | null;
}

export function useDepartmentFilters(items: Ref<DepartmentItem[]>) {
    const searchQuery = ref<string>('');
    const isDense = ref<boolean>(false);
    const activeViewMode = ref<'grid' | 'table'>('grid');

    const filteredDepartments = computed<DepartmentItem[]>(() => {
        const q = searchQuery.value.toLowerCase().trim();

        return items.value.filter((d) => {
            if (!q) return true;
            return (
                d.name.toLowerCase().includes(q) ||
                d.code.toLowerCase().includes(q) ||
                d.desc.toLowerCase().includes(q) ||
                (d.location && d.location.toLowerCase().includes(q)) ||
                (d.manager && d.manager.toLowerCase().includes(q))
            );
        });
    });

    const setViewMode = (mode: 'grid' | 'table') => {
        activeViewMode.value = mode;
    };

    const toggleDense = () => {
        isDense.value = !isDense.value;
    };

    return {
        searchQuery,
        isDense,
        activeViewMode,
        filteredDepartments,
        setViewMode,
        toggleDense,
    };
}
