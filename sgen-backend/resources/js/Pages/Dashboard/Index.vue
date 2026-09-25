<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
import DashboardWelcome from '@/Components/Dashboard/DashboardWelcome.vue';
import DashboardKpiGrid from '@/Components/Dashboard/DashboardKpiGrid.vue';
import DashboardTicketStatusCard from '@/Components/Dashboard/DashboardTicketStatusCard.vue';
import DashboardTicketChartCard from '@/Components/Dashboard/DashboardTicketChartCard.vue';
import DashboardCategoryCard from '@/Components/Dashboard/DashboardCategoryCard.vue';
import DashboardInventoryHealthCard from '@/Components/Dashboard/DashboardInventoryHealthCard.vue';
import DashboardTechPerformanceCard from '@/Components/Dashboard/DashboardTechPerformanceCard.vue';
import DashboardOperationsDistributionCard from '@/Components/Dashboard/DashboardOperationsDistributionCard.vue';
import DashboardRecentActivityCard from '@/Components/Dashboard/DashboardRecentActivityCard.vue';
import DashboardAppearanceModal from '@/Components/Dashboard/DashboardAppearanceModal.vue';
import type { DashboardMetrics } from '@/Types/DashboardMetrics';

const props = defineProps<{
    metrics: DashboardMetrics;
}>();

// Estado de modales y feedback interactivo
const isAppearanceOpen = ref(false);
const isDetailModalOpen = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');

// Toast
const toastText = ref('');
const toastTitle = ref('');
const isToastVisible = ref(false);
let toastTimeout: ReturnType<typeof setTimeout> | null = null;

const showToast = (title: string, message: string) => {
    toastTitle.value = title;
    toastText.value = message;
    isToastVisible.value = true;
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        isToastVisible.value = false;
    }, 2800);
};

const handleYearChange = (year: number) => {
    router.get(
        '/dashboard',
        { year },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['metrics'],
            onSuccess: () => {
                showToast('Gráfica actualizada', `Datos del periodo ${year} cargados.`);
            },
        }
    );
};

const handleKpiSelect = (type: string) => {
    if (type === 'equipos') {
        router.visit('/inventario?tab=equipos');
    } else if (type === 'pendientes') {
        router.visit('/soportes?estado=pendiente');
    } else if (type === 'proceso') {
        router.visit('/soportes?estado=en_proceso');
    } else if (type === 'resueltos') {
        router.visit('/soportes?estado=resuelto');
    } else {
        router.visit('/soportes');
    }
};

const handlePointClicked = (pt: { month: string; val: number; year: number }) => {
    showToast(pt.month, `${pt.val} tickets registrados en ${pt.year}.`);
};

const handleFilterInventory = (status: string) => {
    router.visit(`/inventario?tab=equipos&status=${encodeURIComponent(status)}`);
};

const handleOpenTicket = (id: number) => {
    router.visit(`/soportes/${id}`);
};

const handleViewAllTickets = () => {
    router.visit('/soportes');
};

const handleCreateTicket = () => {
    router.visit('/soportes/crear');
};

const handleViewLogs = () => {
    router.visit('/auditoria');
};
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="dashboard-wrapper">
            <!-- Barra de Bienvenida -->
            <DashboardWelcome @open-appearance="isAppearanceOpen = true" />

            <!-- Rejilla de 4 KPIs -->
            <DashboardKpiGrid
                :kpis="metrics.kpis"
                @select-kpi="handleKpiSelect"
            />

            <!-- Grid Principal: 1.45fr (Tickets & Gráfica) y 0.85fr (Categoría, Salud, Técnicos) -->
            <div class="main-grid">
                <div class="left-stack">
                    <DashboardTicketStatusCard
                        :pending="metrics.tickets_by_status.pending"
                        :in-process="metrics.tickets_by_status.in_process"
                        @open-ticket="handleOpenTicket"
                        @view-all="handleViewAllTickets"
                    />

                    <DashboardTicketChartCard
                        :data="metrics.ticket_volume"
                        @change-year="handleYearChange"
                        @point-clicked="handlePointClicked"
                    />
                </div>

                <div class="right-stack">
                    <DashboardCategoryCard
                        :category="metrics.top_category"
                    />

                    <DashboardInventoryHealthCard
                        :health="metrics.inventory_health"
                        @filter-inventory="handleFilterInventory"
                    />

                    <DashboardTechPerformanceCard
                        :technicians="metrics.technicians"
                    />
                </div>
            </div>

            <!-- Grid Inferior: 1.45fr (Distribución Operativa) y 0.85fr (Actividad Reciente) -->
            <div class="bottom-grid">
                <DashboardOperationsDistributionCard
                    :health="metrics.inventory_health"
                />

                <DashboardRecentActivityCard
                    :activities="metrics.recent_activity"
                    @view-logs="handleViewLogs"
                    @create-ticket="handleCreateTicket"
                />
            </div>
        </div>

        <!-- Modal de Personalización -->
        <DashboardAppearanceModal
            :show="isAppearanceOpen"
            @close="isAppearanceOpen = false"
        />

        <!-- Modal Genérico de Operaciones -->
        <BaseModal
            :is-open="isDetailModalOpen"
            :title="modalTitle"
            confirm-text="Entendido"
            cancel-text="Cerrar"
            @close="isDetailModalOpen = false"
            @confirm="isDetailModalOpen = false"
        >
            <p class="modal-description">{{ modalMessage }}</p>
        </BaseModal>

        <!-- Toast Flotante -->
        <div class="toast-card" :class="{ show: isToastVisible }">
            <b class="toast-title">{{ toastTitle }}</b>
            <span class="toast-body">{{ toastText }}</span>
        </div>
    </AppLayout>
</template>

<style scoped>
.dashboard-wrapper {
    max-width: 1220px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
}

.main-grid {
    display: grid;
    grid-template-columns: 1.45fr 0.85fr;
    gap: var(--space-4);
    margin-top: var(--space-4);
}

.left-stack,
.right-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.bottom-grid {
    display: grid;
    grid-template-columns: 1.45fr 0.85fr;
    gap: var(--space-4);
    margin-top: var(--space-4);
}

.modal-description {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.5;
    margin: 0;
}

.toast-card {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 100;
    min-width: 220px;
    padding: 12px 16px;
    border: var(--stroke-w) solid var(--stroke);
    border-left: 4px solid var(--orange);
    background: var(--bg-card);
    border-radius: var(--radius-md);
    transform: translateY(20px);
    opacity: 0;
    pointer-events: none;
    transition: all var(--transition-normal);
}

.toast-card.show {
    transform: translateY(0);
    opacity: 1;
}

.toast-title {
    display: block;
    font-size: 13px;
    color: var(--text);
    margin-bottom: 2px;
}

.toast-body {
    font-size: 12px;
    color: var(--text-muted);
}

@media (max-width: 1080px) {
    .main-grid,
    .bottom-grid {
        grid-template-columns: 1fr;
    }
}
</style>
