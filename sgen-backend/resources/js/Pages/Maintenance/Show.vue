<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    BaseButton,
    BaseBadge,
    BaseCard,
    BaseKpiCard,
    BaseModal,
    BaseInput,
    BaseTextarea,
    BaseEmptyState,
} from '@/Components/UI';

export interface EquipmentInfo {
    id: number;
    codigo: string;
    tipo: string;
    marca: string;
    modelo: string;
    serial: string;
    estado: string;
    departamento: string;
    departamentoId: number | null;
    custodio: string;
    custodioId: number | null;
}

export interface RelatedMaintenance {
    id: number;
    fecha: string;
    tipoMantenimiento: string;
    estado: string;
    costo: number | null;
    descripcion: string;
    tecnicoNombre: string;
}

export interface MaintenanceDetail {
    id: number;
    equipoId: number;
    fecha: string;
    tipoMantenimiento: string;
    estado: string;
    descripcion: string;
    frecuencia: string;
    proximaFecha: string | null;
    costo: number | null;
    tecnicoId: number | null;
    tecnicoNombre: string | null;
    realizadoPor: string | null;
    observaciones: string | null;
    checklist: Array<string | { text?: string; checked?: boolean }> | null;
    duracion: number | null;
    createdAt: string | null;
    updatedAt: string | null;
    equipo: EquipmentInfo | null;
    historialEquipo: RelatedMaintenance[] | null;
    isOverdue: boolean;
}

export interface FormOptions {
    equipments: Array<{ id: number; code: string; type: string; model: string; department: string }>;
    technicians: Array<{ id: number; name: string; email: string }>;
    types: Array<{ value: string; label: string }>;
    frequencies: Array<{ value: string; label: string }>;
}

const props = defineProps<{
    maintenance: MaintenanceDetail;
    options?: FormOptions;
}>();

// Timer state
const currentTime = ref(Date.now());
let timerInterval: number | null = null;

onMounted(() => {
    timerInterval = window.setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval !== null) {
        clearInterval(timerInterval);
    }
});

// Format dates
const formatDate = (dateStr?: string | null): string => {
    if (!dateStr) return 'No especificada';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (dateStr?: string | null): string => {
    if (!dateStr) return 'No especificada';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (val?: number | null): string => {
    const num = Number(val ?? 0);
    return `$${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

// Timer calculation
const timerStatus = computed(() => {
    if (!props.maintenance.fecha) return null;
    const start = new Date(props.maintenance.fecha).getTime();
    if (isNaN(start)) return null;

    const durationMin = props.maintenance.duracion || 60;
    const end = start + durationMin * 60 * 1000;
    const now = currentTime.value;

    if (props.maintenance.estado === 'completado') {
        return { label: 'Completado', class: 'timer-completed', text: 'Servicio finalizado' };
    }
    if (props.maintenance.estado === 'cancelado') {
        return { label: 'Cancelado', class: 'timer-cancelled', text: 'Cancelado' };
    }

    if (now < start) {
        const diffSec = Math.floor((start - now) / 1000);
        const hours = Math.floor(diffSec / 3600);
        const minutes = Math.floor((diffSec % 3600) / 60);
        const seconds = diffSec % 60;
        return {
            label: 'Programado',
            class: 'timer-scheduled',
            text: `Inicia en ${hours}h ${minutes}m ${seconds}s`,
        };
    }

    if (now >= start && now <= end) {
        const diffSec = Math.floor((end - now) / 1000);
        const hours = Math.floor(diffSec / 3600);
        const minutes = Math.floor((diffSec % 3600) / 60);
        const seconds = diffSec % 60;
        return {
            label: 'En curso',
            class: 'timer-running',
            text: `Tiempo restante: ${hours}h ${minutes}m ${seconds}s`,
        };
    }

    return {
        label: 'Vencido',
        class: 'timer-overdue',
        text: 'Plazo expirado',
    };
});

// Badges
const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'completado': return 'success';
        case 'en_proceso': return 'info';
        case 'pendiente': return 'warning';
        case 'pospuesto': return 'neutral';
        case 'cancelado': return 'danger';
        default: return 'neutral';
    }
};

const getTypeBadgeVariant = (type: string) => {
    switch (type) {
        case 'preventivo': return 'brand';
        case 'correctivo': return 'warning';
        case 'predictivo': return 'info';
        default: return 'neutral';
    }
};

// Normalizing checklist items
const normalizedChecklist = computed(() => {
    if (!props.maintenance.checklist || !Array.isArray(props.maintenance.checklist)) {
        return [];
    }
    return props.maintenance.checklist.map((item, idx) => {
        if (typeof item === 'string') {
            return { id: idx, text: item, checked: false };
        }
        return { id: idx, text: item.text || `Actividad ${idx + 1}`, checked: !!item.checked };
    });
});

// Modals State
const isCompleteModalOpen = ref(false);
const isPostponeModalOpen = ref(false);
const isCancelModalOpen = ref(false);
const isEditModalOpen = ref(false);

const completeForm = ref({
    costo: props.maintenance.costo || 0,
    observaciones: props.maintenance.observaciones || '',
});

const postponeForm = ref({
    nueva_fecha: '',
});

const cancelForm = ref({
    motivo: '',
});

const editForm = ref({
    fecha: props.maintenance.fecha ? props.maintenance.fecha.substring(0, 16) : '',
    tipo_mantenimiento: props.maintenance.tipoMantenimiento,
    estado: props.maintenance.estado,
    descripcion: props.maintenance.descripcion,
    frecuencia: props.maintenance.frecuencia,
    proxima_fecha: props.maintenance.proximaFecha || '',
    costo: props.maintenance.costo || 0,
    tecnico_id: props.maintenance.tecnicoId || '',
    observaciones: props.maintenance.observaciones || '',
    duracion: props.maintenance.duracion || 60,
});

const isSubmitting = ref(false);

const handleComplete = () => {
    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenance.id}/completar`,
        {
            costo: completeForm.value.costo,
            observaciones: completeForm.value.observaciones,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
                isCompleteModalOpen.value = false;
            },
        }
    );
};

