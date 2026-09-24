<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import TicketStatusBadge from '@/Components/Support/TicketStatusBadge.vue';
import type { TicketListItem } from '@/types/support';

interface Props {
    isOpen: boolean;
    ticket: TicketListItem | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const goToFullDetail = () => {
    if (props.ticket) {
        emit('close');
        router.visit(`/soportes/${props.ticket.raw_id || props.ticket.id}`);
    }
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Detalle de Soporte" max-width="md" @close="emit('close')">
        <div v-if="ticket" class="quick-detail-body">
            <div class="header-badge-row">
                <span class="ticket-tag">#{{ ticket.id }}</span>
                <TicketStatusBadge
                    :status="ticket.status_variant || ticket.status"
                    :label="ticket.status_label"
                />
            </div>

            <div class="field-block">
                <span class="field-label">Asunto / Título</span>
                <p class="field-title">{{ ticket.title }}</p>
            </div>

            <div class="fields-grid">
                <div class="field-block">
                    <span class="field-label">Solicitante y Área</span>
                    <p class="field-value">{{ ticket.requester }}</p>
                    <span class="field-sub">{{ ticket.department }}</span>
                </div>
                <div class="field-block">
                    <span class="field-label">Técnico Responsable</span>
                    <p class="field-value">{{ ticket.tech_name }}</p>
                    <span class="field-sub">{{ ticket.tech_dept }}</span>
                </div>
            </div>

            <div class="fields-grid">
                <div class="field-block">
                    <span class="field-label">Categoría</span>
                    <p class="field-value">{{ ticket.category }}</p>
                </div>
                <div class="field-block">
                    <span class="field-label">Fecha de Apertura</span>
                    <p class="field-value">{{ ticket.date }}</p>
                </div>
            </div>

            <div class="field-block">
                <span class="field-label">Registro Operativo / Comentarios</span>
                <div class="log-preview-box">
                    {{ ticket.description || 'Soporte atendido y verificado satisfactoriamente según protocolo de TI.' }}
                </div>
            </div>

            <div class="modal-actions-bar">
                <BaseButton variant="secondary" type="button" @click="emit('close')">
                    Cerrar
                </BaseButton>
                <BaseButton variant="primary" type="button" @click="goToFullDetail">
                    Ver Ficha Completa →
                </BaseButton>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.quick-detail-body {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.header-badge-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ticket-tag {
    font-size: 13px;
    font-weight: 700;
    color: var(--primary);
    background: rgba(79, 70, 229, 0.12);
    padding: 3px 8px;
    border-radius: 6px;
    border: var(--stroke-w) solid rgba(79, 70, 229, 0.25);
}
.field-block {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.field-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.field-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.35;
}
.fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.field-value {
    margin: 0;
    font-size: 13px;
    color: var(--text);
    font-weight: 600;
}
.field-sub {
    font-size: 11px;
    color: var(--text-muted);
}
.log-preview-box {
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    padding: 12px;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.45;
}
.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
.link-full-detail {
    text-decoration: none;
}
</style>
