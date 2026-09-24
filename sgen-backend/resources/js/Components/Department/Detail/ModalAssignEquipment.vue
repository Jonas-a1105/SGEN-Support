<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseCombobox } from '@/Components/UI';
import type { ComboboxOption } from '@/Components/UI';
import type { DepartmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    department: DepartmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const selectedEquipmentId = ref<number | ''>('');
const isSubmitting = ref(false);

const equipmentOptions = computed<ComboboxOption[]>(() => {
    return (props.department.candidatosEquipos || []).map((cand) => ({
        value: cand.id,
        label: `[${cand.codigo}] ${cand.nombre}`,
        sublabel: `Estado: ${cand.estado}`,
    }));
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            selectedEquipmentId.value = '';
        }
    }
);

const handleAssign = () => {
    if (!selectedEquipmentId.value) return;

    isSubmitting.value = true;
    router.post(
        `/departamentos/${props.department.id}/equipos`,
        { equipo_id: selectedEquipmentId.value },
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                emit('close');
                selectedEquipmentId.value = '';
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Asignar Equipo al Departamento"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleAssign" class="modal-form-stack">
            <BaseCombobox
                :model-value="selectedEquipmentId"
                label="Seleccionar Equipo Disponible"
                placeholder="Seleccione un equipo..."
                search-placeholder="Buscar equipo por código o nombre..."
                :options="equipmentOptions"
                :searchable="true"
                required
                :help-text="`El equipo quedará registrado bajo la custodia física del departamento ${department.nombre}.`"
                @update:model-value="(val) => { selectedEquipmentId = val ? Number(val) : ''; }"
            />

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="!selectedEquipmentId || isSubmitting">
                    Asignar Equipo
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