const handlePostpone = () => {
    if (!postponeForm.value.nueva_fecha) return;
    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenance.id}/posponer`,
        {
            nueva_fecha: postponeForm.value.nueva_fecha,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
                isPostponeModalOpen.value = false;
            },
        }
    );
};

const handleCancel = () => {
    if (!cancelForm.value.motivo) return;
    isSubmitting.value = true;
    router.post(
        `/mantenimientos/${props.maintenance.id}/cancelar`,
        {
            motivo: cancelForm.value.motivo,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
                isCancelModalOpen.value = false;
            },
        }
    );
};

const handleSaveEdit = () => {
    isSubmitting.value = true;
    router.put(
        `/mantenimientos/${props.maintenance.id}`,
        {
            fecha: editForm.value.fecha,
            tipo_mantenimiento: editForm.value.tipo_mantenimiento,
            estado: editForm.value.estado,
            descripcion: editForm.value.descripcion,
            frecuencia: editForm.value.frecuencia,
            proxima_fecha: editForm.value.proxima_fecha || null,
            costo: editForm.value.costo,
            tecnico_id: editForm.value.tecnico_id || null,
            observaciones: editForm.value.observaciones,
            duracion: editForm.value.duracion,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
                isEditModalOpen.value = false;
            },
        }
    );
};

const triggerPrint = () => {
    window.print();
};

const technicianInitials = computed(() => {
    const name = props.maintenance.tecnicoNombre || 'Tecnico';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
});
</script>

<template>
    <AppLayout :title="`Mantenimiento #${maintenance.id}`">
        <Head :title="`Mantenimiento #${maintenance.id} - ${maintenance.tipoMantenimiento}`" />

        <div class="maint-show-wrapper">
            <!-- Overdue Alert Banner -->
            <div v-if="maintenance.isOverdue" class="maint-alert-banner">
                <div class="maint-alert-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="maint-alert-content">
                    <strong class="maint-alert-title">Mantenimiento con Plazo Expirado</strong>
                    <span class="maint-alert-desc">
                        La fecha programada ({{ formatDateTime(maintenance.fecha) }}) ha concluido sin marcarse como completado.
                    </span>
                </div>
                <BaseButton
                    variant="primary"
                    size="sm"
                    @click="isCompleteModalOpen = true"
                >
                    Marcar Realizado
                </BaseButton>
            </div>

            <!-- Page Header -->
            <header class="maint-header">
                <div class="maint-header-left">
                    <Link href="/mantenimientos" class="maint-back-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Volver a Mantenimientos</span>
                    </Link>

                    <div class="maint-title-row">
                        <h1 class="maint-main-title">
                            Mantenimiento {{ maintenance.tipoMantenimiento }}
                        </h1>
                        <BaseBadge :variant="getStatusBadgeVariant(maintenance.estado)" size="md">
                            {{ maintenance.estado.replace('_', ' ') }}
                        </BaseBadge>
                        <BaseBadge :variant="getTypeBadgeVariant(maintenance.tipoMantenimiento)" size="md">
                            {{ maintenance.tipoMantenimiento }}
                        </BaseBadge>
                        <span class="maint-pill-duration">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            {{ maintenance.duracion || 60 }} min
                        </span>
                        <div v-if="timerStatus" class="maint-timer-chip" :class="timerStatus.class">
                            <span class="timer-dot"></span>
                            <span class="timer-text">{{ timerStatus.text }}</span>
                        </div>
                    </div>

                    <p class="maint-meta-sub">
                        ID: #{{ maintenance.id }} • Registrado el {{ formatDate(maintenance.createdAt || maintenance.fecha) }}
                    </p>
                </div>

                <div class="maint-header-actions">
                    <template v-if="maintenance.estado !== 'completado' && maintenance.estado !== 'cancelado'">
                        <button
                            type="button"
                            class="btn-action-complete"
                            @click="isCompleteModalOpen = true"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Marcar Realizado</span>
                        </button>

                        <button
                            type="button"
                            class="btn-action-postpone"
                            @click="isPostponeModalOpen = true"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 14 10"></polyline>
                            </svg>
                            <span>Posponer</span>
                        </button>

                        <button
                            type="button"
                            class="btn-action-cancel-task"
                            @click="isCancelModalOpen = true"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span>Cancelar</span>
                        </button>

                        <BaseButton variant="subtle" size="md" @click="isEditModalOpen = true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Editar</span>
                        </BaseButton>
                    </template>

                    <BaseButton variant="subtle" size="md" @click="triggerPrint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <span>Imprimir</span>
                    </BaseButton>
                </div>
            </header>

            <!-- 4 KPI Metrics Row -->
            <section class="maint-kpi-grid" aria-label="Métricas de la orden de mantenimiento">
                <BaseKpiCard
                    label="COSTO ESTIMADO / FINAL"
                    :value="formatCurrency(maintenance.costo)"
                    :subtext="`Tipo: ${maintenance.tipoMantenimiento}`"
                    color="brand"
                />
                <BaseKpiCard
                    label="ESTADO DEL SERVICIO"
                    :value="maintenance.estado.toUpperCase().replace('_', ' ')"
                    :subtext="`Orden #${maintenance.id}`"
                    :color="maintenance.estado === 'completado' ? 'green' : (maintenance.isOverdue ? 'red' : 'blue')"
                />
                <BaseKpiCard
                    label="FRECUENCIA OPERATIVA"
                    :value="maintenance.frecuencia.toUpperCase()"
                    :subtext="maintenance.frecuencia !== 'unica' ? 'Recurrencia activa' : 'Servicio puntual'"
                    color="purple"
                />
                <BaseKpiCard
                    label="PRÓXIMA FECHA"
                    :value="formatDate(maintenance.proximaFecha)"
                    :subtext="maintenance.proximaFecha ? 'Ciclo programado' : 'Sin fecha recurrente'"
                    color="orange"
                />
            </section>

            <!-- Main Content: 2 Columns -->
            <div class="maint-content-grid">
                <!-- LEFT COLUMN: Main Details (8 cols) -->
                <div class="maint-col-main">
                    <!-- Equipment Card -->
                    <BaseCard class="maint-section-card" padding="lg">
                        <div class="maint-card-header">
                            <div class="maint-card-header-left">
                                <div class="maint-card-icon-wrap blue">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="maint-card-title">Información del Activo Intervenido</h2>
                                    <p class="maint-card-sub">Detalles técnicos del equipo y su asignación actual</p>
                                </div>
                            </div>
                            <Link
                                v-if="maintenance.equipo?.id"
                                :href="`/equipos/${maintenance.equipo.id}`"
                                class="maint-link-btn"
                            >
                                <span>Ver Ficha de Equipo</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </Link>
                        </div>

                        <div v-if="maintenance.equipo" class="maint-asset-banner">
                            <div class="asset-banner-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                                    <line x1="8" y1="4" x2="8" y2="20"></line>
                                    <line x1="16" y1="4" x2="16" y2="20"></line>
                                    <line x1="4" y1="8" x2="20" y2="8"></line>
                                    <line x1="4" y1="16" x2="20" y2="16"></line>
                                </svg>
                            </div>
                            <div class="asset-banner-info">
                                <div class="asset-banner-title-row">
                                    <h3 class="asset-title-text">
                                        {{ maintenance.equipo.marca }} {{ maintenance.equipo.modelo }}
                                    </h3>
                                    <BaseBadge variant="code" size="md">{{ maintenance.equipo.codigo }}</BaseBadge>
                                    <span class="asset-type-badge">{{ maintenance.equipo.tipo }}</span>
                                </div>
                                <div class="asset-banner-meta-row">
                                    <span class="meta-item">
                                        <strong>Serial:</strong> {{ maintenance.equipo.serial }}
                                    </span>
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item">
                                        <strong>Departamento:</strong>
                                        <Link
                                            v-if="maintenance.equipo.departamentoId"
                                            :href="`/departamentos/${maintenance.equipo.departamentoId}`"
                                            class="meta-link"
                                        >
                                            {{ maintenance.equipo.departamento }}
                                        </Link>
                                        <span v-else>{{ maintenance.equipo.departamento }}</span>
                                    </span>
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item">
                                        <strong>Custodio:</strong>
                                        <Link
                                            v-if="maintenance.equipo.custodioId"
                                            :href="`/personal/${maintenance.equipo.custodioId}`"
                                            class="meta-link"
                                        >
                                            {{ maintenance.equipo.custodio }}
                                        </Link>
                                        <span v-else>{{ maintenance.equipo.custodio }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </BaseCard>

                    <!-- Work Description Card -->
                    <BaseCard class="maint-section-card" padding="lg">
                        <div class="maint-card-header">
                            <div class="maint-card-header-left">
                                <div class="maint-card-icon-wrap purple">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="maint-card-title">Descripción del Trabajo</h2>
                                    <p class="maint-card-sub">Alcance detallado de la intervención técnica</p>
                                </div>
                            </div>
                        </div>

                        <div class="maint-desc-body">
                            {{ maintenance.descripcion || 'Sin descripción especificada para este mantenimiento.' }}
                        </div>

                        <!-- Checklist section if exists -->
                        <div v-if="normalizedChecklist.length > 0" class="maint-checklist-section">
                            <h3 class="checklist-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                                Checklist de Actividades Técnicas
                            </h3>
                            <ul class="checklist-list">
                                <li
                                    v-for="item in normalizedChecklist"
                                    :key="item.id"
                                    class="checklist-item"
                                    :class="{ completed: item.checked || maintenance.estado === 'completado' }"
                                >
                                    <div class="checklist-check-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>
                                    <span class="checklist-text">{{ item.text }}</span>
                                </li>
                            </ul>
                        </div>
                    </BaseCard>

                    <!-- Technical Observations Card -->
                    <BaseCard v-if="maintenance.observaciones" class="maint-section-card" padding="lg">
                        <div class="maint-card-header">
                            <div class="maint-card-header-left">
                                <div class="maint-card-icon-wrap amber">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="maint-card-title">Observaciones Técnicas y Dictamen</h2>
                                    <p class="maint-card-sub">Anotaciones operativas registradas por el personal</p>
                                </div>
                            </div>
                        </div>

                        <div class="maint-observations-callout">
                            <p class="observations-text">{{ maintenance.observaciones }}</p>
                        </div>
                    </BaseCard>

                    <!-- Equipment Maintenance History -->
                    <BaseCard class="maint-section-card" padding="lg">
                        <div class="maint-card-header">
                            <div class="maint-card-header-left">
                                <div class="maint-card-icon-wrap green">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 14 14"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="maint-card-title">Historial de Mantenimientos del Equipo</h2>
                                    <p class="maint-card-sub">Otros servicios e intervenciones ejecutadas sobre este equipo</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="maintenance.historialEquipo && maintenance.historialEquipo.length > 0" class="maint-history-table-wrap">
                            <table class="maint-history-table">
                                <thead>
                                    <tr>
                                        <th>FECHA</th>
                                        <th>TIPO</th>
                                        <th>TÉCNICO</th>
                                        <th>ESTADO</th>
                                        <th>COSTO</th>
                                        <th class="text-right">VER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="h in maintenance.historialEquipo" :key="h.id">
                                        <td class="font-medium">{{ formatDate(h.fecha) }}</td>
                                        <td>
                                            <span class="maint-type-tag" :class="h.tipoMantenimiento">
                                                {{ h.tipoMantenimiento }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ h.tecnicoNombre }}</td>
                                        <td>
                                            <BaseBadge :variant="getStatusBadgeVariant(h.estado)" size="sm">
                                                {{ h.estado.replace('_', ' ') }}
                                            </BaseBadge>
                                        </td>
                                        <td class="font-mono">{{ formatCurrency(h.costo) }}</td>
                                        <td class="text-right">
                                            <Link :href="`/mantenimientos/${h.id}`" class="btn-table-eye" title="Ver mantenimiento">
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
                        <BaseEmptyState
                            v-else
                            title="Sin otros mantenimientos registrados"
                            description="Este activo no cuenta con antecedentes previos de mantenimiento en la base de datos."
                        />
                    </BaseCard>
                </div>

                <!-- RIGHT COLUMN: Metadata & Technician (4 cols) -->
                <div class="maint-col-sidebar">
                    <!-- Schedule Card -->
                    <BaseCard class="maint-sidebar-card" padding="lg">
                        <h3 class="sidebar-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Planificación & Fechas
                        </h3>

                        <div class="maint-timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot blue">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <span class="timeline-label">FECHA PROGRAMADA</span>
                                    <strong class="timeline-value">{{ formatDateTime(maintenance.fecha) }}</strong>
                                </div>
                            </div>

                            <div class="timeline-connector"></div>

                            <div class="timeline-item">
                                <div class="timeline-dot green">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <span class="timeline-label">PRÓXIMO VENCIMIENTO</span>
                                    <strong class="timeline-value">{{ formatDate(maintenance.proximaFecha) }}</strong>
                                    <span class="timeline-subtext">
                                        {{ maintenance.proximaFecha ? 'Recurrente según ciclo' : 'Servicio sin recurrencia' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-field-row">
                            <span class="field-label">Frecuencia Planificada:</span>
                            <span class="field-badge-value">{{ maintenance.frecuencia.toUpperCase() }}</span>
                        </div>
                        <div class="sidebar-field-row">
                            <span class="field-label">Duración Estimada:</span>
                            <span class="field-badge-value">{{ maintenance.duracion || 60 }} minutos</span>
                        </div>
                    </BaseCard>

                    <!-- Technician Card -->
                    <BaseCard class="maint-sidebar-card" padding="lg">
                        <h3 class="sidebar-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Técnico Responsable
                        </h3>

                        <div class="maint-tech-profile">
                            <div class="tech-avatar-circle">
                                {{ technicianInitials }}
                            </div>
                            <div class="tech-info-block">
                                <strong class="tech-name">{{ maintenance.tecnicoNombre || 'Sin asignar' }}</strong>
                                <span class="tech-role-desc">Técnico de Soporte Asignado</span>
                            </div>
                        </div>

                        <div v-if="maintenance.realizadoPor" class="tech-third-party-row">
                            <span class="field-label">Realizado por (Externo/Tercero):</span>
                            <strong class="third-party-val">{{ maintenance.realizadoPor }}</strong>
                        </div>
                    </BaseCard>

                    <!-- Administrative & Financial Card -->
                    <BaseCard class="maint-sidebar-card" padding="lg">
                        <h3 class="sidebar-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Resumen Financiero & Auditoría
                        </h3>

                        <div class="maint-cost-highlight">
                            <span class="cost-title">Costo Liquidado del Servicio</span>
                            <span class="cost-amount">{{ formatCurrency(maintenance.costo) }}</span>
                        </div>

                        <div class="audit-list">
                            <div class="audit-row">
                                <span class="audit-lbl">Tipo de servicio:</span>
                                <span class="audit-val capitalize">{{ maintenance.tipoMantenimiento }}</span>
                            </div>
                            <div class="audit-row">
                                <span class="audit-lbl">Fecha de creación:</span>
                                <span class="audit-val">{{ formatDateTime(maintenance.createdAt) }}</span>
                            </div>
                            <div class="audit-row">
                                <span class="audit-lbl">Última actualización:</span>
                                <span class="audit-val">{{ formatDateTime(maintenance.updatedAt) }}</span>
                            </div>
                        </div>
                    </BaseCard>
                </div>
            </div>
        </div>

        <!-- MODAL: Marcar Realizado / Completar -->
        <BaseModal
            :is-open="isCompleteModalOpen"
            title="Marcar Mantenimiento como Realizado"
            max-width="md"
            @close="isCompleteModalOpen = false"
        >
            <form @submit.prevent="handleComplete" class="modal-form-stack">
                <div class="modal-intro-text">
                    El mantenimiento <strong>#{{ maintenance.id }}</strong> se registrará como concluido y pasará al estado completado.
                </div>

                <div class="form-group">
                    <label class="form-label">Costo Final del Servicio ($)</label>
                    <BaseInput
                        v-model.number="completeForm.costo"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">Observaciones y Dictamen Técnico</label>
                    <BaseTextarea
                        v-model="completeForm.observaciones"
                        placeholder="Detalles de las piezas cambiadas, pruebas realizadas o dictamen final..."
                        rows="4"
                    />
                </div>

                <div class="modal-actions-footer">
                    <BaseButton
                        variant="subtle"
                        size="md"
                        type="button"
                        @click="isCompleteModalOpen = false"
                    >
                        Cancelar
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        size="md"
                        type="submit"
                        :disabled="isSubmitting"
                    >
                        Confirmar y Completar
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- MODAL: Posponer Mantenimiento -->
        <BaseModal
            :is-open="isPostponeModalOpen"
            title="Posponer Orden de Mantenimiento"
            max-width="md"
            @close="isPostponeModalOpen = false"
        >
            <form @submit.prevent="handlePostpone" class="modal-form-stack">
                <div class="modal-intro-text">
                    Indica la nueva fecha y hora para la ejecución del servicio técnico sobre el equipo <strong>{{ maintenance.equipo?.codigo }}</strong>.
                </div>

                <div class="form-group">
                    <label class="form-label">Nueva Fecha y Hora Programada *</label>
                    <BaseInput
                        v-model="postponeForm.nueva_fecha"
                        type="datetime-local"
                        required
                    />
                </div>

                <div class="modal-actions-footer">
                    <BaseButton
                        variant="subtle"
                        size="md"
                        type="button"
                        @click="isPostponeModalOpen = false"
                    >
                        Cancelar
                    </BaseButton>
                    <BaseButton
                        variant="warning"
                        size="md"
                        type="submit"
                        :disabled="isSubmitting || !postponeForm.nueva_fecha"
                    >
                        Posponer Servicio
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- MODAL: Cancelar Mantenimiento -->
        <BaseModal
            :is-open="isCancelModalOpen"
            title="Cancelar Orden de Mantenimiento"
            max-width="md"
            @close="isCancelModalOpen = false"
        >
            <form @submit.prevent="handleCancel" class="modal-form-stack">
                <div class="modal-intro-text danger">
                    ¿Estás seguro de que deseas cancelar este mantenimiento? Esta orden quedará anulada y no podrá ser ejecutada.
                </div>

                <div class="form-group">
                    <label class="form-label">Motivo de Cancelación *</label>
                    <BaseTextarea
                        v-model="cancelForm.motivo"
                        placeholder="Indica el motivo por el cual se anula la orden..."
                        rows="3"
                        required
                    />
                </div>

                <div class="modal-actions-footer">
                    <BaseButton
                        variant="subtle"
                        size="md"
                        type="button"
                        @click="isCancelModalOpen = false"
                    >
                        Volver
                    </BaseButton>
                    <BaseButton
                        variant="danger"
                        size="md"
                        type="submit"
                        :disabled="isSubmitting || !cancelForm.motivo"
                    >
                        Confirmar Cancelación
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- MODAL: Editar Mantenimiento -->
        <BaseModal
            :is-open="isEditModalOpen"
            title="Editar Ficha de Mantenimiento"
            max-width="lg"
            @close="isEditModalOpen = false"
        >
            <form @submit.prevent="handleSaveEdit" class="modal-form-stack">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Fecha y Hora Programada *</label>
                        <BaseInput
                            v-model="editForm.fecha"
                            type="datetime-local"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Mantenimiento *</label>
                        <select v-model="editForm.tipo_mantenimiento" class="custom-select" required>
                            <option value="preventivo">Preventivo</option>
                            <option value="correctivo">Correctivo</option>
                            <option value="predictivo">Predictivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Estado de la Orden *</label>
                        <select v-model="editForm.estado" class="custom-select" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="pospuesto">Pospuesto</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frecuencia Planificada</label>
                        <select v-model="editForm.frecuencia" class="custom-select">
                            <option value="unica">Única vez</option>
                            <option value="mensual">Mensual</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="semestral">Semestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Técnico Asignado</label>
                        <select v-model="editForm.tecnico_id" class="custom-select">
                            <option value="">Sin asignar</option>
                            <option
                                v-for="t in options?.technicians"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración Estimada (min)</label>
                        <BaseInput
                            v-model.number="editForm.duracion"
                            type="number"
                            min="5"
                            placeholder="60"
                        />
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Costo Estimado ($)</label>
                        <BaseInput
                            v-model.number="editForm.costo"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Próxima Fecha de Recurrencia</label>
                        <BaseInput
                            v-model="editForm.proxima_fecha"
                            type="date"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción del Trabajo *</label>
                    <BaseTextarea
                        v-model="editForm.descripcion"
                        rows="3"
                        required
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">Observaciones Adicionales</label>
                    <BaseTextarea
                        v-model="editForm.observaciones"
                        rows="2"
                    />
                </div>

                <div class="modal-actions-footer">
                    <BaseButton
                        variant="subtle"
                        size="md"
                        type="button"
                        @click="isEditModalOpen = false"
                    >
                        Cancelar
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        size="md"
                        type="submit"
                        :disabled="isSubmitting"
                    >
                        Guardar Cambios
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </AppLayout>
</template>

<style scoped>
.maint-show-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Alert Banner */
.maint-alert-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(239, 68, 68, 0.08);
    border: var(--stroke-w) solid rgba(239, 68, 68, 0.3);
    border-radius: var(--panel-radius, 12px);
    padding: 16px 20px;
    box-shadow: none !important;
}

.maint-alert-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-alert-icon svg {
    width: 20px;
    height: 20px;
}

.maint-alert-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.maint-alert-title {
    color: #ef4444;
    font-size: 14px;
    font-weight: 700;
}

.maint-alert-desc {
    color: var(--text-muted);
    font-size: 13px;
}

/* Page Header */
.maint-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.maint-header-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.maint-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.2s ease;
}

