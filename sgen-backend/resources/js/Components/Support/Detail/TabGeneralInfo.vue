<script setup lang="ts">
import type { TicketDetail, TicketAsset, ConsumedMaterial } from '@/Types/support';
import TicketIncidentDetailsCard from './TicketIncidentDetailsCard.vue';
import TicketAffectedAssetCard from './TicketAffectedAssetCard.vue';
import TicketMaterialsCard from './TicketMaterialsCard.vue';

interface Props {
    ticket: TicketDetail;
    asset: TicketAsset;
    materials: ConsumedMaterial[];
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'open-assign-tech'): void;
    (e: 'open-edit-date'): void;
    (e: 'open-add-material'): void;
}>();
</script>

<template>
    <div class="detail-grid">
        <!-- COLUMNA IZQUIERDA: DETALLES DEL INCIDENTE -->
        <TicketIncidentDetailsCard
            :ticket="ticket"
            @open-assign-tech="emit('open-assign-tech')"
            @open-edit-date="emit('open-edit-date')"
        />

        <!-- COLUMNA DERECHA: ACTIVO AFECTADO & MATERIALES -->
        <div class="right-column-stack">
            <TicketAffectedAssetCard :asset="asset" />

            <TicketMaterialsCard
                :materials="materials"
                @open-add-material="emit('open-add-material')"
            />
        </div>
    </div>
</template>

<style scoped>
.detail-grid {
    display: grid;
    grid-template-columns: 1.4fr 0.85fr;
    gap: 20px;
    align-items: start;
}

@media (max-width: 900px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}

.right-column-stack {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
</style>
