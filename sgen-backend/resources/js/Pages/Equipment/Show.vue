<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    BaseButton,
    BaseBadge,
    BaseCard,
    BaseKpiCard,
    BaseModal,
    BaseInput,
    BaseSelect,
    BaseTextarea,
    BaseEmptyState,
} from '@/Components/UI';

export interface EquipmentTicket {
    id: number;
    titulo: string;
    descripcion: string;
    estado: string;
    prioridad: string;
    fecha: string | null;
}

export interface EquipmentMaintenance {
    id: number;
    tipo: string;
    estado: string;
    descripcion: string;
    costo: number;
    realizadoPor: string;
    fecha: string | null;
    proximaFecha: string | null;
}

export interface DepartmentOption {
    id: number;
    nombre: string;
}

export interface EmployeeOption {
    id: number;
    nombre: string;
    cargo: string;
}

export interface EquipmentDetail {
    id: number;
    inventoryCode: string;
    serialNumber: string;
    name: string;
    type: string;
    brand: string | null;
    model: string | null;
    status: string;
    rawStatus: string;
    departmentId: number | null;
    departmentName: string | null;
    employeeId: number | null;
    employeeName: string | null;
    physicalLocation: string | null;
    processor: string | null;
    ram: string | null;
    storage: string | null;
    os: string | null;
    ipAddress: string | null;
    driver: string | null;
    toner: string | null;
    purchaseDate: string | null;
    supplier: string | null;
    warranty: string | null;
    purchaseValue: number | null;
    warrantyPercent: number;
    warrantyStatus: 'active' | 'warning' | 'expired';
    warrantyRemaining: string | null;
    tickets: EquipmentTicket[];
    maintenances: EquipmentMaintenance[];
    departamentos: DepartmentOption[];
    empleados: EmployeeOption[];
}

const props = defineProps<{
    equipment: EquipmentDetail;
}>();

// Navigation Tabs
type TabKey = 'specs' | 'purchase' | 'support' | 'maintenance';
const activeTab = ref<TabKey>('specs');

const setActiveTab = (tab: TabKey) => {
    activeTab.value = tab;
};

// Reassignment Modal
const isReassignModalOpen = ref(false);
const reassignForm = ref({
    departamento_id: props.equipment.departmentId ?? '',
    empleado_id: props.equipment.employeeId ?? '',
    ubicacion_fisica: props.equipment.physicalLocation ?? '',
});

const openReassignModal = () => {
    reassignForm.value = {
        departamento_id: props.equipment.departmentId ?? '',
        empleado_id: props.equipment.employeeId ?? '',
        ubicacion_fisica: props.equipment.physicalLocation ?? '',
    };
    isReassignModalOpen.value = true;
};

const handleSaveReassignment = () => {
    router.put(
        `/equipos/${props.equipment.id}`,
        {
            departamento_id: reassignForm.value.departamento_id || null,
            empleado_id: reassignForm.value.empleado_id || null,
            ubicacion_fisica: reassignForm.value.ubicacion_fisica || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isReassignModalOpen.value = false;
            },
        }
    );
};

// Edit Equipment Modal
const isEditModalOpen = ref(false);
const editForm = ref({
    marca: props.equipment.brand ?? '',
    modelo: props.equipment.model ?? '',
    tipo: props.equipment.type,
    estado: props.equipment.rawStatus,
    numero_serie: props.equipment.serialNumber,
    procesador: props.equipment.processor ?? '',
    memoria_ram: props.equipment.ram ?? '',
    almacenamiento: props.equipment.storage ?? '',
    sistema_operativo: props.equipment.os ?? '',
    direccion_ip: props.equipment.ipAddress ?? '',
    proveedor: props.equipment.supplier ?? '',
    valor_compra: props.equipment.purchaseValue ?? '',
    fecha_compra: props.equipment.purchaseDate ?? '',
    garantia: props.equipment.warranty ?? '',
});

