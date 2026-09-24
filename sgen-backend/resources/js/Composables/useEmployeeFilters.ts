import { ref, computed, type Ref } from 'vue';

export interface EmployeeItem {
    numericId: number;
    id: string;
    fullName: string;
    firstName: string;
    lastName: string;
    position: string;
    dept: string;
    departmentId?: number | null;
    email: string;
    idDoc: string;
    userAccount?: string | null;
    initials: string;
    tint: string;
    role: string;
}

export function useEmployeeFilters(items: Ref<EmployeeItem[]>) {
    const currentDeptFilter = ref<string>('all');
    const currentKpiFilter = ref<string>('all');
    const searchQuery = ref<string>('');
    const isDense = ref<boolean>(false);
    const activeViewMode = ref<'grid' | 'table'>('grid');

    const filteredEmployees = computed<EmployeeItem[]>(() => {
        const q = searchQuery.value.toLowerCase().trim();
        const dept = currentDeptFilter.value;
        const kpi = currentKpiFilter.value;

        return items.value.filter((emp) => {
            // Department filter
            if (dept !== 'all' && emp.dept !== dept) {
                return false;
            }

            // KPI filter
            if (kpi === 'active' && !emp.userAccount) return false;
            if (kpi === 'inactive' && emp.userAccount) return false;

            // Search query filter
            if (q) {
                const matchName = emp.fullName.toLowerCase().includes(q);
                const matchDept = emp.dept.toLowerCase().includes(q);
                const matchEmail = emp.email.toLowerCase().includes(q);
                const matchDoc = emp.idDoc.toLowerCase().includes(q);
                const matchPos = emp.position.toLowerCase().includes(q);
                const matchId = emp.id.toLowerCase().includes(q);
                return matchName || matchDept || matchEmail || matchDoc || matchPos || matchId;
            }

            return true;
        });
    });

    const setDeptFilter = (dept: string) => {
        currentDeptFilter.value = dept;
    };

    const setKpiFilter = (kpi: string) => {
        currentKpiFilter.value = kpi;
    };

    const setViewMode = (mode: 'grid' | 'table') => {
        activeViewMode.value = mode;
    };

    const toggleDense = () => {
        isDense.value = !isDense.value;
    };

    return {
        currentDeptFilter,
        currentKpiFilter,
        searchQuery,
        isDense,
        activeViewMode,
        filteredEmployees,
        setDeptFilter,
        setKpiFilter,
        setViewMode,
        toggleDense,
    };
}
