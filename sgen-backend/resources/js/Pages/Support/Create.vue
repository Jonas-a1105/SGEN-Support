<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeviceSearchCard from '@/Components/Support/Create/DeviceSearchCard.vue';
import DeviceSelectedCard from '@/Components/Support/Create/DeviceSelectedCard.vue';
import DeviceEmptyState from '@/Components/Support/Create/DeviceEmptyState.vue';
import CategorySelector from '@/Components/Support/Create/CategorySelector.vue';
import PrioritySelector from '@/Components/Support/Create/PrioritySelector.vue';
import TicketIncidentFields from '@/Components/Support/Create/TicketIncidentFields.vue';
import TicketAssignmentFields from '@/Components/Support/Create/TicketAssignmentFields.vue';
import ModalSelectEquipment from '@/Components/Support/Create/ModalSelectEquipment.vue';
import { useTicketEquipmentSearch } from '@/Composables/useTicketEquipmentSearch';
import type { FormEquipment, FormCategory, FormTechnician, TicketPriority } from '@/Components/Support/Create/types';

interface Props {
    options: {
        technicians?: FormTechnician[];
        equipments?: FormEquipment[];
        categories?: FormCategory[];
    };
}

const props = defineProps<Props>();

// Form state
const selectedCategoryId = ref<number>(props.options.categories?.[0]?.id ?? 1);
const selectedPriority = ref<TicketPriority>('media');
const titulo = ref('');
const descripcion = ref('');
const solicitante = ref('');
const departamento = ref('');
const selectedTechnicianId = ref<number | null>(props.options.technicians?.[0]?.id ?? null);
const isSubmitting = ref(false);
const errorMessage = ref('');

// Device search via composable (SRP)
const equipmentsRef = computed(() => props.options.equipments);
const {
    deviceSearch,
    selectedEquipmentId,
    isDeviceModalOpen,
    selectedEquipment,
    searchResults,
    selectEquipment,
    clearEquipment,
    handleSearchSubmit,
} = useTicketEquipmentSearch(equipmentsRef, (eq: FormEquipment) => {
    if (eq.department && eq.department !== 'Sin departamento') {
        departamento.value = eq.department;
    }
    if (eq.assigned_to && eq.assigned_to !== 'Sin asignar') {
        solicitante.value = eq.assigned_to;
    }
});

// Preseleccionar equipo si viene por parámetro en la URL
const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
const queryEquipoId = urlParams?.get('equipo_id') ? Number(urlParams.get('equipo_id')) : null;
if (queryEquipoId && props.options.equipments) {
    const matched = props.options.equipments.find((e) => e.id === queryEquipoId);
    if (matched) {
        selectEquipment(matched);
    }
}

