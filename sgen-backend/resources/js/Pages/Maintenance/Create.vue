<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader, BaseButton } from '@/Components/UI';
import type { Equipment, Technician, FormOption, ChecklistTask } from '@/Components/Maintenance/Create/types';
import AssetSelectionCard from '@/Components/Maintenance/Create/AssetSelectionCard.vue';
import WorkDetailCard from '@/Components/Maintenance/Create/WorkDetailCard.vue';
import PlanningCard from '@/Components/Maintenance/Create/PlanningCard.vue';
import ResourcesCard from '@/Components/Maintenance/Create/ResourcesCard.vue';

const props = defineProps<{
    equipments?: Equipment[];
    technicians?: Technician[];
    types?: FormOption[];
    frequencies?: FormOption[];
}>();

const checklistTasks = ref<ChecklistTask[]>([]);
const submitting = ref(false);
const errorMessage = ref('');

const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
const initialEquipoId = urlParams?.get('equipo_id') || '';

const form = ref({
    equipo_id: initialEquipoId,
    fecha: '',
    tipo_mantenimiento: 'preventivo',
    estado: 'pendiente',
    descripcion: '',
    frecuencia: 'unica',
    proxima_fecha: '',
    costo: '0.00',
    tecnico_id: '',
    observaciones: '',
    duracion: 60,
});

const handleSubmit = () => {
    errorMessage.value = '';

    if (!form.value.equipo_id) {
        errorMessage.value = 'Por favor seleccione un equipo.';
        return;
    }
    if (!form.value.fecha) {
        errorMessage.value = 'Por favor indique la fecha y hora programada.';
        return;
    }
    if (!form.value.descripcion.trim()) {
        errorMessage.value = 'Por favor ingrese la descripción general.';
        return;
    }

    submitting.value = true;

    let obs = form.value.observaciones ? form.value.observaciones.trim() : '';
    if (checklistTasks.value.length > 0) {
        const checklistSummary = checklistTasks.value
            .map((t) => `[${t.done ? 'X' : ' '}] ${t.text}`)
            .join('; ');
        obs = obs ? `${obs} | Checklist: ${checklistSummary}` : `Checklist: ${checklistSummary}`;
    }

    const payload = {
        equipo_id: parseInt(form.value.equipo_id),
        fecha: form.value.fecha,
        tipo_mantenimiento: form.value.tipo_mantenimiento,
        descripcion: form.value.descripcion.trim(),
        frecuencia: form.value.frecuencia || 'unica',
        proxima_fecha: form.value.proxima_fecha || null,
        costo: form.value.costo ? parseFloat(form.value.costo) : null,
        tecnico_id: form.value.tecnico_id ? parseInt(form.value.tecnico_id) : null,
        observaciones: obs ? obs.slice(0, 500) : null,
        duracion: form.value.duracion ? Number(form.value.duracion) : null,
    };

    router.post('/mantenimientos', payload, {
        onSuccess: () => {
            submitting.value = false;
        },
        onError: () => {
            submitting.value = false;
        },
    });
};

const handleCancel = () => {
    router.visit('/mantenimientos');
};
</script>

<template>
    <AppLayout title="Programar Mantenimiento">
        <Head title="Programar Mantenimiento" />

        <div class="view-wrapper schedule-form-view">
            <!-- BREADCRUMBS -->
            <nav class="breadcrumb-nav" aria-label="Ruta de navegación">
                <a class="breadcrumb-link" @click.prevent="handleCancel">Mantenimientos</a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-current">Programar</span>
            </nav>

            <!-- ENCABEZADO DEL FORMULARIO CON BASEPAGEHEADER -->
            <BasePageHeader
                title="Programar Mantenimiento"
                subtitle="Complete los detalles técnicos y logísticos para la orden de trabajo."
            >
                <template #actions>
                    <BaseButton variant="subtle" size="md" type="button" @click="handleCancel">
                        <span>← Cancelar</span>
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        size="md"
                        type="button"
                        :disabled="submitting"
                        :loading="submitting"
                        @click="handleSubmit"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="save-icon">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>{{ submitting ? 'Guardando...' : 'Guardar Orden' }}</span>
                    </BaseButton>
                </template>
            </BasePageHeader>

            <!-- MENSAJE DE VALIDACIÓN -->
            <div v-if="errorMessage" class="maintenance-error-banner" role="alert">
                <svg viewBox="0 0 24 24" class="error-banner-icon">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ errorMessage }}</span>
            </div>

            <!-- CUADRÍCULA DEL FORMULARIO A 2 COLUMNAS -->
            <form class="schedule-grid-layout" @submit.prevent="handleSubmit">
                <!-- COLUMNA IZQUIERDA: ACTIVO Y DETALLE DEL TRABAJO -->
                <div class="form-col">
                    <AssetSelectionCard
                        v-model="form.equipo_id"
                        :equipments="equipments"
                    />

                    <WorkDetailCard
                        v-model:tipo-mantenimiento="form.tipo_mantenimiento"
                        v-model:estado="form.estado"
                        v-model:descripcion="form.descripcion"
                        v-model:observaciones="form.observaciones"
                        v-model:checklist="checklistTasks"
                    />
                </div>

                <!-- COLUMNA DERECHA: PLANIFICACIÓN Y RECURSOS -->
                <div class="form-col">
                    <PlanningCard
                        v-model:fecha="form.fecha"
                        v-model:duracion="form.duracion"
                        v-model:frecuencia="form.frecuencia"
                        v-model:proxima-fecha="form.proxima_fecha"
                    />

                    <ResourcesCard
                        v-model:tecnico-id="form.tecnico_id"
                        v-model:costo="form.costo"
                        :technicians="technicians"
                    />
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.view-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-bottom: 32px;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-muted);
}

.breadcrumb-link {
    color: var(--text-muted);
    cursor: pointer;
    text-decoration: none;
    border-bottom: 1.5px solid transparent;
    transition: color 0.2s ease, border-color 0.2s ease;
}

.breadcrumb-link:hover {
    color: var(--orange);
    border-color: var(--orange);
}

.breadcrumb-separator {
    color: var(--text-dim);
}

.breadcrumb-current {
    color: var(--text);
    font-weight: 600;
}

.save-icon {
    width: 16px;
    height: 16px;
}

.schedule-grid-layout {
    display: grid;
    grid-template-columns: 1.45fr 0.85fr;
    gap: 20px;
    align-items: start;
}

.form-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.maintenance-error-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: var(--panel-radius, 10px);
    color: var(--color-red, #ef4444);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 20px;
}

.error-banner-icon {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

@media (max-width: 980px) {
    .schedule-grid-layout {
        grid-template-columns: 1fr;
    }
}
</style>
