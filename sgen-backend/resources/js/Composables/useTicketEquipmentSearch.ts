import { ref, computed, type Ref } from 'vue';
import type { FormEquipment } from '@/Components/Support/Create/types';

export function useTicketEquipmentSearch(
    equipmentsRef: Ref<FormEquipment[] | undefined>,
    onSelectCallback?: (eq: FormEquipment) => void
) {
    const deviceSearch = ref('');
    const selectedEquipmentId = ref<number | null>(equipmentsRef.value?.[0]?.id ?? null);
    const isDeviceModalOpen = ref(false);

    const selectedEquipment = computed<FormEquipment | undefined>(() => {
        if (!selectedEquipmentId.value || !equipmentsRef.value) return undefined;
        return equipmentsRef.value.find((e) => e.id === selectedEquipmentId.value);
    });

    const searchResults = computed<FormEquipment[]>(() => {
        const q = deviceSearch.value.trim().toLowerCase();
        if (!q || !equipmentsRef.value) return [];
        return equipmentsRef.value.filter((eq) => {
            const code = (eq.code || '').toLowerCase();
            const serial = (eq.serial || '').toLowerCase();
            const model = (eq.model || '').toLowerCase();
            const dept = (eq.department || '').toLowerCase();
            const assigned = (eq.assigned_to || '').toLowerCase();
            return (
                code.includes(q) ||
                serial.includes(q) ||
                model.includes(q) ||
                dept.includes(q) ||
                assigned.includes(q)
            );
        });
    });

    function selectEquipment(eq: FormEquipment) {
        selectedEquipmentId.value = eq.id;
        isDeviceModalOpen.value = false;
        deviceSearch.value = '';
        if (onSelectCallback) {
            onSelectCallback(eq);
        }
    }

    function clearEquipment() {
        selectedEquipmentId.value = null;
    }

    function handleSearchSubmit() {
        if (searchResults.value.length === 1) {
            selectEquipment(searchResults.value[0]);
        } else if (searchResults.value.length > 1) {
            isDeviceModalOpen.value = true;
        }
    }

    return {
        deviceSearch,
        selectedEquipmentId,
        isDeviceModalOpen,
        selectedEquipment,
        searchResults,
        selectEquipment,
        clearEquipment,
        handleSearchSubmit,
    };
}
