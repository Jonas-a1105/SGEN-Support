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
const selectedEquipmentId = ref<number | null>(props.options.equipments?.[0]?.id ?? null);
const selectedCategoryId = ref<number>(props.options.categories?.[0]?.id ?? 1);
const selectedPriority = ref<TicketPriority>('media');
const titulo = ref('');
const descripcion = ref('');
const solicitante = ref('');
const departamento = ref('');
const selectedTechnicianId = ref<number>(props.options.technicians?.[0]?.id ?? 38);
const isSubmitting = ref(false);
const errorMessage = ref('');

// Device search & modal state
const deviceSearch = ref('');
const isDeviceModalOpen = ref(false);

const selectedEquipment = computed<FormEquipment | undefined>(() => {
    if (!selectedEquipmentId.value || !props.options.equipments) return undefined;
    return props.options.equipments.find((e) => e.id === selectedEquipmentId.value);
});

function selectEquipment(eq: FormEquipment) {
    selectedEquipmentId.value = eq.id;
    if (eq.department && eq.department !== 'Sin departamento') {
        departamento.value = eq.department;
    }
    if (eq.assigned_to && eq.assigned_to !== 'Sin asignar') {
        solicitante.value = eq.assigned_to;
    }
    isDeviceModalOpen.value = false;
    deviceSearch.value = '';
}

function clearEquipment() {
    selectedEquipmentId.value = null;
}

const searchResults = computed<FormEquipment[]>(() => {
    const q = deviceSearch.value.trim().toLowerCase();
    if (!q || !props.options.equipments) return [];
    return props.options.equipments.filter((eq) => {
        const code = (eq.code || '').toLowerCase();
        const serial = (eq.serial || '').toLowerCase();
        const model = (eq.model || '').toLowerCase();
        const dept = (eq.department || '').toLowerCase();
        const assigned = (eq.assigned_to || '').toLowerCase();
        return (
            code.includes(q) ||
            serial.includes(q) ||
            model.includes(q) ||
            dept.includes(q) ||
            assigned.includes(q)
        );
    });
});

function handleSearchSubmit() {
    if (searchResults.value.length === 1) {
        selectEquipment(searchResults.value[0]);
    } else if (searchResults.value.length > 1) {
        isDeviceModalOpen.value = true;
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

    isSubmitting.value = true;
    errorMessage.value = '';

    router.post(
        '/soportes',
        {
            titulo: titulo.value.trim(),
            descripcion: descripcion.value.trim(),
            equipo_id: selectedEquipmentId.value ?? 238,
            categoria_id: selectedCategoryId.value,
            prioridad: selectedPriority.value,
            empleado_id: selectedTechnicianId.value,
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
                <div class="tf-header">
                    <Link href="/soportes" class="tf-back-btn" title="Volver a lista de soportes">
                        <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12" /><polyline points="12 19 5 12 12 5" /></svg>
                    </Link>
                    <div>
                        <h1 class="tf-title">Nuevo Ticket de Soporte</h1>
                        <p class="tf-subtitle">Reporta una incidencia técnica para su resolución y seguimiento.</p>
                    </div>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="tf-alert-error">
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
                                <div class="tf-actions">
                                    <Link href="/soportes" class="tf-btn-cancel">
                                        Cancelar
                                    </Link>
                                    <button type="submit" class="tf-btn-save" :disabled="isSubmitting">
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
    max-width: 1200px;
    margin: 0 auto;
    padding: 12px 0 32px 0;
}

.tf-max-w {
    width: 100%;
}

.tf-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.tf-back-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    display: grid;
    place-items: center;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.tf-back-btn:hover {
    color: var(--text, #f4f4f6);
    border-color: var(--orange, #2563eb);
}

.tf-back-btn svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tf-title {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 2px 0;
}

.tf-subtitle {
    font-size: 13px;
    color: var(--text-muted, #8e9199);
    margin: 0;
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

.tf-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
    padding-top: 16px;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.tf-btn-cancel {
    padding: 10px 18px;
    border-radius: 10px;
    background: transparent;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.tf-btn-cancel:hover {
    color: var(--text, #f4f4f6);
    border-color: var(--stroke-hover, #454952);
}

.tf-btn-save {
    padding: 10px 22px;
    border-radius: 10px;
    background: var(--orange, #2563eb);
    border: var(--stroke-w, 2px) solid var(--orange, #2563eb);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: opacity 0.2s ease;
    box-shadow: none !important;
}

.tf-btn-save:hover:not(:disabled) {
    opacity: 0.9;
}

.tf-btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.tf-btn-save svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
}
</style>