const openEditModal = () => {
    editForm.value = {
        marca: props.equipment.brand ?? '',
        modelo: props.equipment.model ?? '',
        tipo: props.equipment.type,
        estado: props.equipment.rawStatus,
        numero_serie: props.equipment.serialNumber,
        procesador: props.equipment.processor ?? '',
        memoria_ram: props.equipment.ram ?? '',
        almacenamiento: props.equipment.storage ?? '',
        sistema_operativo: props.equipment.os ?? '',
        direccion_ip: props.equipment.ipAddress ?? '',
        proveedor: props.equipment.supplier ?? '',
        valor_compra: props.equipment.purchaseValue ?? '',
        fecha_compra: props.equipment.purchaseDate ?? '',
        garantia: props.equipment.warranty ?? '',
    };
    isEditModalOpen.value = true;
};

const handleSaveEdit = () => {
    router.put(
        `/equipos/${props.equipment.id}`,
        editForm.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditModalOpen.value = false;
            },
        }
    );
};

// Formatting helpers
const formatCurrency = (val: number | null): string => {
    if (val === null || val === undefined) return 'N/A';
    return '$' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getStatusBadgeVariant = (rawStatus: string): 'success' | 'info' | 'warning' | 'danger' | 'neutral' => {
    switch (rawStatus.toLowerCase()) {
        case 'disponible':
        case 'nuevo':
        case 'en_uso':
            return 'success';
        case 'en_reparacion':
        case 'usado':
        case 'en_reserva':
            return 'warning';
        case 'fuera_de_servicio':
            return 'danger';
        default:
            return 'neutral';
    }
};

const getTicketStatusVariant = (st: string): 'warning' | 'info' | 'neutral' | 'success' => {
    switch (st.toLowerCase()) {
        case 'pendiente':
            return 'warning';
        case 'en_proceso':
            return 'info';
        case 'resuelto':
            return 'success';
        default:
            return 'neutral';
    }
};

const getWarrantyProgressClass = (pct: number): string => {
    const rounded = Math.round(Math.min(100, Math.max(0, pct)) / 5) * 5;
    return `progress-pct-${rounded}`;
};

const kpiStatusColor = computed<'green' | 'blue' | 'yellow' | 'red'>(() => {
    switch (props.equipment.rawStatus.toLowerCase()) {
        case 'disponible':
            return 'green';
        case 'en_uso':
            return 'blue';
        case 'en_reparacion':
            return 'yellow';
        default:
            return 'red';
    }
});
</script>

<template>
    <AppLayout :title="`Equipo: ${equipment.name}`">
        <Head :title="`Equipo - ${equipment.name}`" />

        <div class="equip-show-container">
            <!-- Header Bar -->
            <header class="equip-page-header">
                <div class="equip-header-left">
                    <Link href="/equipos" class="equip-back-link">
                        <svg class="header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Volver a Equipos</span>
                    </Link>

                    <div class="equip-title-row">
                        <div class="equip-icon-box">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div class="equip-title-text-group">
                            <div class="equip-headline-tags">
                                <h1 class="equip-main-title">{{ equipment.name }}</h1>
                                <BaseBadge variant="code" size="md">{{ equipment.inventoryCode }}</BaseBadge>
                                <BaseBadge :variant="getStatusBadgeVariant(equipment.rawStatus)" size="md" dot>
                                    {{ equipment.status }}
                                </BaseBadge>
                            </div>
                            <div class="equip-meta-info">
                                <span class="equip-type-pill">{{ equipment.type }}</span>
                                <span v-if="equipment.serialNumber" class="equip-serial-pill">
                                    S/N: {{ equipment.serialNumber }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="equip-header-actions">
                    <BaseButton variant="subtle" size="md" @click="openEditModal">
                        <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Editar Equipo</span>
                    </BaseButton>
                    <Link :href="`/soportes/crear?equipo_id=${equipment.id}`">
                        <BaseButton variant="primary" size="md">
                            <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Crear Ticket</span>
                        </BaseButton>
                    </Link>
                </div>
            </header>

            <!-- 4 KPI Cards -->
            <section class="equip-kpi-grid" aria-label="Indicadores del equipo">
                <BaseKpiCard
                    label="ESTADO OPERATIVO"
                    :value="equipment.status"
                    subtext="Condición del activo"
                    icon="fa-solid fa-signal"
                    :color="kpiStatusColor"
                />
                <BaseKpiCard
                    label="MANTENIMIENTOS"
                    :value="equipment.maintenances.length"
                    subtext="Historial de servicios"
                    icon="fa-solid fa-wrench"
                    color="yellow"
                    :active="activeTab === 'maintenance'"
                    clickable
                    @click="setActiveTab('maintenance')"
                />
                <BaseKpiCard
                    label="TICKETS DE SOPORTE"
                    :value="equipment.tickets.length"
                    subtext="Reportes de incidencia"
                    icon="fa-solid fa-ticket"
                    color="blue"
                    :active="activeTab === 'support'"
                    clickable
                    @click="setActiveTab('support')"
                />
                <BaseKpiCard
                    label="ESTADO DE GARANTÍA"
                    :value="equipment.warrantyRemaining || (equipment.warranty ? 'Activa' : 'Sin garantía')"
                    :subtext="equipment.purchaseValue ? formatCurrency(equipment.purchaseValue) : 'Sin costo reg.'"
                    icon="fa-solid fa-shield-halved"
                    color="purple"
                    :active="activeTab === 'purchase'"
                    clickable
                    @click="setActiveTab('purchase')"
                />
            </section>

            <!-- Main 2-Column Layout -->
            <div class="equip-content-layout">
                <!-- Left Column: Identity & Custody -->
                <div class="equip-sidebar-col">
                    <!-- Ubicación y Custodio -->
                    <BaseCard title="Ubicación y Asignación" variant="glass" padding="md">
                        <template #actions>
                            <BaseButton variant="subtle" size="sm" @click="openReassignModal">
                                Reasignar
                            </BaseButton>
                        </template>

                        <div class="custody-info-stack">
                            <div class="custody-item">
                                <div class="custody-icon blue">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                        <line x1="9" y1="22" x2="9" y2="2"></line>
                                        <line x1="15" y1="22" x2="15" y2="2"></line>
                                    </svg>
                                </div>
                                <div class="custody-details">
                                    <span class="custody-label">DEPARTAMENTO</span>
                                    <Link
                                        v-if="equipment.departmentId"
                                        :href="`/departamentos/${equipment.departmentId}`"
                                        class="custody-link"
                                    >
                                        {{ equipment.departmentName }}
                                    </Link>
                                    <span v-else class="custody-unassigned">No Asignado</span>
                                    <span class="custody-sub">{{ equipment.physicalLocation || 'Sin ubicación específica' }}</span>
                                </div>
                            </div>

                            <div class="custody-item">
                                <div class="custody-icon orange">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="custody-details">
                                    <span class="custody-label">CUSTODIO ASIGNADO</span>
                                    <Link
                                        v-if="equipment.employeeId"
                                        :href="`/personal/${equipment.employeeId}`"
                                        class="custody-link"
                                    >
                                        {{ equipment.employeeName }}
                                    </Link>
                                    <span v-else class="custody-unassigned">Sin custodio directo</span>
                                </div>
                            </div>
                        </div>
                    </BaseCard>

                    <!-- Ficha Resumen -->
                    <BaseCard title="Identificación del Activo" variant="glass" padding="md">
                        <div class="spec-list-summary">
                            <div class="summary-row">
                                <span class="summary-label">Marca</span>
                                <span class="summary-val">{{ equipment.brand || 'N/A' }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Modelo</span>
                                <span class="summary-val">{{ equipment.model || 'N/A' }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Tipo</span>
                                <span class="summary-val">{{ equipment.type }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Código Inventario</span>
                                <span class="summary-val mono">{{ equipment.inventoryCode }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Serial</span>
                                <span class="summary-val mono">{{ equipment.serialNumber || 'N/A' }}</span>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Right Column: Tabs Panel -->
                <div class="equip-main-col">
                    <div class="equip-panel-wrapper">
                        <!-- Navigation Tabs Bar -->
                        <nav class="equip-tabs-nav" aria-label="Pestañas de equipo">
                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'specs' }"
                                @click="setActiveTab('specs')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                    <rect x="9" y="9" width="6" height="6"></rect>
                                    <line x1="9" y1="1" x2="9" y2="4"></line>
                                    <line x1="15" y1="1" x2="15" y2="4"></line>
                                    <line x1="9" y1="20" x2="9" y2="23"></line>
                                    <line x1="15" y1="20" x2="15" y2="23"></line>
                                    <line x1="20" y1="9" x2="23" y2="9"></line>
                                    <line x1="20" y1="14" x2="23" y2="14"></line>
                                    <line x1="1" y1="9" x2="4" y2="9"></line>
                                    <line x1="1" y1="14" x2="4" y2="14"></line>
                                </svg>
                                <span>Especificaciones</span>
                            </button>

                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'purchase' }"
                                @click="setActiveTab('purchase')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                <span>Adquisición y Garantía</span>
                            </button>

                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'support' }"
                                @click="setActiveTab('support')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span>Soportes</span>
                                <span v-if="equipment.tickets.length > 0" class="equip-tab-counter">
                                    {{ equipment.tickets.length }}
                                </span>
                            </button>

                            <button
                                type="button"
                                class="equip-tab-button"
                                :class="{ 'is-active': activeTab === 'maintenance' }"
                                @click="setActiveTab('maintenance')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                </svg>
                                <span>Mantenimiento</span>
                                <span v-if="equipment.maintenances.length > 0" class="equip-tab-counter">
                                    {{ equipment.maintenances.length }}
                                </span>
                            </button>
                        </nav>

                        <!-- Tab Panels -->
                        <div class="equip-tab-body">
                            <!-- Panel 1: Especificaciones -->
                            <div v-if="activeTab === 'specs'" class="tab-panel-specs">
                                <div class="hardware-specs-grid">
                                    <div class="spec-card">
                                        <div class="spec-icon-box">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                                <rect x="9" y="9" width="6" height="6"></rect>
                                            </svg>
                                        </div>
                                        <div class="spec-info">
                                            <span class="spec-label">Procesador</span>
                                            <strong class="spec-val">{{ equipment.processor || 'No especificado' }}</strong>
                                        </div>
                                    </div>

                                    <div class="spec-card">
                                        <div class="spec-icon-box">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 19v-3"></path>
                                                <path d="M10 19v-3"></path>
                                                <path d="M14 19v-3"></path>
                                                <path d="M18 19v-3"></path>
                                                <rect x="2" y="5" width="20" height="11" rx="2"></rect>
                                            </svg>
                                        </div>
                                        <div class="spec-info">
                                            <span class="spec-label">Memoria RAM</span>
                                            <strong class="spec-val">{{ equipment.ram || 'No especificada' }}</strong>
                                        </div>
                                    </div>

                                    <div class="spec-card">
                                        <div class="spec-icon-box">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                            </svg>
                                        </div>
                                        <div class="spec-info">
                                            <span class="spec-label">Almacenamiento</span>
                                            <strong class="spec-val">{{ equipment.storage || 'No especificado' }}</strong>
                                        </div>
                                    </div>

                                    <div class="spec-card">
                                        <div class="spec-icon-box">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                                <line x1="12" y1="17" x2="12" y2="21"></line>
                                            </svg>
                                        </div>
                                        <div class="spec-info">
                                            <span class="spec-label">Sistema Operativo</span>
                                            <strong class="spec-val">{{ equipment.os || 'No especificado' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="network-details-box">
                                    <h4 class="section-subheading">Configuración de Red y Conectividad</h4>
                                    <div class="network-grid">
                                        <div class="net-item">
                                            <span class="net-label">Dirección IP Asignada</span>
                                            <span class="net-val mono">{{ equipment.ipAddress || 'Sin IP configurada' }}</span>
                                        </div>
                                        <div v-if="equipment.driver" class="net-item">
                                            <span class="net-label">Driver / Controlador</span>
                                            <span class="net-val">{{ equipment.driver }}</span>
                                        </div>
                                        <div v-if="equipment.toner" class="net-item">
                                            <span class="net-label">Tóner / Insumo Compatible</span>
                                            <span class="net-val">{{ equipment.toner }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel 2: Adquisición y Garantía -->
                            <div v-else-if="activeTab === 'purchase'" class="tab-panel-purchase">
                                <div class="purchase-details-card">
                                    <div class="purchase-top-row">
                                        <div class="vendor-box">
                                            <span class="purchase-label">PROVEEDOR</span>
                                            <h4 class="vendor-name">{{ equipment.supplier || 'No registrado' }}</h4>
                                        </div>
                                        <div class="price-box">
                                            <span class="purchase-label">VALOR DE COMPRA</span>
                                            <h3 class="price-val">{{ formatCurrency(equipment.purchaseValue) }}</h3>
                                        </div>
                                    </div>

                                    <div class="warranty-card-section">
                                        <div class="warranty-header-row">
                                            <span class="warranty-title">Estado de la Garantía</span>
                                            <BaseBadge
                                                :variant="equipment.warrantyStatus === 'active' ? 'success' : equipment.warrantyStatus === 'warning' ? 'warning' : 'danger'"
                                                size="md"
                                                dot
                                            >
                                                {{ equipment.warrantyStatus === 'active' ? 'Garantía Vigente' : equipment.warrantyStatus === 'warning' ? 'Por Vencer' : 'Garantía Expirada' }}
                                            </BaseBadge>
                                        </div>

                                        <div v-if="equipment.warranty" class="warranty-progress-wrap">
                                            <div class="progress-track">
                                                <div
                                                    class="progress-fill"
                                                    :class="[getWarrantyProgressClass(equipment.warrantyPercent), `warranty-${equipment.warrantyStatus}`]"
                                                ></div>
                                            </div>
                                            <div class="warranty-dates-row">
                                                <span>Adquisición: {{ equipment.purchaseDate || 'N/A' }}</span>
                                                <span v-if="equipment.warrantyRemaining">
                                                    Restante: {{ equipment.warrantyRemaining }}
                                                </span>
                                                <span>Vencimiento: {{ equipment.warranty }}</span>
                                            </div>
                                        </div>

                                        <div v-else class="no-warranty-notice">
                                            Sin fecha de vencimiento de garantía registrada en el sistema.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel 3: Tickets de Soporte -->
                            <div v-else-if="activeTab === 'support'" class="tab-panel-support">
                                <div v-if="equipment.tickets.length > 0" class="tickets-table-card">
                                    <div class="table-responsive">
                                        <table class="detail-table">
                                            <thead>
                                                <tr>
                                                    <th>CÓDIGO</th>
                                                    <th>ASUNTO / REPORTE</th>
                                                    <th>ESTADO</th>
                                                    <th>PRIORIDAD</th>
                                                    <th>FECHA</th>
                                                    <th class="text-right">ACCIÓN</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="t in equipment.tickets" :key="t.id">
                                                    <td>
                                                        <BaseBadge variant="code" size="md">#{{ t.id }}</BaseBadge>
                                                    </td>
                                                    <td>
                                                        <div class="ticket-info-cell">
                                                            <strong class="ticket-title">{{ t.titulo }}</strong>
                                                            <span class="ticket-desc">{{ t.descripcion }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <BaseBadge :variant="getTicketStatusVariant(t.estado)" size="sm">
                                                            {{ t.estado }}
                                                        </BaseBadge>
                                                    </td>
                                                    <td>
                                                        <span class="priority-tag">{{ t.prioridad }}</span>
                                                    </td>
                                                    <td class="text-muted-cell">{{ t.fecha }}</td>
                                                    <td class="text-right">
                                                        <Link :href="`/soportes/${t.id}`" class="action-btn-circle" title="Ver Ticket">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                <circle cx="12" cy="12" r="3"></circle>
                                                            </svg>
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <BaseEmptyState
                                    v-else
                                    title="Sin tickets reportados"
                                    description="Este equipo opera con normalidad y no presenta incidencias de soporte registradas."
                                >
                                    <Link :href="`/soportes/crear?equipo_id=${equipment.id}`">
                                        <BaseButton variant="primary" size="md">
                                            Crear Ticket para este Equipo
                                        </BaseButton>
                                    </Link>
                                </BaseEmptyState>
                            </div>

                            <!-- Panel 4: Mantenimiento -->
                            <div v-else-if="activeTab === 'maintenance'" class="tab-panel-maintenance">
                                <div v-if="equipment.maintenances.length > 0" class="maint-timeline-wrap">
                                    <div class="maint-timeline">
                                        <div
                                            v-for="m in equipment.maintenances"
                                            :key="m.id"
                                            class="maint-timeline-item"
                                        >
                                            <div class="maint-timeline-dot"></div>
                                            <div class="maint-card">
                                                <div class="maint-card-header">
                                                    <div class="maint-type-pill">
                                                        <span class="maint-type-title">{{ m.tipo }}</span>
                                                        <BaseBadge variant="neutral" size="sm">{{ m.estado }}</BaseBadge>
                                                    </div>
                                                    <span class="maint-date">{{ m.fecha }}</span>
                                                </div>
                                                <p class="maint-desc">{{ m.descripcion }}</p>
                                                <div class="maint-footer-meta">
                                                    <span class="maint-tech">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                        {{ m.realizadoPor }}
                                                    </span>
                                                    <span v-if="m.costo > 0" class="maint-cost">
                                                        Costo: {{ formatCurrency(m.costo) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <BaseEmptyState
                                    v-else
                                    title="Sin mantenimientos registrados"
                                    description="No hay intervenciones o mantenimientos preventivos/correctivos cargados para este equipo."
                                >
                                    <Link :href="`/mantenimientos/crear?equipo_id=${equipment.id}`">
                                        <BaseButton variant="primary" size="md">
                                            Registrar Mantenimiento
                                        </BaseButton>
                                    </Link>
                                </BaseEmptyState>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Reasignar Ubicación y Custodio -->
        <BaseModal
            :is-open="isReassignModalOpen"
            title="Reasignar Ubicación y Custodio"
            max-width="md"
            @close="isReassignModalOpen = false"
        >
            <form @submit.prevent="handleSaveReassignment" class="modal-form-stack">
                <div class="form-group">
                    <label class="form-label" for="select-reassign-dept">Departamento Destino</label>
                    <select
                        id="select-reassign-dept"
                        v-model="reassignForm.departamento_id"
                        class="form-control-select"
                    >
                        <option value="">Sin departamento asignado</option>
                        <option
                            v-for="d in equipment.departamentos"
                            :key="d.id"
                            :value="d.id"
                        >
                            {{ d.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="select-reassign-emp">Custodio Asignado</label>
                    <select
                        id="select-reassign-emp"
                        v-model="reassignForm.empleado_id"
                        class="form-control-select"
                    >
                        <option value="">Sin custodio asignado</option>
                        <option
                            v-for="e in equipment.empleados"
                            :key="e.id"
                            :value="e.id"
                        >
                            {{ e.nombre }} ({{ e.cargo }})
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <BaseInput
                        v-model="reassignForm.ubicacion_fisica"
                        label="Ubicación Física Específica"
                        placeholder="Ej. Oficina 302, Mesa 4"
                    />
                </div>

                <div class="modal-actions-bar">
                    <BaseButton type="button" variant="subtle" size="md" @click="isReassignModalOpen = false">
                        Cancelar
                    </BaseButton>
                    <BaseButton type="submit" variant="primary" size="md">
                        Guardar Reasignación
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- MODAL: Editar Ficha Técnica del Equipo -->
        <BaseModal
            :is-open="isEditModalOpen"
            title="Editar Ficha del Equipo"
            max-width="lg"
            @close="isEditModalOpen = false"
        >
            <form @submit.prevent="handleSaveEdit" class="modal-form-stack">
                <div class="form-grid-2">
                    <BaseInput v-model="editForm.marca" label="Marca" placeholder="Ej. Dell, HP, Lenovo" />
                    <BaseInput v-model="editForm.modelo" label="Modelo" placeholder="Ej. Latitude 5420" />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.tipo" label="Tipo de Dispositivo" placeholder="Computadora, Laptop..." required />
                    <BaseInput v-model="editForm.numero_serie" label="Número de Serie" placeholder="S/N" />
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Estado Operativo</label>
                        <select v-model="editForm.estado" class="form-control-select">
                            <option value="disponible">Disponible</option>
                            <option value="en_uso">En Uso</option>
                            <option value="en_reparacion">En Reparación</option>
                            <option value="fuera_de_servicio">Fuera de Servicio</option>
                        </select>
                    </div>
                    <BaseInput v-model="editForm.direccion_ip" label="Dirección IP" placeholder="192.168.1.X" />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.procesador" label="Procesador (CPU)" placeholder="Intel Core i5..." />
                    <BaseInput v-model="editForm.memoria_ram" label="Memoria RAM" placeholder="16 GB DDR4" />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.almacenamiento" label="Almacenamiento" placeholder="512 GB NVMe SSD" />
                    <BaseInput v-model="editForm.sistema_operativo" label="Sistema Operativo" placeholder="Windows 11 Pro" />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.proveedor" label="Proveedor" placeholder="Nombre del proveedor" />
                    <BaseInput v-model="editForm.valor_compra" type="number" step="0.01" label="Valor de Compra ($)" placeholder="0.00" />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.fecha_compra" type="date" label="Fecha de Compra" />
                    <BaseInput v-model="editForm.garantia" type="date" label="Vencimiento de Garantía" />
                </div>

                <div class="modal-actions-bar">
                    <BaseButton type="button" variant="subtle" size="md" @click="isEditModalOpen = false">
                        Cancelar
                    </BaseButton>
                    <BaseButton type="submit" variant="primary" size="md">
                        Guardar Cambios
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </AppLayout>
</template>

<style scoped>
/* 1. Base tokens & layout */
.equip-show-container {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
    padding: var(--space-6);
    max-width: 1440px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

/* 2. Header Bar */
.equip-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.equip-header-left {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.equip-back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.equip-back-link:hover {
    color: var(--brand);
}

.header-icon-svg {
    width: 16px;
    height: 16px;
}

.equip-title-row {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.equip-icon-box {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-md);
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand);
    flex-shrink: 0;
}

.equip-icon-box svg {
    width: 26px;
    height: 26px;
}

.equip-title-text-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.equip-headline-tags {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.equip-main-title {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--text);
    margin: 0;
}

.equip-meta-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: 13px;
    color: var(--text-muted);
}

.equip-type-pill {
    font-weight: 600;
    color: var(--brand);
}

.equip-serial-pill {
    font-family: var(--font-mono);
    color: var(--text-dim);
}

.equip-header-actions {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
}

/* 3. KPI Grid */
.equip-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: var(--space-4);
}

/* 4. 2-Column Layout */
.equip-content-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: var(--space-6);
    align-items: start;
}

@media (max-width: 1024px) {
    .equip-content-layout {
        grid-template-columns: 1fr;
    }
}

.equip-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

.custody-info-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.custody-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
}

.custody-icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.custody-icon svg {
    width: 18px;
    height: 18px;
}

.custody-icon.blue {
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
}

.custody-icon.orange {
    background: rgba(249, 115, 22, 0.12);
    color: #f97316;
}

.custody-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.custody-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: var(--text-dim);
}

.custody-link {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.custody-link:hover {
    color: var(--brand);
}

.custody-unassigned {
    font-size: 13px;
    color: var(--text-muted);
}

.custody-sub {
    font-size: 12px;
    color: var(--text-muted);
}

.spec-list-summary {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--space-2);
    border-bottom: 1px solid var(--stroke-subtle);
    font-size: 13px;
}

.summary-label {
    color: var(--text-dim);
    font-weight: 600;
}

.summary-val {
    color: var(--text);
    font-weight: 700;
}

.summary-val.mono {
    font-family: var(--font-mono);
}

/* Right Column Panel */
.equip-panel-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--card-radius);
    overflow: hidden;
    box-shadow: none !important;
}

.equip-tabs-nav {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-2) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
    overflow-x: auto;
}

.equip-tab-button {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all var(--transition-fast);
    white-space: nowrap;
    outline: none;
}

.equip-tab-button svg {
    width: 16px;
    height: 16px;
}

.equip-tab-button:hover {
    color: var(--text);
}

.equip-tab-button.is-active {
    color: var(--brand);
    border-bottom-color: var(--brand);
}

.equip-tab-counter {
    background: var(--stroke);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
}

.equip-tab-button.is-active .equip-tab-counter {
    background: rgba(var(--brand-rgb), 0.2);
    color: var(--brand);
}

.equip-tab-body {
    padding: var(--space-6);
}

/* Specs Tab */
.hardware-specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--space-4);
}

.spec-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    background: var(--bg-sub);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
}

.spec-icon-box {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: rgba(var(--brand-rgb), 0.1);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.spec-icon-box svg {
    width: 20px;
    height: 20px;
}

.spec-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.spec-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.spec-val {
    font-size: 14px;
    color: var(--text);
}

.network-details-box {
    margin-top: var(--space-6);
    padding-top: var(--space-5);
    border-top: 1px solid var(--stroke-subtle);
}

.section-subheading {
    margin: 0 0 var(--space-4) 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.network-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
}

.net-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    background: var(--bg-sub);
    padding: var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid var(--stroke-subtle);
}

.net-label {
    font-size: 11px;
    color: var(--text-dim);
    font-weight: 700;
}

.net-val {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.net-val.mono {
    font-family: var(--font-mono);
}

/* Purchase & Warranty */
.purchase-details-card {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.purchase-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--space-5);
    border-bottom: 1px solid var(--stroke-subtle);
}

.purchase-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: var(--text-dim);
}

