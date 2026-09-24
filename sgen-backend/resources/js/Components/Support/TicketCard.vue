<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import BaseCard from '@/Components/UI/BaseCard.vue';
import { BaseAvatar } from '@/Components/UI';
import TicketStatusIcon from '@/Components/Support/TicketStatusIcon.vue';
import type { TicketListItem } from '@/types/support';

const props = defineProps<{
    ticket: TicketListItem;
    isCompact?: boolean;
}>();

const emit = defineEmits<{
    (e: 'view', rawId: number): void;
    (e: 'edit', ticket: TicketListItem): void;
    (e: 'delete', rawId: number): void;
    (e: 'filter-requester', name: string): void;
    (e: 'filter-tech', name: string): void;
}>();

const navigateToDetail = () => {
    const raw = props.ticket.raw_id || props.ticket.id;
    router.visit(`/soportes/${raw}`);
};
</script>

<template>
    <BaseCard
        class="ticket-card"
        :class="[`status-${ticket.status}`, { compact: isCompact }]"
        padding="none"
        @click="navigateToDetail"
    >
        <div class="ticket-card-top">
            <TicketStatusIcon :status="ticket.status" />

            <div class="ticket-info">
                <div class="ticket-meta-top">
                    <span class="ticket-code">#{{ ticket.id }}</span>
                    <span class="ticket-tag">
                        <svg class="tag-icon" viewBox="0 0 24 24">
                            <path d="m4 6 7-1 7 7-6 6-7-7z" />
                            <circle cx="8.5" cy="8.5" r="1.2" fill="currentColor" />
                        </svg>
                        {{ ticket.category }}
                    </span>
                </div>
                <h2 class="ticket-title" :title="ticket.title">
                    <Link :href="`/soportes/${ticket.raw_id || ticket.id}`" class="ticket-title-link" @click.stop>
                        {{ ticket.title }}
                    </Link>
                </h2>
                <div class="ticket-requester">
                    <svg class="req-icon" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <button
                        type="button"
                        class="requester-link"
                        @click="emit('filter-requester', ticket.requester)"
                        title="Filtrar por este solicitante"
                    >
                        {{ ticket.requester }}
                    </button>
                    <span class="dept-subline">• {{ ticket.dept }}</span>
                </div>
            </div>
        </div>

        <div class="ticket-card-bottom">
            <div class="ticket-tech">
                <BaseAvatar :name="ticket.tech" :initials="ticket.tech_init" size="sm" />
                <div class="tech-meta">
                    <span class="tech-role">Técnico</span>
                    <button
                        type="button"
                        class="tech-link"
                        @click="emit('filter-tech', ticket.tech)"
                        title="Filtrar por este técnico"
                    >
                        {{ ticket.tech }}
                    </button>
                </div>
            </div>

            <div class="ticket-date-col">
                <span class="ticket-date">{{ ticket.date }}</span>
                <div class="ticket-indicators">
                    <span class="indicator-item" title="Comentarios">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                        {{ ticket.comments }}
                    </span>
                    <span class="indicator-item" title="Detalles">
                        <svg viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="ticket-actions">
                <button
                    type="button"
                    class="action-btn view-btn"
                    title="Previsualización rápida"
                    @click.stop="emit('view', ticket.raw_id)"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
                <Link
                    :href="`/soportes/${ticket.raw_id || ticket.id}`"
                    class="action-btn edit-btn"
                    title="Ver ficha completa / Editar ticket"
                    @click.stop
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                    </svg>
                </Link>
                <button
                    type="button"
                    class="action-btn delete delete-btn"
                    title="Eliminar ticket"
                    @click.stop="emit('delete', ticket.raw_id)"
                >
                    <svg viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                </button>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.ticket-card {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    margin-bottom: 12px;
    border-left: 4px solid var(--stroke);
    border-radius: 4px 18px 18px 4px;
    gap: 16px;
    transition: transform var(--transition-normal), border-color var(--transition-normal);
}

.ticket-card:hover {
    transform: translateY(-1px);
}

.ticket-card.status-resolved {
    border-left-color: var(--green);
}

.ticket-card.status-process {
    border-left-color: var(--blue);
}

.ticket-card.status-pending {
    border-left-color: var(--yellow);
}

.ticket-card.status-critical {
    border-left-color: var(--red);
}

.ticket-card-top {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
    min-width: 0;
}

.ticket-info {
    min-width: 0;
    flex: 1;
}

.ticket-meta-top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.ticket-code {
    font-size: 11px;
    font-weight: 700;
    color: var(--orange);
    font-family: var(--font-mono);
}

.ticket-tag {
    font-size: 11px;
    padding: 1px 7px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.tag-icon {
    width: 10px;
    height: 10px;
    stroke: currentColor;
    fill: none;
}

.ticket-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 4px;
    cursor: pointer;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: color var(--transition-fast);
}

.ticket-title-link {
    color: inherit;
    text-decoration: none;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ticket-title:hover,
.ticket-title-link:hover {
    color: var(--primary);
}

.ticket-requester {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-muted);
}

.req-icon {
    width: 13px;
    height: 13px;
    stroke: var(--text-muted);
    fill: none;
    stroke-width: 2;
}

.requester-link,
.tech-link {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 12px;
    padding: 0;
    cursor: pointer;
    transition: color var(--transition-fast);
}

.requester-link:hover,
.tech-link:hover {
    color: var(--orange);
    text-decoration: underline;
}

.dept-subline {
    color: var(--text-dim);
}

.ticket-card-bottom {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-shrink: 0;
}

.ticket-tech {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 130px;
}

.tech-meta {
    display: flex;
    flex-direction: column;
}

.tech-role {
    font-size: 10px;
    color: var(--text-dim);
    text-transform: uppercase;
}

.ticket-date-col {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    min-width: 90px;
}

.ticket-date {
    font-size: 12px;
    color: var(--text);
    font-family: var(--font-mono);
}

.ticket-indicators {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 3px;
}

.indicator-item {
    font-size: 11px;
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.indicator-item svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.ticket-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all var(--transition-fast);
}

.action-btn svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.action-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
    background: var(--stroke-subtle);
}

.action-btn.delete:hover {
    color: var(--red);
    border-color: rgba(220, 38, 38, 0.4);
    background: rgba(220, 38, 38, 0.1);
}

/* Modo compacto */
.ticket-card.compact {
    padding: 10px 16px;
    margin-bottom: 8px;
}

@media (max-width: 768px) {
    .ticket-card {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .ticket-card-bottom {
        justify-content: space-between;
        border-top: 1px solid var(--stroke-subtle);
        padding-top: 10px;
    }
}
</style>
