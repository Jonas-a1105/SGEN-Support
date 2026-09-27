<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseInput from '@/Components/UI/BaseInput.vue';
import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
import BaseCombobox from '@/Components/UI/BaseCombobox.vue';

/**
 * Baja patrimonial de un activo (Módulo 11): flujo irreversible, motivo
 * legal obligatorio, valor de recuperación, destino y consentimiento
 * explícito — la evidencia permanece (historia custodial y tickets a salvo).
 */
interface Props {
    isOpen: boolean;
    equipmentId: number;
    equipmentCode: string;
    version?: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = reactive({
    motivo: 'obsolescencia' as string,
    valor_recuperacion: '' as string | number,
    destino: '',
    nota: '',
    confirmar_irreversible: false,
    submitting: false,
});

const motiveOptions = [
    { value: 'obsolescencia', label: 'Obsolescencia (fin de vida útil)' },
    { value: 'robo', label: 'Robo / Extravío' },
    { value: 'donacion', label: 'Donación' },
    { value: 'venta', label: 'Venta' },
    { value: 'desecho', label: 'Desecho / reciclaje' },
];

const submit = (): void => {
    if (form.submitting || !form.confirmar_irreversible) {
        return;
    }

    form.submitting = true;

    router.post(
        `/equipos/${props.equipmentId}/baja`,
        {
            motivo: form.motivo,
            valor_recuperacion: form.valor_recuperacion === '' ? null : form.valor_recuperacion,
            destino: form.destino || null,
            nota: form.nota || null,
            confirmar_irreversible: form.confirmar_irreversible,
        },
        {
            preserveScroll: false,
            onFinish: () => {
                form.submitting = false;
            },
            onSuccess: () => {
                emit('close');
                Object.assign(form, { motivo: 'obsolescencia', valor_recuperacion: '', destino: '', nota: '', confirmar_irreversible: false });
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        :title="`Bajar activo ${equipmentCode} (patrimonial, irreversible)`"
        max-width="md"
        @close="emit('close')"
    >
        <form class="form-stack" @submit.prevent="submit">
            <p class="aviso">
                La baja patrimonial retira el activo del ciclo operativo y emite su acta formal.
                <strong>No se elimina la historia</strong> (tickets, mantenimientos, custodias) — queda como evidencia.
            </p>

            <BaseCombobox
                v-model="form.motivo"
                label="Motivo legal de la baja"
                :options="motiveOptions"
                :searchable="false"
                required
            />

            <div class="grid-2">
                <BaseInput
                    v-model="form.valor_recuperacion"
                    label="Valor de recuperación ($)"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                />
                <BaseInput
                    v-model="form.destino"
                    label="Destino"
                    placeholder="Almacén general / cliente beneficiario…"
                />
            </div>

            <BaseTextarea
                v-model="form.nota"
                label="Nota técnica (opcional)"
                placeholder="Acuerdo de junta, servicio sanitario previo, informe de falla grave…"
            />

            <label class="confirm-check">
                <input type="checkbox" v-model="form.confirmar_irreversible" required>
                <span>Confirmo que esta baja es irreversible y emite el acta formal con hash.</span>
            </label>

            <div class="modal-actions">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">Cancelar</BaseButton>
                <BaseButton
                    type="submit"
                    variant="danger"
                    size="md"
                    :disabled="!form.confirmar_irreversible || form.submitting"
                >
                    Registrar baja patrimonial
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4, 16px);
}
.aviso {
    margin: 0;
    padding: 12px 14px;
    background: rgba(245, 158, 11, 0.08);
    border: var(--stroke-w, 2px) solid rgba(245, 158, 11, 0.3);
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.45;
    color: var(--text, #f4f4f6);
}
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3, 12px);
}
.confirm-check {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    font-size: 12.5px;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    line-height: 1.4;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3, 12px);
    padding-top: var(--space-2, 8px);
}
@media (max-width: 640px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>
