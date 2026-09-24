<script setup lang="ts">
import type { Department, Employee } from '@/Types/inventory';

defineProps<{
    departamentoId: number | null;
    empleadoId: number | null;
    departamentos: Department[];
    empleados: Employee[];
}>();

const emit = defineEmits<{
    (e: 'update:departamentoId', val: number | null): void;
    (e: 'update:empleadoId', val: number | null): void;
}>();
</script>

<template>
    <div id="wizardStep3" class="step-pane-body">
        <div class="form-group">
            <label class="form-label" for="wizDept">Departamento Asignado</label>
            <select
                :value="departamentoId"
                class="form-select"
                id="wizDept"
                @change="emit('update:departamentoId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            >
                <option :value="null">No Asignado (Almacén Informática)</option>
                <option v-for="d in departamentos" :key="d.id" :value="d.id">
                    {{ d.nombre }}
                </option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="wizUser">Funcionario Responsable</label>
            <select
                :value="empleadoId"
                class="form-select"
                id="wizUser"
                @change="emit('update:empleadoId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            >
                <option :value="null">-- Sin asignar --</option>
                <option v-for="emp in empleados" :key="emp.id" :value="emp.id">
                    {{ emp.nombre }} {{ emp.apellido || '' }}
                </option>
            </select>
        </div>
    </div>
</template>

<style scoped>
.step-pane-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.form-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.form-select {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--radius-md);
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 13px;
    font-family: var(--font-sans);
    outline: none;
    transition: border-color var(--transition-fast);
}

.form-select:focus {
    border-color: var(--orange);
}
</style>
