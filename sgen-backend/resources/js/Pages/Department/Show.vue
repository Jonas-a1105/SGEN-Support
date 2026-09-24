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
    BaseConfirmModal,
    BaseInput,
    BaseSelect,
    BaseTextarea,
    BaseSearchToolbar,
    BaseEmptyState,
} from '@/Components/UI';

export interface DepartmentEmployee {
    id: number;
    nombre: string;
    apellido: string;
    fullName: string;
    cedula: string;
    cargo: string;
    email: string;
    telefono: string;
    rol: string;
    initials: string;
}

export interface DepartmentEquipment {
    id: number;
    codigo: string;
    numeroSerie: string;
    nombre: string;
    tipo: string;
    marca: string;
    modelo: string;
    estado: string;
    ubicacion: string;
    custodio: string;
}

export interface DepartmentConsumable {
    id: number;
    codigo: string;
    nombre: string;
    categoria: string;
    unidad_medida: string;
    stock: number;
    stock_minimo: number;
    is_low_stock: boolean;
    valor_unitario: number;
    valor_total: number;
}

export interface EmployeeCandidate {
    id: number;
    nombre: string;
    cargo: string;
    departamento_id: number | null;
}

export interface EquipmentCandidate {
    id: number;
    codigo: string;
    nombre: string;
    estado: string;
}

export interface DepartmentDetail {
    id: number;
    code: string;
    nombre: string;
    ubicacion: string | null;
    jefeAreaNombre: string | null;
    jefeAreaId: number | null;
    jefeEmail: string | null;
    jefeTelefono: string | null;
    jefeCargo: string | null;
    jefeInitials: string;
    descripcion: string | null;
    equiposCount: number;
    empleadosCount: number;
    inventoryPercent: number;
    ticketsCount: number;
    consumablesCount: number;
    consumablesValue: number;
    color: string;
    empleados: DepartmentEmployee[];
    equipos: DepartmentEquipment[];
    consumibles: DepartmentConsumable[];
    candidatosEmpleados: EmployeeCandidate[];
    candidatosEquipos: EquipmentCandidate[];
    createdAt: string | null;
}

const props = defineProps<{
    department: DepartmentDetail;
}>();

// Navigation Tabs
type TabKey = 'overview' | 'employees' | 'assets' | 'inventory';
const activeTab = ref<TabKey>('overview');

const setActiveTab = (tab: TabKey) => {
    activeTab.value = tab;
};

// Filters
const employeeSearch = ref('');
const assetSearch = ref('');
const consumableSearch = ref('');
const consumableCategory = ref('all');

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase();
    if (!q) return props.department.empleados;
    return props.department.empleados.filter((e) =>
        e.fullName.toLowerCase().includes(q) ||
        e.cargo.toLowerCase().includes(q) ||
        e.email.toLowerCase().includes(q) ||
        e.cedula.toLowerCase().includes(q)
    );
});

const filteredAssets = computed(() => {
    const q = assetSearch.value.trim().toLowerCase();
    if (!q) return props.department.equipos;
    return props.department.equipos.filter((eq) =>
        eq.codigo.toLowerCase().includes(q) ||
        eq.nombre.toLowerCase().includes(q) ||
        eq.tipo.toLowerCase().includes(q) ||
        eq.custodio.toLowerCase().includes(q)
    );
});

const availableCategories = computed(() => {
    const cats = new Set<string>();
    props.department.consumibles.forEach((c) => {
        if (c.categoria) cats.add(c.categoria);
    });
    return Array.from(cats).sort();
});

const filteredConsumables = computed(() => {
    const q = consumableSearch.value.trim().toLowerCase();
    const cat = consumableCategory.value;

    return props.department.consumibles.filter((c) => {
        const matchesQuery = !q ||
            c.codigo.toLowerCase().includes(q) ||
            c.nombre.toLowerCase().includes(q) ||
            c.categoria.toLowerCase().includes(q);

        const matchesCat = cat === 'all' || c.categoria === cat;
        return matchesQuery && matchesCat;
    });
});

