<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BasePageHeader } from '@/Components/UI';
import {
    useAuditFilters,
    useBitacoraFilters,
    type AuditSessionItem,
    type BitacoraActionItem,
} from '@/Composables/useAuditFilters';
import AuditKpiRow, { type AuditKpis } from '@/Components/Audit/AuditKpiRow.vue';
import AuditToolbar from '@/Components/Audit/AuditToolbar.vue';
import AuditTable from '@/Components/Audit/AuditTable.vue';
import ModalSessionDetail from '@/Components/Audit/ModalSessionDetail.vue';
import AuditTabBar from '@/Components/Audit/AuditTabBar.vue';
import BitacoraToolbar from '@/Components/Audit/BitacoraToolbar.vue';
import BitacoraTimeline from '@/Components/Audit/BitacoraTimeline.vue';
import ModalBitacoraDetail from '@/Components/Audit/ModalBitacoraDetail.vue';

const props = withDefaults(
    defineProps<{
        sessions: AuditSessionItem[];
        actions?: BitacoraActionItem[];
        kpis: AuditKpis;
        active_tab?: string;
        filters?: {
            search?: string;
            user_id?: string | number;
            entity?: string;
        };
    }>(),
    {
        actions: () => [],
        active_tab: 'sesiones',
    }
);

// Tab activo: 'sesiones' (auditoria.html) | 'bitacora' (bitacora.html)
const activeTab = ref<'sesiones' | 'bitacora'>(
    props.active_tab === 'bitacora' ? 'bitacora' : 'sesiones'
);

// Filtros para Sesiones de Acceso
const sessionsRef = toRef(props, 'sessions');
const { search: sessionSearch, statusFilter, filteredSessions } = useAuditFilters(sessionsRef);

// Filtros para Bitácora de Movimientos
const actionsRef = toRef(props, 'actions');
const {
    search: bitacoraSearch,
    selectedEntity: bitacoraEntity,
    filteredActions,
} = useBitacoraFilters(actionsRef);

// Modales
const showSessionModal = ref(false);
const selectedSession = ref<AuditSessionItem | null>(null);

const showActionModal = ref(false);
const selectedAction = ref<BitacoraActionItem | null>(null);

function openSessionDetail(session: AuditSessionItem) {
    selectedSession.value = session;
    showSessionModal.value = true;
}

function openActionDetail(action: BitacoraActionItem) {
    selectedAction.value = action;
    showActionModal.value = true;
}

function handleExportSessions() {
    window.location.href = `/auditoria/export?search=${encodeURIComponent(sessionSearch.value)}`;
}

function handleExportBitacora() {
    window.location.href = `/auditoria/export-bitacora?search=${encodeURIComponent(
        bitacoraSearch.value
    )}&entity=${encodeURIComponent(bitacoraEntity.value)}`;
}
</script>

<template>
    <AppLayout>
        <Head title="Auditoría y Bitácora del Sistema" />

        <div class="module-wrapper">
            <!-- Header -->
            <BasePageHeader
                title="Auditoría del Sistema"
                subtitle="Monitoreo de seguridad, registro de sesiones y trazabilidad de operaciones."
            >
                <template #icon>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                </template>
            </BasePageHeader>

            <!-- Pestaña Segmentada: Sesiones vs. Bitácora -->
            <AuditTabBar
                :active-tab="activeTab"
                :sessions-count="sessions.length"
                :actions-count="actions.length"
                @change="activeTab = $event"
            />

            <!-- VISTA 1: SESIONES DE ACCESO (auditoria.html) -->
            <div v-if="activeTab === 'sesiones'" class="tab-pane">
                <!-- KPIs -->
                <AuditKpiRow :kpis="kpis" />

                <!-- Toolbar -->
                <AuditToolbar
                    v-model:search="sessionSearch"
                    @export="handleExportSessions"
                />

                <!-- Table -->
                <AuditTable
                    :sessions="filteredSessions"
                    @view="openSessionDetail"
                />

                <!-- Modal de Detalle de Sesión -->
                <ModalSessionDetail
                    :show="showSessionModal"
                    :session="selectedSession"
                    @close="showSessionModal = false"
                />
            </div>

            <!-- VISTA 2: BITÁCORA DE MOVIMIENTOS (bitacora.html) -->
            <div v-else-if="activeTab === 'bitacora'" class="tab-pane">
                <!-- Toolbar de Bitácora -->
                <BitacoraToolbar
                    v-model:search="bitacoraSearch"
                    v-model:selected-entity="bitacoraEntity"
                    :showing-count="filteredActions.length"
                    :total-count="actions.length"
                    @export="handleExportBitacora"
                />

                <!-- Timeline de Eventos -->
                <BitacoraTimeline
                    :actions="filteredActions"
                    @inspect="openActionDetail"
                />

                <!-- Modal de Detalle de Bitácora (Diff) -->
                <ModalBitacoraDetail
                    :show="showActionModal"
                    :action="selectedAction"
                    @close="showActionModal = false"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.module-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.tab-pane {
    display: flex;
    flex-direction: column;
    width: 100%;
}
</style>
