<script setup lang="ts">
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
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
                <div class="form-group">
                    <label class="form-label" for="ticketCategorySelect">Categoría</label>
                    <select id="ticketCategorySelect" v-model="form.categoria_id" class="form-select">
                        <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ticketPrioritySelect">Prioridad</label>
                    <select id="ticketPrioritySelect" v-model="form.prioridad" class="form-select">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="ticketTechSelect">Técnico Asignado</label>
                    <select id="ticketTechSelect" v-model="form.empleado_id" class="form-select">
                        <option v-for="tech in options.technicians" :key="tech.id" :value="tech.id">
                            {{ tech.name }} ({{ tech.specialty }})
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ticketStatusSelect">Estado Inicial</label>
                    <select id="ticketStatusSelect" v-model="form.estado" class="form-select">
                        <option value="pendiente">Pendiente (Ámbar)</option>
                        <option value="en_proceso">En Proceso (Azul)</option>
                        <option value="resuelto">Resuelto / Entregado (Verde)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="ticketEquipSelect">Equipo / Activo Afectado</label>
                <select id="ticketEquipSelect" v-model="form.equipo_id" class="form-select">
                    <option v-for="eq in options.equipments" :key="eq.id" :value="eq.id">
                        {{ eq.type }} - {{ eq.code || eq.serial }} ({{ eq.model }}) - {{ eq.department }}
                    </option>
                </select>
            </div>

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
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.form-input,
.form-select {
    width: 100%;
    height: 40px;
    padding: 8px 12px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
}
.form-input:focus,
.form-select:focus {
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