.maint-back-btn svg {
    width: 14px;
    height: 14px;
}

.maint-back-btn:hover {
    color: var(--blue, #3b82f6);
}

.maint-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.maint-main-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    text-transform: capitalize;
}

.maint-pill-duration {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    box-shadow: none !important;
}

.maint-pill-duration svg {
    width: 13px;
    height: 13px;
}

.maint-timer-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    font-family: monospace;
    border: var(--stroke-w) solid transparent;
}

.maint-timer-chip.timer-scheduled {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.25);
}

.maint-timer-chip.timer-running {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.maint-timer-chip.timer-overdue {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.25);
}

.maint-timer-chip.timer-completed {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.maint-timer-chip.timer-cancelled {
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border-color: var(--stroke);
}

.timer-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}

.maint-meta-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

.maint-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-icon {
    width: 15px;
    height: 15px;
}

.btn-action-complete {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    background: #059669;
    border: none;
    cursor: pointer;
    box-shadow: none !important;
    transition: background 0.2s ease;
}

.btn-action-complete svg {
    width: 16px;
    height: 16px;
}

.btn-action-complete:hover {
    background: #047857;
}

.btn-action-postpone {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #b45309;
    background: rgba(245, 158, 11, 0.12);
    border: var(--stroke-w) solid rgba(245, 158, 11, 0.3);
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-action-postpone svg {
    width: 15px;
    height: 15px;
}

.btn-action-postpone:hover {
    background: rgba(245, 158, 11, 0.2);
}

.btn-action-cancel-task {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
    border: var(--stroke-w) solid rgba(239, 68, 68, 0.25);
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-action-cancel-task svg {
    width: 15px;
    height: 15px;
}

.btn-action-cancel-task:hover {
    background: rgba(239, 68, 68, 0.16);
}

/* KPI Grid */
.maint-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

/* Two Columns Content */
.maint-content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 1024px) {
    .maint-content-grid {
        grid-template-columns: 8fr 4fr;
    }
}

.maint-col-main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.maint-col-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Section Cards */
.maint-section-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
}

