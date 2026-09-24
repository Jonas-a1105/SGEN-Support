<script setup lang="ts">
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const emit = defineEmits<{
    (e: 'create-ticket'): void;
}>();
</script>

<template>
    <BaseCard padding="lg" id="subpaneTickets" class="empty-pane-card">
        <div class="empty-icon-circle">
            <svg viewBox="0 0 24 24" class="svg-icon-large">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
        </div>
        <h3 class="empty-pane-title">Sin tickets activos</h3>
        <p class="empty-pane-desc">
            Este equipo funciona correctamente. No hay reportes de soporte pendientes.
        </p>
        <BaseButton
            variant="primary"
            id="btnShortcutCreateTicket"
            @click="emit('create-ticket')"
        >
            + Crear Ticket
        </BaseButton>
    </BaseCard>
</template>

<style scoped>
.empty-pane-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--space-8) var(--space-5) !important;
}

.empty-icon-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    display: grid;
    place-items: center;
    margin-bottom: var(--space-4);
}

.svg-icon-large {
    width: 28px;
    height: 28px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.empty-pane-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 var(--space-2) 0;
}

.empty-pane-desc {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 380px;
    margin: 0 0 var(--space-5) 0;
    line-height: 1.4;
}
</style>
