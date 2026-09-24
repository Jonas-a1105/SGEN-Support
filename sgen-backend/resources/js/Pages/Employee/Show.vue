<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    BaseButton,
    BaseBadge,
    BaseCard,
    BaseKpiCard,
    BaseModal,
    BaseInput,
    BaseEmptyState,
} from '@/Components/UI';

export interface AssignedEquipment {
    id: number;
    codigo: string;
    numeroSerie: string;
    nombre: string;
    tipo: string;
    marca: string;
    modelo: string;
    estado: string;
    ubicacion: string;
}

export interface EmployeeTicket {
    id: number;
    titulo: string;
    descripcion: string;
    estado: string;
    prioridad: string;
    fecha: string | null;
}

export interface DepartmentOption {
    id: number;
    nombre: string;
}

export interface EmployeeDetail {
    id: number;
    formattedId: string;
    nombre: string;
    apellido: string;
    fullName: string;
    email: string;
    cedula: string | null;
    cargo: string | null;
    telefono: string | null;
    departamentoId: number | null;
    departamentoNombre: string | null;
    rol: string;
    usuarioId: number | null;
    username: string | null;
    initials: string;
    tint: string;
    equipos: AssignedEquipment[];
    tickets: EmployeeTicket[];
    departamentos: DepartmentOption[];
    equiposCount: number;
    ticketsCount: number;
    resolvedTicketsCount: number;
}

const props = defineProps<{
    employee: EmployeeDetail;
}>();

// Navigation Tabs
type TabKey = 'overview' | 'assets' | 'tickets';
const activeTab = ref<TabKey>('overview');

const setActiveTab = (tab: TabKey) => {
    activeTab.value = tab;
};

// Edit Modal
const isEditModalOpen = ref(false);
const editForm = ref({
    nombre: props.employee.nombre,
    apellido: props.employee.apellido,
    email: props.employee.email,
    cedula: props.employee.cedula ?? '',
    cargo: props.employee.cargo ?? '',
    telefono: props.employee.telefono ?? '',
    departamento_id: props.employee.departamentoId ?? '',
    rol: props.employee.rol,
});

const openEditModal = () => {
    editForm.value = {
        nombre: props.employee.nombre,
        apellido: props.employee.apellido,
        email: props.employee.email,
        cedula: props.employee.cedula ?? '',
        cargo: props.employee.cargo ?? '',
        telefono: props.employee.telefono ?? '',
        departamento_id: props.employee.departamentoId ?? '',
        rol: props.employee.rol,
    };
    isEditModalOpen.value = true;
};

const handleSaveEdit = () => {
    router.put(
        `/personal/${props.employee.id}`,
        {
            nombre: editForm.value.nombre,
            apellido: editForm.value.apellido,
            email: editForm.value.email,
            cedula: editForm.value.cedula || null,
            cargo: editForm.value.cargo || null,
            telefono: editForm.value.telefono || null,
            departamento_id: editForm.value.departamento_id || null,
            rol: editForm.value.rol,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditModalOpen.value = false;
            },
        }
    );
};

// Helpers
const getRoleBadgeVariant = (rol: string): 'accent' | 'success' | 'info' | 'neutral' => {
    switch (rol.toLowerCase()) {
        case 'administrador':
            return 'accent';
        case 'tecnico':
            return 'success';
        case 'consultor':
            return 'info';
        default:
            return 'neutral';
    }
};

