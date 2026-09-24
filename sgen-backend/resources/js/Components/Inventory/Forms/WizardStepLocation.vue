<script setup lang="ts">
import { computed } from 'vue';
import type { Department, Employee } from '@/Types/inventory';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';

const props = defineProps<{
    departamentoId: number | null;
    empleadoId: number | null;
    departamentos: Department[];
    empleados: Employee[];
}>();

const emit = defineEmits<{
    (e: 'update:departamentoId', val: number | null): void;
    (e: 'update:empleadoId', val: number | null): void;
}>();

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
</script>

<template>
    <div id="wizardStep3" class="step-pane-body">
        <BaseCombobox
            :model-value="departamentoId ?? ''"
            label="Departamento Asignado"
            placeholder="Seleccione departamento..."
            search-placeholder="Buscar departamento..."
            :options="deptOptions"
            :searchable="true"
            @update:model-value="(val) => emit('update:departamentoId', val ? Number(val) : null)"
        />

        <BaseCombobox
            :model-value="empleadoId ?? ''"
            label="Funcionario Responsable"
            placeholder="Seleccione funcionario..."
            search-placeholder="Buscar colaborador..."
            :options="empOptions"
            :searchable="true"
            @update:model-value="(val) => emit('update:empleadoId', val ? Number(val) : null)"
        />
    </div>
</template>

<style scoped>
.step-pane-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}
</style>
