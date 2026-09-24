<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Equipment, Department, Employee } from '@/Types/inventory';
import StepperIndicator from './Forms/StepperIndicator.vue';
import ImageDropzone from './Common/ImageDropzone.vue';
import WizardStepBasic from './Forms/WizardStepBasic.vue';
import WizardStepSpecs from './Forms/WizardStepSpecs.vue';
import WizardStepLocation from './Forms/WizardStepLocation.vue';
import WizardStepAcquisition from './Forms/WizardStepAcquisition.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = defineProps<{
    equipment: Equipment | null;
    departamentos: Department[];
    empleados: Employee[];
}>();

const emit = defineEmits<{ (e: 'back'): void; (e: 'saved'): void }>();

const wizardStep = ref(1);

const form = useForm({
    id: props.equipment?.id || null,
    codigo_inventario: props.equipment?.codigo_inventario || '',
    numero_serie: props.equipment?.numero_serie || '',
    tipo: props.equipment?.tipo_equipo || props.equipment?.tipo || 'Computadora',
    estado: props.equipment?.estado || 'Disponible',
    marca: props.equipment?.marca || '',
    modelo: props.equipment?.modelo || '',
    procesador: props.equipment?.procesador || '',
    memoria_ram: props.equipment?.ram || '',
    almacenamiento: props.equipment?.almacenamiento || '',
    sistema_operativo: props.equipment?.sistema_operativo || '',
    departamento_id: props.equipment?.departamento_id || null,
    empleado_id: props.equipment?.empleado_id || null,
    valor_compra: props.equipment?.precio_compra ? String(props.equipment.precio_compra) : '',
    proveedor: props.equipment?.proveedor || '',
});

watch(
    () => props.equipment,
    (newVal) => {
        if (newVal) {
            form.id = newVal.id;
            form.codigo_inventario = newVal.codigo_inventario || '';
            form.numero_serie = newVal.numero_serie || '';
            form.tipo = newVal.tipo_equipo || newVal.tipo || 'Computadora';
            form.estado = newVal.estado || 'Disponible';
            form.marca = newVal.marca || '';
            form.modelo = newVal.modelo || '';
            form.procesador = newVal.procesador || '';
            form.memoria_ram = newVal.ram || '';
            form.almacenamiento = newVal.almacenamiento || '';
            form.sistema_operativo = newVal.sistema_operativo || '';
            form.departamento_id = newVal.departamento_id || null;
            form.empleado_id = newVal.empleado_id || null;
            form.valor_compra = newVal.precio_compra ? String(newVal.precio_compra) : '';
            form.proveedor = newVal.proveedor || '';
        }
    },
    { immediate: true }
);

const nextStep = () => {
    if (wizardStep.value < 4) {
        wizardStep.value++;
    } else {
        submit();
    }
};

const prevStep = () => {
    if (wizardStep.value > 1) {
        wizardStep.value--;
    }
};

const submit = () => {
    form.post('/equipos', {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('back');
        },
    });
};

const stepHeaders = [
    'Información Básica',
    'Especificaciones',
    'Ubicación',
    'Adquisición',
];
</script>

