<script setup lang="ts">
import type { LogEntry } from '@/types/support';
import TechnicalLogEditor from './TechnicalLogEditor.vue';
import TechnicalLogHistory from './TechnicalLogHistory.vue';
import TechnicalProtocolCard from './TechnicalProtocolCard.vue';
import TechnicalAssignedTechCard from './TechnicalAssignedTechCard.vue';

interface Props {
    ticketId: number;
    techName: string;
    techInitial: string;
    logEntries: LogEntry[];
}

defineProps<Props>();
const emit = defineEmits<{ (e: 'open-assign-tech'): void }>();
</script>

<template>
    <div class="log-grid">
        <!-- COLUMNA IZQUIERDA: EDITOR DE BITÁCORA Y REGISTROS -->
        <div class="log-left-col">
            <TechnicalLogEditor :ticket-id="ticketId" />
            <TechnicalLogHistory :log-entries="logEntries" />
        </div>

        <!-- COLUMNA DERECHA: PROTOCOLO TÉCNICO Y TÉCNICO ASIGNADO -->
        <div class="log-right-col">
            <TechnicalProtocolCard />
            <TechnicalAssignedTechCard
                :tech-name="techName"
                :tech-initial="techInitial"
                @open-assign-tech="emit('open-assign-tech')"
            />
        </div>
    </div>
</template>

<style scoped>
.log-grid {
    display: grid;
    grid-template-columns: 1.35fr 0.9fr;
    gap: 20px;
    align-items: start;
}

@media (max-width: 900px) {
    .log-grid {
        grid-template-columns: 1fr;
    }
}

.log-left-col {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.log-right-col {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
</style>
