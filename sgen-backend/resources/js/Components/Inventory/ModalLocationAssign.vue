<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Equipment, Department, Employee } from '@/Types/inventory';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = defineProps<{
    isOpen: boolean;
    equipment: Equipment | null;
    departamentos: Department[];
    empleados: Employee[];
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const form = useForm({
    equipo_id: props.equipment?.id || 0,
    departamento_id: props.equipment?.departamento_id || null,
    empleado_id: props.equipment?.empleado_id || null,
    ubicacion_fisica: props.equipment?.ubicacion_fisica || '',
});

watch(
    () => props.equipment,
    (newEq) => {
        if (newEq) {
            form.equipo_id = newEq.id;
            form.departamento_id = newEq.departamento_id || null;
            form.empleado_id = newEq.empleado_id || null;
            form.ubicacion_fisica = newEq.ubicacion_fisica || '';
        }
    },
    { immediate: true }
);

const submit = () => {
    form.post('/equipos/reasignar', {
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

            <div class="form-group">
                <label class="form-label">Nuevo Departamento</label>
                <select v-model="form.departamento_id" class="form-select" id="reassignDept">
                    <option :value="null">No Asignado (Almacén Informática)</option>
                    <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Usuario Asignado</label>
                <select v-model="form.empleado_id" class="form-select" id="reassignUser">
                    <option :value="null">-- Sin asignar --</option>
                    <option v-for="emp in empleados" :key="emp.id" :value="emp.id">
                        {{ emp.nombre }} {{ emp.apellido || '' }}
                    </option>
                </select>
            </div>

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

.modal-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    margin-top: var(--space-2);
    padding-top: var(--space-3);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
