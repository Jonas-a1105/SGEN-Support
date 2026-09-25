<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketDetailHeader from '@/Components/Support/Detail/TicketDetailHeader.vue';
import TicketDetailTabs, { type TabKey } from '@/Components/Support/Detail/TicketDetailTabs.vue';
import TabGeneralInfo from '@/Components/Support/Detail/TabGeneralInfo.vue';
import TabAttachments from '@/Components/Support/Detail/TabAttachments.vue';
import TabComments from '@/Components/Support/Detail/TabComments.vue';
import TabTechnicalLog from '@/Components/Support/Detail/TabTechnicalLog.vue';
import TabRating from '@/Components/Support/Detail/TabRating.vue';
import ModalAssignTechnician from '@/Components/Support/Detail/ModalAssignTechnician.vue';
import ModalAddMaterial from '@/Components/Support/Detail/ModalAddMaterial.vue';
import ModalEditField from '@/Components/Support/Detail/ModalEditField.vue';
import ModalPauseTicket from '@/Components/Support/Detail/ModalPauseTicket.vue';
import ModalResolveTicket from '@/Components/Support/Detail/ModalResolveTicket.vue';
import ModalReopenTicket from '@/Components/Support/Detail/ModalReopenTicket.vue';
import type {
    TicketDetail,
    TicketAsset,
    TicketComment,
    TicketAttachment,
    ConsumedMaterial,
    TicketRating,
    LogEntry,
    SupportFormOptions,
} from '@/Types/support';

interface Props {
    ticket: TicketDetail;
    asset: TicketAsset;
    comments: TicketComment[];
    attachments: TicketAttachment[];
    materials: ConsumedMaterial[];
    rating: TicketRating | null;
    logEntries: LogEntry[];
    options: SupportFormOptions;
}

const props = defineProps<Props>();

const activeTab = ref<TabKey>('tabGeneral');
const isAssignTechOpen = ref(false);
const isAddMaterialOpen = ref(false);
const isEditFieldOpen = ref(false);
const isPauseTicketOpen = ref(false);
const isResolveTicketOpen = ref(false);
const isReopenTicketOpen = ref(false);

const handleStartTicket = () => {
    router.put(
        `/soportes/${props.ticket.id}`,
        { estado: 'en_proceso' },
        { preserveScroll: true }
    );
};

const handleResumeTicket = () => {
    router.post(
        `/soportes/${props.ticket.id}/reanudar`,
        {},
        { preserveScroll: true }
    );
};

const handleReopenTicket = () => {
    isReopenTicketOpen.value = true;
};
</script>

<template>
    <AppLayout :title="`Detalle de Soporte #${ticket.id} — Control y Seguimiento`">
        <Head :title="`Soporte #${ticket.id} — ${ticket.title}`" />

        <div class="ticket-detail-view-container">
            <!-- HEADER DEL TICKET CON CICLO DE VIDA COMPLETO -->
            <TicketDetailHeader
                :ticket="ticket"
                @start-ticket="handleStartTicket"
                @open-pause="isPauseTicketOpen = true"
                @resume-ticket="handleResumeTicket"
                @open-resolve="isResolveTicketOpen = true"
                @reopen-ticket="handleReopenTicket"
            />

            <!-- PESTAÑAS DEL TICKET -->
            <TicketDetailTabs
                v-model:active-tab="activeTab"
                :comments-count="comments.length"
                :attachments-count="attachments.length"
            />

            <!-- CONTENIDO DE LAS PESTAÑAS -->
            <main class="detail-tab-body">
                <TabGeneralInfo
                    v-if="activeTab === 'tabGeneral'"
                    :ticket="ticket"
                    :asset="asset"
                    :materials="materials"
                    @open-assign-tech="isAssignTechOpen = true"
                    @open-edit-date="isEditFieldOpen = true"
                    @open-add-material="isAddMaterialOpen = true"
                />

                <TabAttachments
                    v-else-if="activeTab === 'tabAttachments'"
                    :ticket-id="ticket.id"
                    :attachments="attachments"
                />

                <TabComments
                    v-else-if="activeTab === 'tabComments'"
                    :ticket-id="ticket.id"
                    :comments="comments"
                />

                <TabTechnicalLog
                    v-else-if="activeTab === 'tabLog'"
                    :ticket-id="ticket.id"
                    :tech-name="ticket.tech_name"
                    :tech-initial="ticket.tech_initial"
                    :log-entries="logEntries"
                    @open-assign-tech="isAssignTechOpen = true"
                />

                <TabRating
                    v-else-if="activeTab === 'tabRating'"
                    :ticket-id="ticket.id"
                    :rating="rating"
                />
            </main>
        </div>

        <!-- MODALES DE ACCIÓN -->
        <ModalAssignTechnician
            :is-open="isAssignTechOpen"
            :ticket="ticket"
            :asset="asset"
            :technicians="options.technicians"
            @close="isAssignTechOpen = false"
        />

        <ModalAddMaterial
            :is-open="isAddMaterialOpen"
            :ticket-id="ticket.id"
            :inventory-items="options.inventory_items"
            @close="isAddMaterialOpen = false"
        />

        <ModalEditField
            :is-open="isEditFieldOpen"
            :ticket-id="ticket.id"
            :initial-status="ticket.status"
            @close="isEditFieldOpen = false"
        />

        <ModalPauseTicket
            :is-open="isPauseTicketOpen"
            :ticket-id="ticket.id"
            @close="isPauseTicketOpen = false"
        />

        <ModalResolveTicket
            :is-open="isResolveTicketOpen"
            :ticket-id="ticket.id"
            @close="isResolveTicketOpen = false"
        />

        <ModalReopenTicket
            :is-open="isReopenTicketOpen"
            :ticket-id="ticket.id"
            @close="isReopenTicketOpen = false"
        />
    </AppLayout>
</template>

<style scoped>
.ticket-detail-view-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
}
.detail-tab-body {
    width: 100%;
}
</style>