// Modals
const isAssignEmployeeOpen = ref(false);
const selectedEmployeeId = ref<number | ''>('');

const isAssignEquipmentOpen = ref(false);
const selectedEquipmentId = ref<number | ''>('');

const isEditModalOpen = ref(false);
const editForm = ref({
    nombre: props.department.nombre,
    ubicacion: props.department.ubicacion || '',
    descripcion: props.department.descripcion || '',
    jefe_area_id: props.department.jefeAreaId || '',
});

interface UnlinkTarget {
    type: 'employee' | 'equipment';
    id: number;
    name: string;
}
const isConfirmUnlinkOpen = ref(false);
const unlinkTarget = ref<UnlinkTarget | null>(null);

// Assign Employee Actions
const openAssignEmployeeModal = () => {
    selectedEmployeeId.value = '';
    isAssignEmployeeOpen.value = true;
};

const handleAssignEmployee = () => {
    if (!selectedEmployeeId.value) return;

    router.post(
        `/departamentos/${props.department.id}/empleados`,
        { empleado_id: selectedEmployeeId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isAssignEmployeeOpen.value = false;
                selectedEmployeeId.value = '';
            },
        }
    );
};

// Assign Equipment Actions
const openAssignEquipmentModal = () => {
    selectedEquipmentId.value = '';
    isAssignEquipmentOpen.value = true;
};

const handleAssignEquipment = () => {
    if (!selectedEquipmentId.value) return;

    router.post(
        `/departamentos/${props.department.id}/equipos`,
        { equipo_id: selectedEquipmentId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isAssignEquipmentOpen.value = false;
                selectedEquipmentId.value = '';
            },
        }
    );
};

// Edit Department Actions
const openEditModal = () => {
    editForm.value = {
        nombre: props.department.nombre,
        ubicacion: props.department.ubicacion || '',
        descripcion: props.department.descripcion || '',
        jefe_area_id: props.department.jefeAreaId || '',
    };
    isEditModalOpen.value = true;
};

const handleSaveDepartment = () => {
    router.put(
        `/departamentos/${props.department.id}`,
        {
            nombre: editForm.value.nombre,
            ubicacion: editForm.value.ubicacion,
            descripcion: editForm.value.descripcion,
            jefe_area_id: editForm.value.jefe_area_id || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditModalOpen.value = false;
            },
        }
    );
};

// Unlink actions
const requestUnlinkEmployee = (emp: DepartmentEmployee) => {
    unlinkTarget.value = {
        type: 'employee',
        id: emp.id,
        name: emp.fullName,
    };
    isConfirmUnlinkOpen.value = true;
};

const requestUnlinkEquipment = (eq: DepartmentEquipment) => {
    unlinkTarget.value = {
        type: 'equipment',
        id: eq.id,
        name: `${eq.codigo} - ${eq.nombre}`,
    };
    isConfirmUnlinkOpen.value = true;
};

const handleConfirmUnlink = () => {
    if (!unlinkTarget.value) return;

    const { type, id } = unlinkTarget.value;
    const url = type === 'employee'
        ? `/departamentos/${props.department.id}/empleados/${id}`
        : `/departamentos/${props.department.id}/equipos/${id}`;

    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            isConfirmUnlinkOpen.value = false;
            unlinkTarget.value = null;
        },
    });
};

