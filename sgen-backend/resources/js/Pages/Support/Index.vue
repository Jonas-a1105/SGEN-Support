<script setup lang="ts">
import { ref, toRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BaseEmptyState } from '@/Components/UI';
import SupportHeader from '@/Components/Support/SupportHeader.vue';
import SupportKpiGrid from '@/Components/Support/SupportKpiGrid.vue';
import SupportToolbar from '@/Components/Support/SupportToolbar.vue';
import TicketCard from '@/Components/Support/TicketCard.vue';
import ModalNewTicket from '@/Components/Support/ModalNewTicket.vue';
import ModalConfirmDelete from '@/Components/Support/ModalConfirmDelete.vue';
import ModalTicketQuickDetail from '@/Components/Support/ModalTicketQuickDetail.vue';
import { useSupportFilters } from '@/Composables/useSupportFilters';
import type { SupportKpis, TicketListItem, SupportFormOptions } from '@/types/support';

interface Props {
    kpis: SupportKpis;
    tickets: TicketListItem[];
    activeFilter?: string;
    options: SupportFormOptions;
}

const props = withDefaults(defineProps<Props>(), {
    activeFilter: 'all',
});

// Delegación completa de la responsabilidad de filtrado al Composable
const {
    searchQuery,
    activeFilter,
    isCompact,
    filteredTickets,
    setFilter,
    setSearch,
    filterByRequester,
    filterByTech,
} = useSupportFilters(toRef(props, 'tickets'), props.activeFilter);

const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
const isNewModalOpen = ref(urlParams?.get('nuevo') === '1' || urlParams?.get('create') === '1');
const isDeleteModalOpen = ref(false);
const isQuickDetailOpen = ref(false);

const selectedTicketForDelete = ref<number | null>(null);
const selectedTicketForView = ref<TicketListItem | null>(null);

const handleViewTicket = (rawId: number | string) => {
    const numericId = typeof rawId === 'number' ? rawId : parseInt(String(rawId).replace(/\D/g, ''), 10);
    const found = props.tickets.find((t) => t.raw_id === numericId || t.id === `T-${numericId}` || t.id === `${numericId}`);
    if (found) {
        selectedTicketForView.value = found;
        isQuickDetailOpen.value = true;
    }
};

const handleEditTicket = (rawId: number | string | TicketListItem) => {
    let id: number;
    if (typeof rawId === 'object' && rawId !== null) {
        id = rawId.raw_id || parseInt(String(rawId.id).replace(/\D/g, ''), 10);
    } else if (typeof rawId === 'string') {
        id = parseInt(rawId.replace(/\D/g, ''), 10);
    } else {
        id = rawId;
    }
    router.visit(`/soportes/${id}`);
};

const handleDeleteTicket = (rawId: number | string) => {
    const numericId = typeof rawId === 'number' ? rawId : parseInt(String(rawId).replace(/\D/g, ''), 10);
    selectedTicketForDelete.value = numericId;
    isDeleteModalOpen.value = true;
};
</script>

<template>
    <AppLayout title="Gestión de Soportes — Control y Seguimiento">
        <Head title="Gestión de Soportes" />

        <div class="support-index-container">
            <SupportHeader @open-new="isNewModalOpen = true" @new-ticket="isNewModalOpen = true" />

            <SupportKpiGrid :kpis="kpis" />

            <SupportToolbar
                :kpis="kpis"
                :active-filter="activeFilter"
                :is-compact="isCompact"
                :search-query="searchQuery"
                @update:active-filter="setFilter"
                @update:is-compact="isCompact = $event"
                @update:search-query="setSearch"
            />

            <!-- TICKETS LIST -->
            <div v-if="filteredTickets.length > 0" class="tickets-flow">
                <TicketCard
                    v-for="ticket in filteredTickets"
                    :key="ticket.id"
                    :ticket="ticket"
                    :is-compact="isCompact"
                    @view="handleViewTicket"
                    @edit="handleEditTicket"
                    @delete="handleDeleteTicket"
                    @filter-requester="filterByRequester"
                    @filter-tech="filterByTech"
                />
            </div>

            <!-- EMPTY STATE -->
            <BaseEmptyState
                v-else
                title="No se encontraron tickets"
                subtitle="No hay registros de soporte que coincidan con el filtro actual o la búsqueda ingresada."
            />
        </div>

        <!-- MODALES -->
        <ModalNewTicket
            :is-open="isNewModalOpen"
            :options="options"
            @close="isNewModalOpen = false"
        />

        <ModalConfirmDelete
            :is-open="isDeleteModalOpen"
            :ticket-id="selectedTicketForDelete"
            @close="isDeleteModalOpen = false"
        />

        <ModalTicketQuickDetail
            :is-open="isQuickDetailOpen"
            :ticket="selectedTicketForView"
            @close="isQuickDetailOpen = false"
        />
    </AppLayout>
</template>

<style scoped>
.support-index-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
}

.tickets-flow {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
</style>
