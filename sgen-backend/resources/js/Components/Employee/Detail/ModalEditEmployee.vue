<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput } from '@/Components/UI';
import type { EmployeeDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    employee: EmployeeDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const editForm = ref({
    nombre: props.employee.nombre,
    apellido: props.employee.apellido,
    email: props.employee.email,
    cedula: props.employee.cedula ?? '',
    cargo: props.employee.cargo ?? '',
    telefono: props.employee.telefono ?? '',
    departamento_id: props.employee.departamentoId ?? '',
    rol: props.employee.rol,
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            editForm.value = {
                nombre: props.employee.nombre,
                apellido: props.employee.apellido,
                email: props.employee.email,
                cedula: props.employee.cedula ?? '',
                cargo: props.employee.cargo ?? '',
                telefono: props.employee.telefono ?? '',
                departamento_id: props.employee.departamentoId ?? '',
                rol: props.employee.rol,
            };
        }
    }
);

const isSubmitting = ref(false);

const handleSave = () => {
    isSubmitting.value = true;
    router.put(
        `/personal/${props.employee.id}`,
        {
            nombre: editForm.value.nombre,
            apellido: editForm.value.apellido,
            email: editForm.value.email,
            cedula: editForm.value.cedula || null,
            cargo: editForm.value.cargo || null,
            telefono: editForm.value.telefono || null,
            departamento_id: editForm.value.departamento_id || null,
            rol: editForm.value.rol,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                emit('close');
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Editar Información del Colaborador"
        max-width="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSave" class="modal-form-stack">
            <div class="form-grid-2">
                <BaseInput v-model="editForm.nombre" label="Nombre *" required />
                <BaseInput v-model="editForm.apellido" label="Apellido *" required />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.email" type="email" label="Correo Electrónico *" required />
                <BaseInput v-model="editForm.cedula" label="Cédula de Identidad" placeholder="V-12345678" />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.cargo" label="Cargo / Posición" placeholder="Ej. Analista de Sistemas" />
                <BaseInput v-model="editForm.telefono" label="Teléfono" placeholder="0414-1234567" />
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Departamento</label>
                    <select v-model="editForm.departamento_id" class="form-control-select">
                        <option value="">Sin departamento</option>
                        <option
                            v-for="d in employee.departamentos"
                            :key="d.id"
                            :value="d.id"
                        >
                            {{ d.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Rol en Plataforma *</label>
                    <select v-model="editForm.rol" class="form-control-select" required>
                        <option value="tecnico">Técnico</option>
                        <option value="administrador">Administrador</option>
                        <option value="consultor">Consultor</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="isSubmitting">
                    Guardar Cambios
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 640px) {
    .form-grid-2 {
        grid-template-columns: 1fr 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}

.form-control-select {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}

.form-control-select:focus {
    border-color: var(--blue, #3b82f6);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