// Formatting helpers
const formatCurrency = (val: number): string => {
    return '$' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getEquipmentBadgeVariant = (estado: string): 'success' | 'info' | 'warning' | 'danger' | 'neutral' => {
    switch (estado.toLowerCase()) {
        case 'disponible':
            return 'success';
        case 'asignado':
            return 'info';
        case 'en_reparacion':
        case 'en reparacion':
            return 'warning';
        case 'de_baja':
        case 'de baja':
            return 'danger';
        default:
            return 'neutral';
    }
};

const getProgressWidthClass = (percent: number): string => {
    const pct = Math.min(100, Math.max(0, percent));
    const rounded = Math.round(pct / 5) * 5;
    return `progress-pct-${rounded}`;
};
</script>

<template>
    <AppLayout :title="`Departamento: ${department.nombre}`">
        <Head :title="`Departamento - ${department.nombre}`" />

        <div class="dept-show-container">
            <!-- Header Bar -->
            <header class="dept-page-header">
                <div class="dept-header-left">
                    <Link href="/departamentos" class="dept-back-link">
                        <svg class="header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Volver a Departamentos</span>
                    </Link>

                    <div class="dept-title-row">
                        <div class="dept-icon-box">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="2"></line>
                                <line x1="15" y1="22" x2="15" y2="2"></line>
                                <line x1="8" y1="6" x2="16" y2="6"></line>
                                <line x1="8" y1="10" x2="16" y2="10"></line>
                                <line x1="8" y1="14" x2="16" y2="14"></line>
                            </svg>
                        </div>
                        <div class="dept-title-text-group">
                            <div class="dept-headline-tags">
                                <h1 class="dept-main-title">{{ department.nombre }}</h1>
                                <BaseBadge variant="code" size="md">{{ department.code }}</BaseBadge>
                            </div>
                            <div class="dept-meta-info">
                                <span class="dept-location-pill">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>{{ department.ubicacion || 'Sin ubicación física' }}</span>
                                </span>
                                <span v-if="department.createdAt" class="dept-created-date">
                                    Registrado el {{ department.createdAt }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dept-header-actions">
                    <BaseButton variant="subtle" size="md" @click="openEditModal">
                        <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Editar Info</span>
                    </BaseButton>
                    <BaseButton variant="primary" size="md" @click="openAssignEmployeeModal">
                        <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Asignar Recurso</span>
                    </BaseButton>
                </div>
            </header>

            <!-- 4 KPI Cards Row -->
            <section class="dept-kpi-grid" aria-label="Indicadores del departamento">
                <BaseKpiCard
                    label="PERSONAL ASIGNADO"
                    :value="department.empleadosCount"
                    subtext="Colaboradores activos"
                    icon="fa-solid fa-users"
                    color="blue"
                    :active="activeTab === 'employees'"
                    clickable
                    @click="setActiveTab('employees')"
                />
                <BaseKpiCard
                    label="EQUIPOS REGISTRADOS"
                    :value="department.equiposCount"
                    subtext="Activos tecnológicos"
                    icon="fa-solid fa-laptop"
                    color="green"
                    :active="activeTab === 'assets'"
                    clickable
                    @click="setActiveTab('assets')"
                />
                <BaseKpiCard
                    label="TICKETS ACTIVOS"
                    :value="department.ticketsCount"
                    subtext="Soporte pendiente"
                    icon="fa-solid fa-ticket"
                    color="yellow"
                />
                <BaseKpiCard
                    label="SUMINISTROS / VALOR"
                    :value="department.consumablesCount"
                    :subtext="department.consumablesValue > 0 ? formatCurrency(department.consumablesValue) : 'Sin valor asignado'"
                    icon="fa-solid fa-boxes-stacked"
                    color="purple"
                    :active="activeTab === 'inventory'"
                    clickable
                    @click="setActiveTab('inventory')"
                />
            </section>

            <!-- Navigation Tabs & Content Box -->
            <div class="dept-main-panel">
                <!-- Navigation Tabs Bar -->
                <nav class="dept-tabs-nav" aria-label="Pestañas de detalle">
                    <button
                        type="button"
                        class="dept-tab-button"
                        :class="{ 'is-active': activeTab === 'overview' }"
                        @click="setActiveTab('overview')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Visión General</span>
                    </button>

                    <button
                        type="button"
                        class="dept-tab-button"
                        :class="{ 'is-active': activeTab === 'employees' }"
                        @click="setActiveTab('employees')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Personal</span>
                        <span class="dept-tab-counter">{{ department.empleadosCount }}</span>
                    </button>

                    <button
                        type="button"
                        class="dept-tab-button"
                        :class="{ 'is-active': activeTab === 'assets' }"
                        @click="setActiveTab('assets')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>Equipos</span>
                        <span class="dept-tab-counter">{{ department.equiposCount }}</span>
                    </button>

                    <button
                        type="button"
                        class="dept-tab-button"
                        :class="{ 'is-active': activeTab === 'inventory' }"
                        @click="setActiveTab('inventory')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span>Stock e Insumos</span>
                        <span class="dept-tab-counter">{{ department.consumablesCount }}</span>
                    </button>
                </nav>

                <!-- TAB PANELS -->
                <div class="dept-tabs-body">
                    <!-- Panel 1: Visión General -->
                    <div v-if="activeTab === 'overview'" class="tab-panel-overview">
                        <div class="overview-grid">
                            <!-- Card: Jefe de Departamento -->
                            <BaseCard title="Responsable de Área" variant="glass" padding="md">
                                <template #actions>
                                    <BaseBadge variant="accent" size="sm">Liderazgo</BaseBadge>
                                </template>

                                <div v-if="department.jefeAreaNombre" class="manager-card-content">
                                    <div class="manager-profile-row">
                                        <div class="manager-avatar-circle">
                                            {{ department.jefeInitials }}
                                        </div>
                                        <div class="manager-info-col">
                                            <h4 class="manager-name">{{ department.jefeAreaNombre }}</h4>
                                            <p class="manager-role">{{ department.jefeCargo || 'Jefe de Departamento' }}</p>
                                        </div>
                                    </div>

                                    <div class="manager-contact-list">
                                        <div class="contact-pill-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                <polyline points="22,6 12,13 2,6"></polyline>
                                            </svg>
                                            <span>{{ department.jefeEmail || 'Sin correo registrado' }}</span>
                                        </div>
                                        <div class="contact-pill-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>
                                            <span>{{ department.jefeTelefono || 'Sin teléfono registrado' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="unassigned-manager-box">
                                    <div class="unassigned-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                    </div>
                                    <p class="unassigned-title">Sin Jefe de Área Asignado</p>
                                    <p class="unassigned-subtitle">Este departamento no tiene actualmente un líder designado.</p>
                                    <BaseButton variant="subtle" size="sm" @click="openEditModal">
                                        Asignar Responsable
                                    </BaseButton>
                                </div>
                            </BaseCard>

                            <!-- Card: Detalles Generales -->
                            <BaseCard title="Detalles del Departamento" variant="glass" padding="md">
                                <div class="dept-details-body">
                                    <div class="detail-section">
                                        <span class="detail-label">DESCRIPCIÓN OPERATIVA</span>
                                        <p class="detail-text">{{ department.descripcion || 'Sin descripción detallada disponible.' }}</p>
                                    </div>

                                    <div class="detail-metrics-row">
                                        <div class="detail-metric-col">
                                            <span class="detail-label">UBICACIÓN</span>
                                            <span class="detail-val">{{ department.ubicacion || 'Sin asignar' }}</span>
                                        </div>
                                        <div class="detail-metric-col">
                                            <span class="detail-label">NIVEL DE OCUPACIÓN</span>
                                            <div class="progress-track-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" :class="getProgressWidthClass(department.inventoryPercent)"></div>
                                                </div>
                                                <span class="progress-pct-text">{{ department.inventoryPercent }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </BaseCard>
                        </div>
                    </div>

                    <!-- Panel 2: Personal Asignado -->
                    <div v-else-if="activeTab === 'employees'" class="tab-panel-employees">
                        <BaseSearchToolbar
                            v-model="employeeSearch"
                            placeholder="Buscar colaboradores por nombre, cargo, email o cédula..."
                        >
                            <BaseButton variant="primary" size="md" @click="openAssignEmployeeModal">
                                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                                <span>Vincular Colaborador</span>
                            </BaseButton>
                        </BaseSearchToolbar>

                        <div v-if="filteredEmployees.length > 0" class="table-card">
                            <div class="table-responsive">
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>COLABORADOR</th>
                                            <th>CÉDULA</th>
                                            <th>CARGO</th>
                                            <th>CONTACTO</th>
                                            <th>ROL</th>
                                            <th class="text-right">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="emp in filteredEmployees" :key="emp.id">
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar-mini">{{ emp.initials }}</div>
                                                    <div class="user-info">
                                                        <span class="user-fullname">{{ emp.fullName }}</span>
                                                        <span class="user-subtext">{{ emp.email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="mono-code">{{ emp.cedula || 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="role-badge">{{ emp.cargo }}</span>
                                            </td>
                                            <td>
                                                <div class="contact-subinfo">
                                                    <span>{{ emp.telefono || 'Sin teléfono' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <BaseBadge variant="neutral" size="sm">{{ emp.rol }}</BaseBadge>
                                            </td>
                                            <td class="text-right">
                                                <div class="table-actions">
                                                    <Link :href="`/personal/${emp.id}`" class="action-btn-circle" title="Ver Perfil">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        class="action-btn-circle action-danger"
                                                        title="Desvincular del departamento"
                                                        @click="requestUnlinkEmployee(emp)"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <BaseEmptyState
                            v-else
                            title="No se encontraron colaboradores"
                            description="No hay personal asignado a este departamento que coincida con el criterio de búsqueda."
                        >
                            <BaseButton variant="primary" size="md" @click="openAssignEmployeeModal">
                                Vincular Primer Colaborador
                            </BaseButton>
                        </BaseEmptyState>
                    </div>

                    <!-- Panel 3: Equipos Asignados -->
                    <div v-else-if="activeTab === 'assets'" class="tab-panel-assets">
                        <BaseSearchToolbar
                            v-model="assetSearch"
                            placeholder="Buscar equipos por código, modelo, tipo o custodio..."
                        >
                            <BaseButton variant="primary" size="md" @click="openAssignEquipmentModal">
                                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Asignar Equipo</span>
                            </BaseButton>
                        </BaseSearchToolbar>

                        <div v-if="filteredAssets.length > 0" class="table-card">
                            <div class="table-responsive">
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>CÓDIGO ACTIVO</th>
                                            <th>DESCRIPCIÓN / MODELO</th>
                                            <th>TIPO</th>
                                            <th>CUSTODIO</th>
                                            <th>ESTADO</th>
                                            <th>UBICACIÓN</th>
                                            <th class="text-right">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="eq in filteredAssets" :key="eq.id">
                                            <td>
                                                <BaseBadge variant="code" size="md">{{ eq.codigo }}</BaseBadge>
                                            </td>
                                            <td>
                                                <div class="asset-info-cell">
                                                    <strong class="asset-title">{{ eq.nombre }}</strong>
                                                    <span v-if="eq.numeroSerie" class="asset-sn">S/N: {{ eq.numeroSerie }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="role-badge">{{ eq.tipo }}</span>
                                            </td>
                                            <td>
                                                <span class="custodio-text">{{ eq.custodio }}</span>
                                            </td>
                                            <td>
                                                <BaseBadge :variant="getEquipmentBadgeVariant(eq.estado)" size="sm">
                                                    {{ eq.estado }}
                                                </BaseBadge>
                                            </td>
                                            <td class="text-muted-cell">
                                                {{ eq.ubicacion || 'Área general' }}
                                            </td>
                                            <td class="text-right">
                                                <div class="table-actions">
                                                    <Link :href="`/equipos/${eq.id}`" class="action-btn-circle" title="Ver Ficha de Equipo">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        class="action-btn-circle action-danger"
                                                        title="Desvincular equipo"
                                                        @click="requestUnlinkEquipment(eq)"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <BaseEmptyState
                            v-else
                            title="No se encontraron equipos"
                            description="No hay equipos vinculados a este departamento que coincidan con la búsqueda."
                        >
                            <BaseButton variant="primary" size="md" @click="openAssignEquipmentModal">
                                Asignar Primer Equipo
                            </BaseButton>
                        </BaseEmptyState>
                    </div>

                    <!-- Panel 4: Stock e Insumos -->
                    <div v-else-if="activeTab === 'inventory'" class="tab-panel-inventory">
                        <BaseSearchToolbar
                            v-model="consumableSearch"
                            placeholder="Buscar insumos por código o nombre..."
                        >
                            <template #prepend>
                                <div class="category-filter-select">
                                    <select v-model="consumableCategory" class="filter-native-select">
                                        <option value="all">Todas las Categorías</option>
                                        <option v-for="cat in availableCategories" :key="cat" :value="cat">
                                            {{ cat }}
                                        </option>
                                    </select>
                                </div>
                            </template>
                        </BaseSearchToolbar>

                        <div v-if="filteredConsumables.length > 0" class="table-card">
                            <div class="table-responsive">
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>CÓDIGO</th>
                                            <th>INSUMO / ARTÍCULO</th>
                                            <th>CATEGORÍA</th>
                                            <th class="text-center">STOCK ASIGNADO</th>
                                            <th class="text-center">STOCK MÍNIMO</th>
                                            <th>ESTADO</th>
                                            <th class="text-right">VALOR TOTAL</th>
                                            <th class="text-right">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in filteredConsumables" :key="item.id">
                                            <td>
                                                <BaseBadge variant="code" size="md">{{ item.codigo }}</BaseBadge>
                                            </td>
                                            <td>
                                                <strong class="item-name">{{ item.nombre }}</strong>
                                            </td>
                                            <td>
                                                <span class="role-badge">{{ item.categoria }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="stock-pill">{{ item.stock }} {{ item.unidad_medida }}</span>
                                            </td>
                                            <td class="text-center text-muted-cell">
                                                {{ item.stock_minimo }} {{ item.unidad_medida }}
                                            </td>
                                            <td>
                                                <BaseBadge :variant="item.is_low_stock ? 'danger' : 'success'" size="sm" dot>
                                                    {{ item.is_low_stock ? 'Bajo Stock' : 'Disponible' }}
                                                </BaseBadge>
                                            </td>
                                            <td class="text-right">
                                                <span class="currency-text">{{ formatCurrency(item.valor_total) }}</span>
                                            </td>
                                            <td class="text-right">
                                                <Link :href="`/inventario/${item.id}`" class="action-btn-circle" title="Ver en Inventario">
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
                            title="No hay insumos asignados"
                            description="Este departamento no tiene consumibles o artículos de inventario registrados actualmente."
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Vincular Colaborador -->
        <BaseModal
            :is-open="isAssignEmployeeOpen"
            title="Vincular Colaborador al Departamento"
            max-width="md"
            @close="isAssignEmployeeOpen = false"
        >
            <form @submit.prevent="handleAssignEmployee" class="modal-form-stack">
                <div class="form-group">
                    <label class="form-label" for="select-assign-emp">Seleccionar Colaborador Disponible</label>
                    <select
                        id="select-assign-emp"
                        v-model="selectedEmployeeId"
                        class="form-control-select"
                        required
                    >
                        <option value="" disabled>Seleccione un colaborador...</option>
                        <option
                            v-for="cand in department.candidatosEmpleados"
                            :key="cand.id"
                            :value="cand.id"
                        >
                            {{ cand.nombre }} ({{ cand.cargo }})
                        </option>
                    </select>
                    <p class="form-help">
                        El colaborador seleccionado pasará a pertenecer oficialmente al departamento {{ department.nombre }}.
                    </p>
                </div>

                <div class="modal-actions-bar">
                    <BaseButton type="button" variant="subtle" size="md" @click="isAssignEmployeeOpen = false">
                        Cancelar
                    </BaseButton>
                    <BaseButton type="submit" variant="primary" size="md" :disabled="!selectedEmployeeId">
                        Confirmar Asignación
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- MODAL: Asignar Equipo -->
        <BaseModal
            :is-open="isAssignEquipmentOpen"
            title="Asignar Equipo al Departamento"
            max-width="md"
            @close="isAssignEquipmentOpen = false"
        >
            <form @submit.prevent="handleAssignEquipment" class="modal-form-stack">
                <div class="form-group">
                    <label class="form-label" for="select-assign-eq">Seleccionar Equipo Disponible</label>
                    <select
                        id="select-assign-eq"
                        v-model="selectedEquipmentId"
                        class="form-control-select"
                        required
                    >
                        <option value="" disabled>Seleccione un equipo...</option>
                        <option
                            v-for="cand in department.candidatosEquipos"
                            :key="cand.id"
                            :value="cand.id"
                        >
                            [{{ cand.codigo }}] {{ cand.nombre }} ({{ cand.estado }})
                        </option>
                    </select>
                    <p class="form-help">
                        El equipo quedará registrado bajo la custodia física del departamento {{ department.nombre }}.
                    </p>
                </div>

                <div class="modal-actions-bar">
                    <BaseButton type="button" variant="subtle" size="md" @click="isAssignEquipmentOpen = false">
                        Cancelar
                    </BaseButton>
                    <BaseButton type="submit" variant="primary" size="md" :disabled="!selectedEquipmentId">
                        Asignar Equipo
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- MODAL: Editar Información del Departamento -->
        <BaseModal
            :is-open="isEditModalOpen"
            title="Editar Información del Departamento"
            max-width="lg"
            @close="isEditModalOpen = false"
        >
            <form @submit.prevent="handleSaveDepartment" class="modal-form-stack">
                <div class="form-group">
                    <BaseInput
                        v-model="editForm.nombre"
                        label="Nombre del Departamento *"
                        placeholder="Ej. Tecnología y Sistemas"
                        required
                    />
                </div>

                <div class="form-group">
                    <BaseInput
                        v-model="editForm.ubicacion"
                        label="Ubicación Física"
                        placeholder="Ej. Torre Corporativa, Piso 4"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label" for="select-edit-jefe">Jefe / Responsable del Área</label>
                    <select
                        id="select-edit-jefe"
                        v-model="editForm.jefe_area_id"
                        class="form-control-select"
                    >
                        <option value="">Sin jefe asignado</option>
                        <option
                            v-for="emp in department.empleados"
                            :key="emp.id"
                            :value="emp.id"
                        >
                            {{ emp.fullName }} ({{ emp.cargo }})
                        </option>
                        <option
                            v-for="cand in department.candidatosEmpleados"
                            :key="cand.id"
                            :value="cand.id"
                        >
                            {{ cand.nombre }} ({{ cand.cargo }})
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <BaseTextarea
                        v-model="editForm.descripcion"
                        label="Descripción / Alcance"
                        placeholder="Describa las responsabilidades y funciones clave de esta área..."
                        :rows="4"
                    />
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

        <!-- MODAL: Confirmar Desvinculación -->
        <BaseConfirmModal
            :is-open="isConfirmUnlinkOpen"
            title="Confirmar Desvinculación"
            :message="`¿Está seguro de que desea desvincular a '${unlinkTarget?.name}' del departamento ${department.nombre}? El registro no se eliminará del sistema.`"
            confirm-text="Sí, desvincular"
            confirm-variant="danger"
            @confirm="handleConfirmUnlink"
            @close="isConfirmUnlinkOpen = false"
        />
    </AppLayout>
</template>

<style scoped>
/* 1. Base tokens & container */
.dept-show-container {
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
.dept-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.dept-header-left {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.dept-back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.dept-back-link:hover {
    color: var(--brand);
}

.header-icon-svg {
    width: 16px;
    height: 16px;
}

.dept-title-row {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.dept-icon-box {
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

.dept-icon-box svg {
    width: 26px;
    height: 26px;
}

.dept-title-text-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.dept-headline-tags {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.dept-main-title {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--text);
    margin: 0;
}

.dept-meta-info {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    font-size: 13px;
    color: var(--text-muted);
}

.dept-location-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
}

.dept-location-pill svg {
    width: 14px;
    height: 14px;
    color: var(--brand);
}

.dept-created-date {
    color: var(--text-dim);
}

.dept-header-actions {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
}

/* 3. KPI Grid */
.dept-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: var(--space-4);
}

/* 4. Main Panel */
.dept-main-panel {
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--card-radius);
    overflow: hidden;
    box-shadow: none !important;
}

/* Navigation Tabs */
.dept-tabs-nav {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-2) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
    overflow-x: auto;
}

.dept-tab-button {
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

.dept-tab-button svg {
    width: 16px;
    height: 16px;
}

.dept-tab-button:hover {
    color: var(--text);
}

.dept-tab-button.is-active {
    color: var(--brand);
    border-bottom-color: var(--brand);
}

.dept-tab-counter {
    background: var(--stroke);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
}

.dept-tab-button.is-active .dept-tab-counter {
    background: rgba(var(--brand-rgb), 0.2);
    color: var(--brand);
}

.dept-tabs-body {
    padding: var(--space-6);
}

/* 5. Overview Grid */
.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: var(--space-5);
}

.manager-card-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

.manager-profile-row {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.manager-avatar-circle {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-pill);
    background: linear-gradient(135deg, rgba(var(--brand-rgb), 0.3), rgba(var(--brand-rgb), 0.05));
    border: 2px solid var(--brand);
    color: var(--brand);
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.manager-info-col {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.manager-name {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
}

.manager-role {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
}

.manager-contact-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.contact-pill-item {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    background: var(--bg-sub);
    border: 1px solid var(--stroke-subtle);
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--text-muted);
}

.contact-pill-item svg {
    width: 14px;
    height: 14px;
    color: var(--text-dim);
}

.unassigned-manager-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--space-6) var(--space-4);
    gap: var(--space-3);
}

.unassigned-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-pill);
    background: var(--bg-sub);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
}

.unassigned-icon svg {
    width: 24px;
    height: 24px;
}

.unassigned-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
}

.unassigned-subtitle {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
    max-width: 280px;
}

/* Detail Metrics */
.dept-details-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

.detail-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.detail-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.detail-text {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-muted);
}

.detail-metrics-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
    padding-top: var(--space-3);
    border-top: 1px solid var(--stroke-subtle);
}

.detail-metric-col {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.detail-val {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.progress-track-container {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.progress-track {
    flex: 1;
    height: 8px;
    background: var(--stroke);
    border-radius: var(--radius-pill);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--brand);
    border-radius: var(--radius-pill);
    transition: width 0.3s ease;
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

.progress-pct-text {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
}

/* 6. Tables Layout */
.table-card {
    background: var(--bg-card);
    border: 1px solid var(--stroke-subtle);
    border-radius: var(--radius-md);
    overflow: hidden;
    margin-top: var(--space-4);
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

.text-center {
    text-align: center;
}

.text-muted-cell {
    color: var(--text-muted);
}

/* User cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.user-avatar-mini {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-pill);
    background: var(--bg-sub);
    border: 1px solid var(--stroke);
    color: var(--text);
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-fullname {
    font-weight: 700;
    color: var(--text);
}

.user-subtext {
    font-size: 11px;
    color: var(--text-dim);
}

.mono-code {
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--text-muted);
}

.role-badge {
    display: inline-block;
    padding: 3px 8px;
    background: var(--bg-sub);
    border-radius: var(--radius-sm);
    font-size: 12px;
    color: var(--text-muted);
}

.contact-subinfo {
    display: flex;
    flex-direction: column;
    font-size: 12px;
    color: var(--text-muted);
}

.asset-info-cell {
    display: flex;
    flex-direction: column;
}

.asset-title {
    font-weight: 700;
    color: var(--text);
}

.asset-sn {
    font-size: 11px;
    font-family: var(--font-mono);
    color: var(--text-dim);
}

.custodio-text {
    font-weight: 600;
    color: var(--text-muted);
}

.stock-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: var(--radius-sm);
    background: var(--bg-sub);
    font-weight: 700;
    font-size: 12px;
    color: var(--text);
}

.currency-text {
    font-weight: 700;
    font-family: var(--font-mono);
    color: var(--text);
}

/* Actions in Table */
.table-actions {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
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

.action-btn-circle.action-danger:hover {
    color: var(--red);
    border-color: var(--red);
    background: rgba(220, 38, 38, 0.1);
}

/* Filter native select in Toolbar */
.category-filter-select {
    display: inline-flex;
}

.filter-native-select {
    height: 38px;
    padding: 0 var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    cursor: pointer;
}

/* Modal Form Styles */
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
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

.form-help {
    margin: 0;
    font-size: 12px;
    color: var(--text-dim);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}
</style>