.vendor-name {
    margin: var(--space-1) 0 0 0;
    font-size: 18px;
    color: var(--text);
}

.price-val {
    margin: var(--space-1) 0 0 0;
    font-size: 24px;
    font-weight: 800;
    color: var(--brand);
    font-family: var(--font-mono);
}

.warranty-card-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.warranty-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.warranty-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
}

.warranty-progress-wrap {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.progress-track {
    height: 10px;
    background: var(--stroke);
    border-radius: var(--radius-pill);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: var(--radius-pill);
    transition: width 0.3s ease;
}

.warranty-active {
    background: var(--brand);
}

.warranty-warning {
    background: var(--yellow);
}

.warranty-expired {
    background: var(--red);
}

/* Progress width classes */
.progress-pct-0 { width: 0%; }
.progress-pct-5 { width: 5%; }
.progress-pct-10 { width: 10%; }
.progress-pct-15 { width: 15%; }
.progress-pct-20 { width: 20%; }
.progress-pct-25 { width: 25%; }
.progress-pct-30 { width: 30%; }
.progress-pct-35 { width: 35%; }
.progress-pct-40 { width: 40%; }
.progress-pct-45 { width: 45%; }
.progress-pct-50 { width: 50%; }
.progress-pct-55 { width: 55%; }
.progress-pct-60 { width: 60%; }
.progress-pct-65 { width: 65%; }
.progress-pct-70 { width: 70%; }
.progress-pct-75 { width: 75%; }
.progress-pct-80 { width: 80%; }
.progress-pct-85 { width: 85%; }
.progress-pct-90 { width: 90%; }
.progress-pct-95 { width: 95%; }
.progress-pct-100 { width: 100%; }

.warranty-dates-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-dim);
}

