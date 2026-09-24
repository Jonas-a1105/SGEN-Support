<script setup lang="ts">
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Equipment, Department, Employee } from '@/Types/inventory';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';

const props = defineProps<{
    isOpen: boolean;
    equipment: Equipment | null;
    departamentos: Department[];
    empleados: Employee[];
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const form = useForm({
    equipo_id: props.equipment?.id || 0,
    departamento_id: (props.equipment?.departamento_id ? String(props.equipment.departamento_id) : '') as string | number,
    empleado_id: (props.equipment?.empleado_id ? String(props.equipment.empleado_id) : '') as string | number,
    ubicacion_fisica: props.equipment?.ubicacion_fisica || '',
});

const deptOptions = computed<ComboboxOption[]>(() => [
    { value: '', label: 'No Asignado (Almacén Informática)' },
    ...props.departamentos.map((d) => ({ value: d.id, label: d.nombre })),
]);

const empOptions = computed<ComboboxOption[]>(() => [
    { value: '', label: 'Sin asignar' },
    ...props.empleados.map((emp) => ({
        value: emp.id,
        label: `${emp.nombre} ${emp.apellido || ''}`.trim(),
    })),
]);

watch(
    () => props.equipment,
    (newEq) => {
        if (newEq) {
            form.equipo_id = newEq.id;
            form.departamento_id = newEq.departamento_id ? String(newEq.departamento_id) : '';
            form.empleado_id = newEq.empleado_id ? String(newEq.empleado_id) : '';
            form.ubicacion_fisica = newEq.ubicacion_fisica || '';
        }
    },
    { immediate: true }
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        departamento_id: data.departamento_id ? Number(data.departamento_id) : null,
        empleado_id: data.empleado_id ? Number(data.empleado_id) : null,
    })).post('/equipos/reasignar', {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Reasignar Ubicación del Equipo"
        max-width="md"
        @close="emit('close')"
    >
        <form id="formReassignLocation" class="modal-form-stack" @submit.prevent="submit">
            <div v-if="equipment" class="equipment-summary-box">
                <div class="equipment-summary-name">
                    Equipo: <strong>{{ equipment.marca }} {{ equipment.modelo }}</strong>
                </div>
                <div class="equipment-summary-dim">
                    Código: {{ equipment.codigo_inventario }} | S/N: {{ equipment.numero_serie || 'N/A' }}
                </div>
            </div>

            <BaseCombobox
                v-model="form.departamento_id"
                label="Nuevo Departamento"
                placeholder="Seleccione un departamento..."
                search-placeholder="Buscar departamento..."
                :options="deptOptions"
                :searchable="true"
                clearable
            />

            <BaseCombobox
                v-model="form.empleado_id"
                label="Usuario Asignado"
                placeholder="Seleccione un custodio..."
                search-placeholder="Buscar colaborador..."
                :options="empOptions"
                :searchable="true"
                clearable
            />

            <div class="modal-actions-row">
                <BaseButton variant="secondary" id="cancelLocModal" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton variant="primary" :disabled="form.processing" :loading="form.processing" type="submit">
                    Actualizar Asignación
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

.equipment-summary-box {
    padding: var(--space-3) var(--space-4);
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: var(--radius-md);
}

.equipment-summary-name {
    font-size: 13px;
    color: var(--text);
    margin-bottom: 2px;
}

.equipment-summary-dim {
    font-size: 11px;
    color: var(--text-dim);
    font-family: var(--font-mono);
}

.modal-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    margin-top: var(--space-2);
    padding-top: var(--space-3);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