.maint-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.maint-card-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.maint-card-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-card-icon-wrap.blue {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.maint-card-icon-wrap.purple {
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
}

.maint-card-icon-wrap.amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.maint-card-icon-wrap.green {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.maint-card-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.maint-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.maint-card-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.maint-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--blue, #3b82f6);
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(59, 130, 246, 0.08);
    transition: background 0.2s ease;
}

.maint-link-btn svg {
    width: 13px;
    height: 13px;
}

.maint-link-btn:hover {
    background: rgba(59, 130, 246, 0.16);
}

/* Asset Banner */
.maint-asset-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
}

.asset-banner-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--blue, #3b82f6);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.asset-banner-icon svg {
    width: 24px;
    height: 24px;
}

.asset-banner-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.asset-banner-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.asset-title-text {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.asset-type-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    text-transform: capitalize;
}

.asset-banner-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-muted);
    flex-wrap: wrap;
}

.meta-divider {
    color: var(--stroke);
}

.meta-link {
    color: var(--blue, #3b82f6);
    text-decoration: none;
    font-weight: 600;
}

.meta-link:hover {
    text-decoration: underline;
}

/* Description */
.maint-desc-body {
    padding: 16px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.6;
    color: var(--text);
    white-space: pre-line;
}

/* Checklist */
.maint-checklist-section {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.checklist-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.checklist-title svg {
    width: 16px;
    height: 16px;
    color: var(--blue, #3b82f6);
}

.checklist-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 8px;
    font-size: 13px;
    color: var(--text);
}

.checklist-check-icon {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: transparent;
    flex-shrink: 0;
}

.checklist-check-icon svg {
    width: 14px;
    height: 14px;
}

.checklist-item.completed .checklist-check-icon {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

.checklist-item.completed .checklist-text {
    text-decoration: line-through;
    color: var(--text-muted);
}

/* Observations Callout */
.maint-observations-callout {
    padding: 16px;
    background: rgba(245, 158, 11, 0.08);
    border: var(--stroke-w) solid rgba(245, 158, 11, 0.3);
    border-radius: 10px;
}

.observations-text {
    margin: 0;
    font-size: 13px;
    color: var(--text);
    line-height: 1.6;
    white-space: pre-line;
}

/* History Table */
.maint-history-table-wrap {
    overflow-x: auto;
}

.maint-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.maint-history-table th {
    padding: 10px 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: var(--stroke-w) solid var(--stroke);
    text-align: left;
}

.maint-history-table td {
    padding: 12px 14px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.maint-history-table tr:last-child td {
    border-bottom: none;
}

.font-medium {
    font-weight: 600;
}

.font-mono {
    font-family: monospace;
    font-weight: 600;
}

.text-muted {
    color: var(--text-muted);
}

.maint-type-tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
}

.maint-type-tag.preventivo {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.maint-type-tag.correctivo {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.maint-type-tag.predictivo {
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
}

.btn-table-eye {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: inline-grid;
    place-items: center;
    text-decoration: none;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-table-eye svg {
    width: 14px;
    height: 14px;
}

.btn-table-eye:hover {
    color: var(--blue, #3b82f6);
    border-color: var(--blue, #3b82f6);
}

/* Sidebar Cards */
.maint-sidebar-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.sidebar-card-title svg {
    width: 16px;
    height: 16px;
    color: var(--blue, #3b82f6);
}

/* Timeline */
.maint-timeline {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.timeline-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.timeline-dot.blue {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.timeline-dot.green {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.timeline-dot svg {
    width: 15px;
    height: 15px;
}

.timeline-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.timeline-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.05em;
}

.timeline-value {
    font-size: 13px;
    color: var(--text);
}

.timeline-subtext {
    font-size: 11px;
    color: var(--text-muted);
}

.timeline-connector {
    width: 2px;
    height: 18px;
    background: var(--stroke);
    margin-left: 14px;
}

.sidebar-divider {
    height: 1px;
    background: var(--stroke-subtle);
    margin: 4px 0;
}

.sidebar-field-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
}

.field-label {
    color: var(--text-muted);
}

.field-badge-value {
    font-weight: 700;
    color: var(--text);
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
}

/* Technician */
.maint-tech-profile {
    display: flex;
    align-items: center;
    gap: 14px;
}

.tech-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--blue, #3b82f6);
    color: #ffffff;
    font-weight: 800;
    font-size: 15px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.tech-info-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.tech-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.tech-role-desc {
    font-size: 12px;
    color: var(--text-muted);
}

.tech-third-party-row {
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
}

.third-party-val {
    color: var(--text);
    font-size: 13px;
}

/* Financial */
.maint-cost-highlight {
    padding: 14px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cost-title {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
}

.cost-amount {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    font-family: monospace;
}

.audit-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 12px;
}

.audit-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.audit-lbl {
    color: var(--text-muted);
}

.audit-val {
    font-weight: 600;
    color: var(--text);
}

.capitalize {
    text-transform: capitalize;
}

.text-right {
    text-align: right;
}

/* Modal Form Stack */
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.modal-intro-text {
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
    padding: 12px;
    background: var(--stroke-subtle);
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
}

.modal-intro-text.danger {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 640px) {
    .form-grid-2 {
        grid-template-columns: 1fr 1fr;
    }
}

.custom-select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}

.custom-select:focus {
    border-color: var(--blue, #3b82f6);
}

.modal-actions-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
