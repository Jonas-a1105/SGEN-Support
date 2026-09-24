<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseKpiCard } from '@/Components/UI';
import EmployeeDetailHeader from '@/Components/Employee/Detail/EmployeeDetailHeader.vue';
import SidebarEmployeeContact from '@/Components/Employee/Detail/SidebarEmployeeContact.vue';
import TabEmployeeOverview from '@/Components/Employee/Detail/TabEmployeeOverview.vue';
import TabEmployeeAssets from '@/Components/Employee/Detail/TabEmployeeAssets.vue';
import TabEmployeeTickets from '@/Components/Employee/Detail/TabEmployeeTickets.vue';
import ModalEditEmployee from '@/Components/Employee/Detail/ModalEditEmployee.vue';
import type { EmployeeDetail, EmployeeTabKey } from '@/Components/Employee/Detail/types';

const props = defineProps<{
    employee: EmployeeDetail;
}>();

const activeTab = ref<EmployeeTabKey>('overview');
const isEditModalOpen = ref(false);
</script>

<template>
    <AppLayout :title="`Colaborador: ${employee.fullName}`">
        <Head :title="`Colaborador - ${employee.fullName}`" />

        <div class="emp-show-container">
            <!-- Header Bar -->
            <EmployeeDetailHeader
                :employee="employee"
                @edit="isEditModalOpen = true"
            />

            <!-- 4 KPI Metrics -->
            <section class="emp-kpi-grid" aria-label="Métricas del colaborador">
                <BaseKpiCard
                    label="EQUIPOS EN CUSTODIA"
                    :value="employee.equiposCount"
                    subtext="Activos bajo su cargo"
                    icon="fa-solid fa-laptop"
                    color="blue"
                />
                <BaseKpiCard
                    label="TICKETS REPORTADOS"
                    :value="employee.ticketsCount"
                    subtext="Incidencias generadas"
                    icon="fa-solid fa-headset"
                    color="orange"
                />
                <BaseKpiCard
                    label="INCIDENCIAS RESUELTAS"
                    :value="employee.resolvedTicketsCount"
                    subtext="Casos cerrados con éxito"
                    icon="fa-solid fa-circle-check"
                    color="green"
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
                <SidebarEmployeeContact :employee="employee" />

                <!-- Right Main Column: Tabs Panel -->
                <main class="emp-main-col">
                    <div class="emp-panel-wrapper">
                        <!-- Navigation Tabs Bar -->
                        <nav class="emp-tabs-nav" aria-label="Pestañas de colaborador">
                            <button
                                type="button"
                                class="emp-tab-button"
                                :class="{ 'is-active': activeTab === 'overview' }"
                                @click="activeTab = 'overview'"
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
                                @click="activeTab = 'assets'"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
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
                                @click="activeTab = 'tickets'"
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
                            <TabEmployeeOverview
                                v-if="activeTab === 'overview'"
                                :employee="employee"
                            />

                            <TabEmployeeAssets
                                v-else-if="activeTab === 'assets'"
                                :equipos="employee.equipos"
                            />

                            <TabEmployeeTickets
                                v-else-if="activeTab === 'tickets'"
                                :tickets="employee.tickets"
                                :employee-id="employee.id"
                            />
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- MODAL: Editar Colaborador -->
        <ModalEditEmployee
            :is-open="isEditModalOpen"
            :employee="employee"
            @close="isEditModalOpen = false"
        />
    </AppLayout>
</template>

<style scoped>
.emp-show-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
    width: 100%;
}

/* KPI Grid */
.emp-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 1024px) {
    .emp-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
}

/* 2-Column Layout */
.emp-content-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: start;
}

@media (min-width: 1024px) {
    .emp-content-layout {
        grid-template-columns: 340px 1fr;
    }
}

.emp-main-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.emp-panel-wrapper {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    overflow: hidden;
    box-shadow: none !important;
}

.emp-tabs-nav {
    display: flex;
    align-items: center;
    border-bottom: var(--stroke-w) solid var(--stroke);
    background: var(--stroke-subtle);
    padding: 0 16px;
    gap: 8px;
    overflow-x: auto;
}

.emp-tab-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    box-shadow: none !important;
}

.emp-tab-button svg {
    width: 15px;
    height: 15px;
}

.emp-tab-button:hover {
    color: var(--text);
}

.emp-tab-button.is-active {
    color: var(--blue, #3b82f6);
    border-bottom-color: var(--blue, #3b82f6);
    background: var(--bg-card);
}

.emp-tab-counter {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: var(--stroke-subtle);
    color: var(--text-muted);
}

.emp-tab-button.is-active .emp-tab-counter {
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue, #3b82f6);
}

.emp-tab-body {
    padding: 24px;
}
</style>
