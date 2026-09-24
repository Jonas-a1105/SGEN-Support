<script setup lang="ts">
import { reactive, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';
import type { SupportFormOptions } from '@/types/support';

interface Props {
    isOpen: boolean;
    options: SupportFormOptions;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const form = reactive({
    titulo: '',
    solicitante: '',
    departamento: '',
    categoria_id: 1,
    prioridad: 'media',
    empleado_id: 38,
    equipo_id: 238,
    estado: 'pendiente',
    isSubmitting: false,
});

const categoryOptions = computed<ComboboxOption[]>(() =>
    (props.options.categories || []).map((cat) => ({
        value: cat.id,
        label: cat.name,
    }))
);

const priorityOptions: ComboboxOption[] = [
    { value: 'baja', label: 'Baja' },
    { value: 'media', label: 'Media' },
    { value: 'alta', label: 'Alta' },
    { value: 'critica', label: 'Crítica' },
];

const techOptions = computed<ComboboxOption[]>(() =>
    (props.options.technicians || []).map((tech) => ({
        value: tech.id,
        label: tech.name,
        sublabel: tech.specialty,
    }))
);

const statusOptions: ComboboxOption[] = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'en_proceso', label: 'En Proceso' },
    { value: 'resuelto', label: 'Resuelto / Entregado' },
];

const equipmentOptions = computed<ComboboxOption[]>(() =>
    (props.options.equipments || []).map((eq) => ({
        value: eq.id,
        label: `${eq.type} - ${eq.code || eq.serial}`,
        sublabel: `${eq.model} (${eq.department})`,
    }))
);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            form.titulo = '';
            form.solicitante = '';
            form.departamento = '';
            form.categoria_id = props.options.categories?.[0]?.id ?? 1;
            form.prioridad = 'media';
            form.empleado_id = props.options.technicians?.[0]?.id ?? 38;
            form.equipo_id = props.options.equipments?.[0]?.id ?? 238;
            form.estado = 'pendiente';
            form.isSubmitting = false;
        }
    }
);

const handleSubmit = () => {
    if (!form.titulo.trim() || form.isSubmitting) return;

    form.isSubmitting = true;
    router.post(
        '/soportes',
        {
            titulo: form.titulo,
            solicitante: form.solicitante,
            departamento: form.departamento,
            categoria_id: form.categoria_id,
            prioridad: form.prioridad,
            empleado_id: form.empleado_id,
            equipo_id: form.equipo_id,
            estado: form.estado,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.isSubmitting = false;
                emit('close');
            },
            onError: () => {
                form.isSubmitting = false;
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Registrar Nuevo Ticket" max-width="lg" @close="emit('close')">
        <form class="ticket-modal-form" @submit.prevent="handleSubmit">
            <div class="form-group">
                <label class="form-label" for="ticketTitleInput">Título del Problema / Asunto</label>
                <input
                    id="ticketTitleInput"
                    v-model="form.titulo"
                    type="text"
                    class="form-input"
                    placeholder="Ej: SOPORTE EN RED LOCAL Y SERVIDORES"
                    required
                />
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="ticketRequesterInput">Solicitante</label>
                    <input
                        id="ticketRequesterInput"
                        v-model="form.solicitante"
                        type="text"
                        class="form-input"
                        placeholder="Ej: Herdil Nair Gutierrez"
                    />
                </div>
                <div class="form-group">
                    <label class="form-label" for="ticketDeptInput">Departamento</label>
                    <input
                        id="ticketDeptInput"
                        v-model="form.departamento"
                        type="text"
                        class="form-input"
                        placeholder="Ej: Dpto. de Recursos Humanos"
                    />
                </div>
            </div>

            <div class="form-row">
                <BaseCombobox
                    :model-value="form.categoria_id"
                    label="Categoría"
                    :options="categoryOptions"
                    :searchable="true"
                    @update:model-value="(val) => { if (val != null) form.categoria_id = Number(val); }"
                />
                <BaseCombobox
                    :model-value="form.prioridad"
                    label="Prioridad"
                    :options="priorityOptions"
                    :searchable="false"
                    @update:model-value="(val) => { if (val != null) form.prioridad = String(val); }"
                />
            </div>

            <div class="form-row">
                <BaseCombobox
                    :model-value="form.empleado_id"
                    label="Técnico Asignado"
                    :options="techOptions"
                    :searchable="true"
                    @update:model-value="(val) => { if (val != null) form.empleado_id = Number(val); }"
                />
                <BaseCombobox
                    :model-value="form.estado"
                    label="Estado Inicial"
                    :options="statusOptions"
                    :searchable="false"
                    @update:model-value="(val) => { if (val != null) form.estado = String(val); }"
                />
            </div>

            <BaseCombobox
                :model-value="form.equipo_id"
                label="Equipo / Activo Afectado"
                :options="equipmentOptions"
                :searchable="true"
                @update:model-value="(val) => { if (val != null) form.equipo_id = Number(val); }"
            />

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton variant="primary" type="submit" :disabled="form.isSubmitting">
                    {{ form.isSubmitting ? 'Guardando...' : 'Guardar Ticket' }}
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.ticket-modal-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.form-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.form-input {
    width: 100%;
    height: 40px;
    padding: 8px 12px;
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-sm, 6px);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}
.form-input:focus {
    border-color: var(--primary);
}
.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
    padding-top: 16px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