function handleSubmit() {
    if (!titulo.value.trim()) {
        errorMessage.value = 'El título del problema es obligatorio.';
        return;
    }

    if (descripcion.value.trim().length < 10) {
        errorMessage.value = 'La descripción debe tener al menos 10 caracteres.';
        return;
    }

    if (!selectedEquipmentId.value) {
        errorMessage.value = 'Debe seleccionar un equipo afectado.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    router.post(
        '/soportes',
        {
            titulo: titulo.value.trim(),
            descripcion: descripcion.value.trim(),
            equipo_id: selectedEquipmentId.value,
            categoria_id: selectedCategoryId.value,
            prioridad: selectedPriority.value,
            empleado_id: selectedTechnicianId.value || undefined,
            solicitante: solicitante.value.trim() || undefined,
            departamento: departamento.value.trim() || undefined,
            estado: 'pendiente',
        },
        {
            onError: (errors) => {
                isSubmitting.value = false;
                const first = Object.values(errors)[0];
                errorMessage.value = typeof first === 'string' ? first : 'Error al registrar el ticket.';
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
}
</script>

<template>
    <AppLayout>
        <Head title="Nuevo Ticket de Soporte" />

        <div class="tf-container">
            <div class="tf-max-w">
                <!-- Header -->
                <div class="module-header">
                    <div class="module-title-wrap">
                        <Link href="/soportes" class="btn-back" title="Volver a lista de soportes">
                            <span>←</span>
                            <span>Volver a Soportes</span>
                        </Link>
                        <div>
                            <h1 class="module-title">Nuevo Ticket de Soporte</h1>
                            <p class="module-subtitle">Reporta una incidencia técnica para su resolución y seguimiento.</p>
                        </div>
                    </div>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="tf-alert-error" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                    <span>{{ errorMessage }}</span>
                </div>

                <form @submit.prevent="handleSubmit">
                    <div class="tf-main-container">
                        <div class="tf-grid">
                            <!-- LEFT COLUMN: DEVICE CONTEXT -->
                            <div class="tf-left-col">
                                <DeviceSearchCard
                                    v-model="deviceSearch"
                                    :search-results="searchResults"
                                    @submit="handleSearchSubmit"
                                    @select="selectEquipment"
                                />

                                <DeviceSelectedCard
                                    v-if="selectedEquipment"
                                    :equipment="selectedEquipment"
                                    @clear="clearEquipment"
                                />

                                <DeviceEmptyState v-else />
                            </div>

                            <!-- RIGHT COLUMN: THE FORM -->
                            <div class="tf-form-card">
                                <!-- Section 1: Categories & Priority -->
                                <div class="tf-section">
                                    <div class="tf-section-header">
                                        <span class="tf-section-number">1</span>
                                        <h3 class="tf-section-title">Detalles del Incidente</h3>
                                    </div>

                                    <div class="tf-two-col">
                                        <CategorySelector
                                            v-model="selectedCategoryId"
                                            :categories="options.categories"
                                        />

                                        <PrioritySelector
                                            v-model="selectedPriority"
                                        />
                                    </div>
                                </div>

                                <div class="tf-divider"></div>

                                <!-- Section 2: Title and Description -->
                                <TicketIncidentFields
                                    v-model:title="titulo"
                                    v-model:description="descripcion"
                                />

                                <div class="tf-divider"></div>

                                <!-- Section 3: Assignment & Requester -->
                                <TicketAssignmentFields
                                    v-model:requester="solicitante"
                                    v-model:department="departamento"
                                    v-model:selected-tech-id="selectedTechnicianId"
                                    :technicians="options.technicians"
                                />

                                <!-- Actions -->
                                <div class="form-actions-row">
                                    <Link href="/soportes" class="btn-cancel">
                                        Cancelar
                                    </Link>
                                    <button type="submit" class="btn-submit" :disabled="isSubmitting">
                                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                                        <span>{{ isSubmitting ? 'Guardando...' : 'Guardar Ticket' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL MULTI-EQUIPOS -->
        <ModalSelectEquipment
            :is-open="isDeviceModalOpen"
            :equipments="searchResults"
            @close="isDeviceModalOpen = false"
            @select="selectEquipment"
        />
    </AppLayout>
</template>

<style scoped>
.tf-container {
    width: 100%;
    margin: 0 auto;
    padding: 0 0 32px 0;
}

.tf-max-w {
    width: 100%;
}

.tf-alert-error {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 12px;
    background: rgba(239, 68, 68, 0.12);
    border: var(--stroke-w, 2px) solid rgba(239, 68, 68, 0.35);
    color: #ef4444;
    font-size: 13px;
    box-shadow: none !important;
}

.tf-alert-error svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.tf-main-container {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 20px;
    padding: 24px;
    box-shadow: none !important;
}

.tf-grid {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 24px;
    align-items: start;
}

@media (max-width: 960px) {
    .tf-grid {
        grid-template-columns: 1fr;
    }
}

.tf-left-col {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.tf-form-card {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tf-section {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.tf-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tf-section-number {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: var(--orange, #2563eb);
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 11px;
    font-weight: 700;
}

.tf-section-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}

.tf-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

@media (max-width: 720px) {
    .tf-two-col {
        grid-template-columns: 1fr;
    }
}

.tf-divider {
    height: 1px;
    background: var(--stroke-subtle, #23252a);
}
</style>