const getEquipmentBadgeVariant = (estado: string): 'success' | 'info' | 'warning' | 'danger' | 'neutral' => {
    switch (estado.toLowerCase()) {
        case 'disponible':
            return 'success';
        case 'asignado':
        case 'en_uso':
            return 'info';
        case 'en_reparacion':
            return 'warning';
        case 'de_baja':
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
</script>

<template>
    <AppLayout :title="`Colaborador: ${employee.fullName}`">
        <Head :title="`Colaborador - ${employee.fullName}`" />

        <div class="emp-show-container">
            <!-- Header Bar -->
            <header class="emp-page-header">
                <div class="emp-header-left">
                    <Link href="/personal" class="emp-back-link">
                        <svg class="header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Volver al Directorio de Personal</span>
                    </Link>

                    <div class="emp-title-row">
                        <div class="emp-avatar-circle">
                            {{ employee.initials }}
                        </div>
                        <div class="emp-title-text-group">
                            <div class="emp-headline-tags">
                                <h1 class="emp-main-title">{{ employee.fullName }}</h1>
                                <BaseBadge variant="code" size="md">{{ employee.formattedId }}</BaseBadge>
                                <BaseBadge :variant="getRoleBadgeVariant(employee.rol)" size="md">
                                    {{ employee.rol }}
                                </BaseBadge>
                            </div>
                            <div class="emp-meta-info">
                                <span class="emp-cargo-pill">{{ employee.cargo || 'Sin cargo asignado' }}</span>
                                <span v-if="employee.cedula" class="emp-cedula-pill">
                                    Cédula: {{ employee.cedula }}
                                </span>
                                <span v-if="employee.departamentoNombre" class="emp-dept-pill">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                        <line x1="9" y1="22" x2="9" y2="2"></line>
                                        <line x1="15" y1="22" x2="15" y2="2"></line>
                                    </svg>
                                    {{ employee.departamentoNombre }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="emp-header-actions">
                    <BaseButton variant="subtle" size="md" @click="openEditModal">
                        <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Editar Información</span>
                    </BaseButton>
                    <Link :href="`/soportes/crear?empleado_id=${employee.id}`">
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
            <section class="emp-kpi-grid" aria-label="Métricas del colaborador">
                <BaseKpiCard
                    label="EQUIPOS EN CUSTODIA"
                    :value="employee.equiposCount"
                    subtext="Activos asignados"
                    icon="fa-solid fa-laptop"
                    color="green"
                    :active="activeTab === 'assets'"
                    clickable
                    @click="setActiveTab('assets')"
                />
                <BaseKpiCard
                    label="TICKETS REPORTADOS"
                    :value="employee.ticketsCount"
                    subtext="Incidencias generadas"
                    icon="fa-solid fa-ticket"
                    color="blue"
                    :active="activeTab === 'tickets'"
                    clickable
                    @click="setActiveTab('tickets')"
                />
                <BaseKpiCard
                    label="INCIDENCIAS RESUELTAS"
                    :value="employee.resolvedTicketsCount"
                    subtext="Atendidas exitosamente"
                    icon="fa-solid fa-check-double"
                    color="purple"
                />
                <BaseKpiCard
                    label="ROL EN SISTEMA"
                    :value="employee.rol.toUpperCase()"
                    :subtext="employee.username ? 'Usuario: @' + employee.username : 'Sin cuenta activa'"
                    icon="fa-solid fa-shield-halved"
                    color="yellow"
                />
            </section>

            <!-- 2-Column Content Layout -->
            <div class="emp-content-layout">
                <!-- Left Sidebar: Contact & Identity -->
                <div class="emp-sidebar-col">
                    <BaseCard title="Datos de Contacto" variant="glass" padding="md">
                        <div class="contact-card-stack">
                            <div class="contact-row">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="contact-meta">
                                    <span class="contact-label">CORREO ELECTRÓNICO</span>
                                    <a :href="`mailto:${employee.email}`" class="contact-val email-link">
                                        {{ employee.email }}
                                    </a>
                                </div>
                            </div>

                            <div class="contact-row">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </div>
                                <div class="contact-meta">
                                    <span class="contact-label">TELÉFONO</span>
                                    <span class="contact-val">{{ employee.telefono || 'Sin teléfono registrado' }}</span>
                                </div>
                            </div>

                            <div class="contact-row">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                        <line x1="9" y1="22" x2="9" y2="2"></line>
                                        <line x1="15" y1="22" x2="15" y2="2"></line>
                                    </svg>
                                </div>
                                <div class="contact-meta">
                                    <span class="contact-label">DEPARTAMENTO</span>
                                    <Link
                                        v-if="employee.departamentoId"
                                        :href="`/departamentos/${employee.departamentoId}`"
                                        class="contact-val dept-link"
                                    >
                                        {{ employee.departamentoNombre }}
                                    </Link>
                                    <span v-else class="contact-val muted">Sin departamento asignado</span>
                                </div>
                            </div>

                            <div class="contact-row">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="contact-meta">
                                    <span class="contact-label">USUARIO DE SISTEMA</span>
                                    <span class="contact-val mono">
                                        {{ employee.username ? `@${employee.username}` : 'No vinculado a usuario' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </BaseCard>
                </div>

                <!-- Right Main Column: Tabs Panel -->
                <div class="emp-main-col">
                    <div class="emp-panel-wrapper">
                        <!-- Navigation Tabs Bar -->
                        <nav class="emp-tabs-nav" aria-label="Pestañas de colaborador">
                            <button
                                type="button"
                                class="emp-tab-button"
                                :class="{ 'is-active': activeTab === 'overview' }"
                                @click="setActiveTab('overview')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span>Información General</span>
                            </button>

                            <button
                                type="button"
                                class="emp-tab-button"
                                :class="{ 'is-active': activeTab === 'assets' }"
                                @click="setActiveTab('assets')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                                <span>Equipos en Custodia</span>
                                <span v-if="employee.equipos.length > 0" class="emp-tab-counter">
                                    {{ employee.equipos.length }}
                                </span>
                            </button>

                            <button
                                type="button"
                                class="emp-tab-button"
                                :class="{ 'is-active': activeTab === 'tickets' }"
                                @click="setActiveTab('tickets')"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span>Tickets de Soporte</span>
                                <span v-if="employee.tickets.length > 0" class="emp-tab-counter">
                                    {{ employee.tickets.length }}
                                </span>
                            </button>
                        </nav>

                        <!-- Tab Panels -->
                        <div class="emp-tab-body">
                            <!-- Panel 1: Overview -->
                            <div v-if="activeTab === 'overview'" class="tab-panel-overview">
                                <div class="overview-section">
                                    <h4 class="overview-subheading">Resumen del Perfil</h4>
                                    <div class="overview-fields-grid">
                                        <div class="ov-field-card">
                                            <span class="ov-label">Nombre Completo</span>
                                            <strong class="ov-value">{{ employee.fullName }}</strong>
                                        </div>
                                        <div class="ov-field-card">
                                            <span class="ov-label">Cédula de Identidad</span>
                                            <strong class="ov-value mono">{{ employee.cedula || 'No registrada' }}</strong>
                                        </div>
                                        <div class="ov-field-card">
                                            <span class="ov-label">Cargo Laboral</span>
                                            <strong class="ov-value">{{ employee.cargo || 'Personal General' }}</strong>
                                        </div>
                                        <div class="ov-field-card">
                                            <span class="ov-label">Departamento</span>
                                            <strong class="ov-value">{{ employee.departamentoNombre || 'Sin departamento' }}</strong>
                                        </div>
                                        <div class="ov-field-card">
                                            <span class="ov-label">Rol en la Plataforma</span>
                                            <strong class="ov-value capitalize">{{ employee.rol }}</strong>
                                        </div>
                                        <div class="ov-field-card">
                                            <span class="ov-label">Cuenta de Usuario</span>
                                            <strong class="ov-value mono">{{ employee.username ? `@${employee.username}` : 'No asignada' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel 2: Equipos en Custodia -->
                            <div v-else-if="activeTab === 'assets'" class="tab-panel-assets">
                                <div v-if="employee.equipos.length > 0" class="table-card">
                                    <div class="table-responsive">
                                        <table class="detail-table">
                                            <thead>
                                                <tr>
                                                    <th>CÓDIGO</th>
                                                    <th>EQUIPO / MODELO</th>
                                                    <th>TIPO</th>
                                                    <th>SERIAL</th>
                                                    <th>ESTADO</th>
                                                    <th>UBICACIÓN</th>
                                                    <th class="text-right">ACCIÓN</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="eq in employee.equipos" :key="eq.id">
                                                    <td>
                                                        <BaseBadge variant="code" size="md">{{ eq.codigo }}</BaseBadge>
                                                    </td>
                                                    <td>
                                                        <strong class="item-name-text">{{ eq.nombre }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="role-badge">{{ eq.tipo }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="mono-text">{{ eq.numeroSerie || 'N/A' }}</span>
                                                    </td>
                                                    <td>
                                                        <BaseBadge :variant="getEquipmentBadgeVariant(eq.estado)" size="sm">
                                                            {{ eq.estado }}
                                                        </BaseBadge>
                                                    </td>
                                                    <td class="text-muted-cell">{{ eq.ubicacion }}</td>
                                                    <td class="text-right">
                                                        <Link :href="`/equipos/${eq.id}`" class="action-btn-circle" title="Ver Ficha de Equipo">
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
                                    title="Sin equipos en custodia"
                                    description="Este colaborador no tiene equipos o dispositivos tecnológicos asignados actualmente."
                                />
                            </div>

                            <!-- Panel 3: Tickets de Soporte -->
                            <div v-else-if="activeTab === 'tickets'" class="tab-panel-tickets">
                                <div v-if="employee.tickets.length > 0" class="table-card">
                                    <div class="table-responsive">
                                        <table class="detail-table">
                                            <thead>
                                                <tr>
                                                    <th>CÓDIGO</th>
                                                    <th>ASUNTO / INCIDENCIA</th>
                                                    <th>ESTADO</th>
                                                    <th>PRIORIDAD</th>
                                                    <th>FECHA</th>
                                                    <th class="text-right">ACCIÓN</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="t in employee.tickets" :key="t.id">
                                                    <td>
                                                        <BaseBadge variant="code" size="md">#{{ t.id }}</BaseBadge>
                                                    </td>
                                                    <td>
                                                        <div class="ticket-cell">
                                                            <strong class="ticket-subject">{{ t.titulo }}</strong>
                                                            <span class="ticket-snippet">{{ t.descripcion }}</span>
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
                                    title="Sin tickets asociados"
                                    description="No se registran solicitudes o incidencias de soporte creadas por este colaborador."
                                >
                                    <Link :href="`/soportes/crear?empleado_id=${employee.id}`">
                                        <BaseButton variant="primary" size="md">
                                            Crear Ticket para este Colaborador
                                        </BaseButton>
                                    </Link>
                                </BaseEmptyState>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Editar Colaborador -->
        <BaseModal
            :is-open="isEditModalOpen"
            title="Editar Información del Colaborador"
            max-width="lg"
            @close="isEditModalOpen = false"
        >
            <form @submit.prevent="handleSaveEdit" class="modal-form-stack">
                <div class="form-grid-2">
                    <BaseInput v-model="editForm.nombre" label="Nombre *" required />
                    <BaseInput v-model="editForm.apellido" label="Apellido *" required />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.email" type="email" label="Correo Electrónico *" required />
                    <BaseInput v-model="editForm.cedula" label="Cédula de Identidad" placeholder="V-12345678" />
                </div>

                <div class="form-grid-2">
                    <BaseInput v-model="editForm.cargo" label="Cargo / Posición" placeholder="Ej. Analista de Sistemas" />
                    <BaseInput v-model="editForm.telefono" label="Teléfono" placeholder="0414-1234567" />
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <select v-model="editForm.departamento_id" class="form-control-select">
                            <option value="">Sin departamento</option>
                            <option
                                v-for="d in employee.departamentos"
                                :key="d.id"
                                :value="d.id"
                            >
                                {{ d.nombre }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rol en Plataforma *</label>
                        <select v-model="editForm.rol" class="form-control-select" required>
                            <option value="tecnico">Técnico</option>
                            <option value="administrador">Administrador</option>
                            <option value="consultor">Consultor</option>
                        </select>
                    </div>
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
.emp-show-container {
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
.emp-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.emp-header-left {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.emp-back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.emp-back-link:hover {
    color: var(--brand);
}

.header-icon-svg {
    width: 16px;
    height: 16px;
}

.emp-title-row {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.emp-avatar-circle {
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

.emp-title-text-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.emp-headline-tags {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.emp-main-title {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--text);
    margin: 0;
}

.emp-meta-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: 13px;
    color: var(--text-muted);
    flex-wrap: wrap;
}

.emp-cargo-pill {
    font-weight: 600;
    color: var(--text);
}

.emp-cedula-pill {
    font-family: var(--font-mono);
    color: var(--text-dim);
}

.emp-dept-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    color: var(--brand);
    font-weight: 600;
}

.emp-dept-pill svg {
    width: 14px;
    height: 14px;
}

.emp-header-actions {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
}

/* 3. KPI Grid */
.emp-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: var(--space-4);
}

/* 4. Content Layout */
.emp-content-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: var(--space-6);
    align-items: start;
}

@media (max-width: 1024px) {
    .emp-content-layout {
        grid-template-columns: 1fr;
    }
}

.emp-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

.contact-card-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.contact-row {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
}

.contact-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--bg-sub);
    border: 1px solid var(--stroke-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand);
    flex-shrink: 0;
}

.contact-icon svg {
    width: 16px;
    height: 16px;
}

.contact-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.contact-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: var(--text-dim);
}

.contact-val {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.contact-val.email-link {
    color: var(--brand);
    text-decoration: none;
}

.contact-val.email-link:hover {
    text-decoration: underline;
}

.contact-val.dept-link {
    color: var(--text);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.contact-val.dept-link:hover {
    color: var(--brand);
}

.contact-val.mono {
    font-family: var(--font-mono);
}

.contact-val.muted {
    color: var(--text-muted);
}

/* Right Main Column */
.emp-panel-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--card-radius);
    overflow: hidden;
    box-shadow: none !important;
}

.emp-tabs-nav {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-2) var(--space-4);
    background: var(--bg-sub);
    border-bottom: 1px solid var(--stroke);
    overflow-x: auto;
}

.emp-tab-button {
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

.emp-tab-button svg {
    width: 16px;
    height: 16px;
}

.emp-tab-button:hover {
    color: var(--text);
}

.emp-tab-button.is-active {
    color: var(--brand);
    border-bottom-color: var(--brand);
}

.emp-tab-counter {
    background: var(--stroke);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
}

.emp-tab-button.is-active .emp-tab-counter {
    background: rgba(var(--brand-rgb), 0.2);
    color: var(--brand);
}

.emp-tab-body {
    padding: var(--space-6);
}

/* Overview Section */
.overview-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.overview-subheading {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.overview-fields-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--space-4);
}

.ov-field-card {
    background: var(--bg-sub);
    padding: var(--space-4);
    border-radius: var(--radius-md);
    border: 1px solid var(--stroke-subtle);
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.ov-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.ov-value {
    font-size: 14px;
    color: var(--text);
}

.ov-value.mono {
    font-family: var(--font-mono);
}

.ov-value.capitalize {
    text-transform: capitalize;
}

/* Tables */
.table-card {
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

.item-name-text {
    font-weight: 700;
    color: var(--text);
}

.role-badge {
    display: inline-block;
    padding: 3px 8px;
    background: var(--bg-sub);
    border-radius: var(--radius-sm);
    font-size: 12px;
    color: var(--text-muted);
}

.mono-text {
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--text-muted);
}

.ticket-cell {
    display: flex;
    flex-direction: column;
}

.ticket-subject {
    font-weight: 700;
    color: var(--text);
}

.ticket-snippet {
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
