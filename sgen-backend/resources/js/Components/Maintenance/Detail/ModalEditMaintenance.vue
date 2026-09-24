<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput, BaseTextarea } from '@/Components/UI';
import type { FormOptions, MaintenanceDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    maintenance: MaintenanceDetail;
    options?: FormOptions;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const editForm = ref({
    fecha: props.maintenance.fecha ? props.maintenance.fecha.substring(0, 16) : '',
    tipo_mantenimiento: props.maintenance.tipoMantenimiento,
    estado: props.maintenance.estado,
    descripcion: props.maintenance.descripcion,
    frecuencia: props.maintenance.frecuencia,
    proxima_fecha: props.maintenance.proximaFecha || '',
    costo: props.maintenance.costo || 0,
    tecnico_id: props.maintenance.tecnicoId || '',
    observaciones: props.maintenance.observaciones || '',
    duracion: props.maintenance.duracion || 60,
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            editForm.value = {
                fecha: props.maintenance.fecha ? props.maintenance.fecha.substring(0, 16) : '',
                tipo_mantenimiento: props.maintenance.tipoMantenimiento,
                estado: props.maintenance.estado,
                descripcion: props.maintenance.descripcion,
                frecuencia: props.maintenance.frecuencia,
                proxima_fecha: props.maintenance.proximaFecha || '',
                costo: props.maintenance.costo || 0,
                tecnico_id: props.maintenance.tecnicoId || '',
                observaciones: props.maintenance.observaciones || '',
                duracion: props.maintenance.duracion || 60,
            };
        }
    }
);

const handleSaveEdit = () => {
    isSubmitting.value = true;
    router.put(
        `/mantenimientos/${props.maintenance.id}`,
        {
            fecha: editForm.value.fecha,
            tipo_mantenimiento: editForm.value.tipo_mantenimiento,
            estado: editForm.value.estado,
            descripcion: editForm.value.descripcion,
            frecuencia: editForm.value.frecuencia,
            proxima_fecha: editForm.value.proxima_fecha || null,
            costo: editForm.value.costo,
            tecnico_id: editForm.value.tecnico_id || null,
            observaciones: editForm.value.observaciones,
            duracion: editForm.value.duracion,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
                emit('close');
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Editar Ficha de Mantenimiento"
        max-width="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSaveEdit" class="modal-form-stack">
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Fecha y Hora Programada *</label>
                    <BaseInput
                        v-model="editForm.fecha"
                        type="datetime-local"
                        required
                    />
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Mantenimiento *</label>
                    <select v-model="editForm.tipo_mantenimiento" class="custom-select" required>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                        <option value="predictivo">Predictivo</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Estado de la Orden *</label>
                    <select v-model="editForm.estado" class="custom-select" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="pospuesto">Pospuesto</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Frecuencia Planificada</label>
                    <select v-model="editForm.frecuencia" class="custom-select">
                        <option value="unica">Única vez</option>
                        <option value="mensual">Mensual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="semestral">Semestral</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Técnico Asignado</label>
                    <select v-model="editForm.tecnico_id" class="custom-select">
                        <option value="">Sin asignar</option>
                        <option
                            v-for="t in options?.technicians"
                            :key="t.id"
                            :value="t.id"
                        >
                            {{ t.name }}
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Duración Estimada (min)</label>
                    <BaseInput
                        v-model.number="editForm.duracion"
                        type="number"
                        min="5"
                        placeholder="60"
                    />
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Costo Estimado ($)</label>
                    <BaseInput
                        v-model.number="editForm.costo"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    />
                </div>
                <div class="form-group">
                    <label class="form-label">Próxima Fecha de Recurrencia</label>
                    <BaseInput
                        v-model="editForm.proxima_fecha"
                        type="date"
                    />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Descripción del Trabajo *</label>
                <BaseTextarea
                    v-model="editForm.descripcion"
                    rows="3"
                    required
                />
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones Adicionales</label>
                <BaseTextarea
                    v-model="editForm.observaciones"
                    rows="2"
                />
            </div>

            <div class="modal-actions-footer">
                <BaseButton
                    variant="subtle"
                    size="md"
                    type="button"
                    @click="emit('close')"
                >
                    Cancelar
                </BaseButton>
                <BaseButton
                    variant="primary"
                    size="md"
                    type="submit"
                    :disabled="isSubmitting"
                >
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

.custom-select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}

.custom-select:focus {
    border-color: var(--blue, #3b82f6);
}

.modal-actions-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
