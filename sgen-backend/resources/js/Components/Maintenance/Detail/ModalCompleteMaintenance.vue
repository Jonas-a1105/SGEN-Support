<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput, BaseTextarea } from '@/Components/UI';
import type { MaintenanceDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    maintenance: MaintenanceDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const isPeriodic = computed(() => props.maintenance.frecuencia && props.maintenance.frecuencia !== 'unica');

const recurrenceDescription = computed(() => {
    switch (props.maintenance.frecuencia) {
        case 'mensual': return 'en 1 mes';
        case 'trimestral': return 'en 3 meses';
        case 'semestral': return 'en 6 meses';
        case 'anual': return 'en 1 año';
        default: return 'automáticamente';
    }
});

const completeForm = ref({
    costo: props.maintenance.costo || 0,
    observaciones: props.maintenance.observaciones || '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            completeForm.value = {
                costo: props.maintenance.costo || 0,
                observaciones: props.maintenance.observaciones || '',
            };
        }
    }
);

const handleComplete = () => {
    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenance.id}/completar`,
        {
            costo: completeForm.value.costo,
            observaciones: completeForm.value.observaciones,
        },
        {
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
        title="Marcar Mantenimiento como Realizado"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleComplete" class="modal-form-stack">
            <div class="modal-intro-text">
                El mantenimiento <strong>#{{ maintenance.id }}</strong> se registrará como concluido y pasará al estado completado.
            </div>

            <div v-if="isPeriodic" class="recurrence-notice">
                <svg viewBox="0 0 24 24" class="notice-icon" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                <div>
                    <strong>Recurrencia ({{ maintenance.frecuencia.toUpperCase() }}):</strong>
                    <span> El CMMS programará automáticamente la próxima cita {{ recurrenceDescription }}.</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Costo Final del Servicio ($)</label>
                <BaseInput
                    v-model.number="completeForm.costo"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                />
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones y Dictamen Técnico</label>
                <BaseTextarea
                    v-model="completeForm.observaciones"
                    placeholder="Detalles de las piezas cambiadas, pruebas realizadas o dictamen final..."
                    rows="4"
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
                    Confirmar y Completar
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

.modal-intro-text {
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
    padding: 12px;
    background: var(--stroke-subtle);
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
}

.recurrence-notice {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    background: rgba(79, 70, 229, 0.08);
    border: 1px solid rgba(79, 70, 229, 0.2);
    border-radius: 8px;
    font-size: 12px;
    color: var(--text);
    line-height: 1.45;
}

.notice-icon {
    width: 18px;
    height: 18px;
    color: var(--primary);
    flex-shrink: 0;
    margin-top: 1px;
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

.modal-actions-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