.no-warranty-notice {
    padding: var(--space-4);
    background: var(--bg-sub);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-size: 13px;
    text-align: center;
}

/* Tickets Table */
.tickets-table-card {
    background: var(--bg-card);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.detail-table th {
    background: var(--bg-sub);
    padding: var(--space-3) var(--space-4);
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--stroke);
}

.detail-table td {
    padding: var(--space-3) var(--space-4);
    border-bottom: 1px solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.detail-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.text-right {
    text-align: right;
}

.text-muted-cell {
    color: var(--text-muted);
}

.ticket-info-cell {
    display: flex;
    flex-direction: column;
}

.ticket-title {
    font-weight: 700;
    color: var(--text);
}

.ticket-desc {
    font-size: 12px;
    color: var(--text-dim);
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.priority-tag {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
}

.action-btn-circle {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-pill);
    background: var(--bg-sub);
    border: 1px solid var(--stroke);
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
}

.action-btn-circle svg {
    width: 14px;
    height: 14px;
}

.action-btn-circle:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
    background: var(--stroke);
}

/* Maintenance Timeline */
.maint-timeline {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    position: relative;
    padding-left: var(--space-6);
}

.maint-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: var(--stroke);
}

.maint-timeline-item {
    position: relative;
    display: flex;
    align-items: flex-start;
}

.maint-timeline-dot {
    position: absolute;
    left: -20px;
    top: 14px;
    width: 10px;
    height: 10px;
    border-radius: var(--radius-pill);
    background: var(--brand);
    border: 2px solid var(--bg-card);
}

.maint-card {
    flex: 1;
    background: var(--bg-sub);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.maint-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.maint-type-pill {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.maint-type-title {
    font-weight: 700;
    color: var(--text);
    text-transform: capitalize;
}

.maint-date {
    font-size: 12px;
    color: var(--text-dim);
}

.maint-desc {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
}

.maint-footer-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-dim);
    margin-top: var(--space-2);
}

.maint-tech {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
}

.maint-tech svg {
    width: 14px;
    height: 14px;
}

.maint-cost {
    font-family: var(--font-mono);
    font-weight: 700;
    color: var(--text);
}

/* Modal Form Styles */
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

@media (max-width: 640px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.form-control-select {
    width: 100%;
    height: 42px;
    padding: 0 var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
}

.form-control-select:focus {
    border-color: var(--brand);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