<template>
    <section class="inventory-wrapper detail-view-container" id="viewEditEquipmentWizard">
        <BaseCard padding="lg">
            <div class="wizard-grid-layout">
                <!-- LATERAL DEL ASISTENTE -->
                <div class="wizard-sidebar-stack">
                    <div>
                        <button
                            class="back-link-btn wizard-back-btn"
                            id="btnBackFromWizard"
                            type="button"
                            @click="emit('back')"
                        >
                            <span>← Volver</span>
                        </button>
                        <div class="wizard-sidebar-hero">
                            <div class="wizard-hero-icon-box">
                                <svg viewBox="0 0 24 24">
                                    <rect x="2" y="3" width="20" height="14" rx="2" />
                                    <line x1="8" y1="21" x2="16" y2="21" />
                                    <line x1="12" y1="17" x2="12" y2="21" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="wizard-hero-title" id="wizardTitle">
                                    {{ equipment?.id ? 'Editar Equipo' : 'Registrar Equipo' }}
                                </h2>
                                <span class="wizard-hero-subtitle">Complete los datos del activo.</span>
                            </div>
                        </div>
                    </div>

                    <StepperIndicator
                        :current-step="wizardStep"
                        @select-step="wizardStep = $event"
                    />

                    <ImageDropzone
                        title="Subir foto"
                        compact
                    />
                </div>

                <!-- CONTENIDO DERECHO DEL ASISTENTE -->
                <div class="wizard-body-panel">
                    <div>
                        <div class="wizard-body-header">
                            <h3 class="panel-title" id="wizardStepHeader">
                                {{ stepHeaders[wizardStep - 1] }}
                            </h3>
                            <BaseBadge variant="code" id="wizardStepIndicator">
                                Paso {{ wizardStep }} de 4
                            </BaseBadge>
                        </div>

                        <WizardStepBasic
                            v-show="wizardStep === 1"
                            :codigo-inventario="form.codigo_inventario"
                            :numero-serie="form.numero_serie"
                            :tipo="form.tipo"
                            :estado="form.estado"
                            :marca="form.marca"
                            :modelo="form.modelo"
                            @update:codigo-inventario="form.codigo_inventario = $event"
                            @update:numero-serie="form.numero_serie = $event"
                            @update:tipo="form.tipo = $event"
                            @update:estado="form.estado = $event"
                            @update:marca="form.marca = $event"
                            @update:modelo="form.modelo = $event"
                        />

                        <WizardStepSpecs
                            v-show="wizardStep === 2"
                            :procesador="form.procesador"
                            :memoria-ram="form.memoria_ram"
                            :almacenamiento="form.almacenamiento"
                            :sistema-operativo="form.sistema_operativo"
                            @update:procesador="form.procesador = $event"
                            @update:memoria-ram="form.memoria_ram = $event"
                            @update:almacenamiento="form.almacenamiento = $event"
                            @update:sistema-operativo="form.sistema_operativo = $event"
                        />

                        <WizardStepLocation
                            v-show="wizardStep === 3"
                            :departamento-id="form.departamento_id"
                            :empleado-id="form.empleado_id"
                            :departamentos="departamentos"
                            :empleados="empleados"
                            @update:departamento-id="form.departamento_id = $event"
                            @update:empleado-id="form.empleado_id = $event"
                        />

                        <WizardStepAcquisition
                            v-show="wizardStep === 4"
                            :valor-compra="form.valor_compra"
                            :proveedor="form.proveedor"
                            @update:valor-compra="form.valor_compra = $event"
                            @update:proveedor="form.proveedor = $event"
                        />
                    </div>

                    <!-- BOTONES DEL ASISTENTE -->
                    <div class="wizard-footer-nav">
                        <BaseButton
                            variant="secondary"
                            id="btnWizardPrev"
                            :disabled="wizardStep <= 1"
                            type="button"
                            @click="prevStep"
                        >
                            Atrás
                        </BaseButton>
                        <BaseButton
                            variant="primary"
                            id="btnWizardNext"
                            type="button"
                            :disabled="form.processing"
                            :loading="form.processing"
                            @click="nextStep"
                        >
                            {{ wizardStep === 4 ? (form.processing ? 'Guardando...' : 'Guardar') : 'Siguiente ›' }}
                        </BaseButton>
                    </div>
                </div>
            </div>
        </BaseCard>
    </section>
</template>

<style scoped>
.wizard-grid-layout {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: var(--space-6);
    min-height: 480px;
}

.wizard-sidebar-stack {
    border-right: var(--stroke-w) solid var(--stroke-subtle);
    padding-right: var(--space-5);
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.wizard-back-btn {
    margin-bottom: var(--space-3) !important;
}

.wizard-sidebar-hero {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-4);
}

.wizard-hero-icon-box {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.wizard-hero-icon-box svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.wizard-hero-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    line-height: 1.2;
}

.wizard-hero-subtitle {
    font-size: 11px;
    color: var(--text-dim);
}

.wizard-body-panel {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.wizard-body-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: var(--space-3);
    margin-bottom: var(--space-4);
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.wizard-footer-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--space-6);
    padding-top: var(--space-4);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

@media (max-width: 1080px) {
    .wizard-grid-layout {
        grid-template-columns: 1fr;
    }
    .wizard-sidebar-stack {
        border-right: none;
        border-bottom: var(--stroke-w) solid var(--stroke-subtle);
        padding-right: 0;
        padding-bottom: var(--space-5);
    }
}
</style>
