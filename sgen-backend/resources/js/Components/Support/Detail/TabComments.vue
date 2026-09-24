<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseBadge, BaseButton, BaseAvatar } from '@/Components/UI';
import type { TicketComment } from '@/types/support';

interface Props {
    ticketId: number;
    comments: TicketComment[];
}

const props = defineProps<Props>();

const newComment = ref('');
const isInternal = ref(false);
const isSending = ref(false);

const toggleVisibility = () => {
    isInternal.value = !isInternal.value;
};

const sendComment = () => {
    if (!newComment.value.trim() || isSending.value) return;

    isSending.value = true;
    router.post(
        `/soportes/${props.ticketId}/comentarios`,
        {
            comentario: newComment.value,
            es_interno: isInternal.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                newComment.value = '';
                isSending.value = false;
            },
            onError: () => {
                isSending.value = false;
            },
        }
    );
};
</script>

<template>
    <section class="detail-panel">
        <div class="panel-header-row">
            <div class="panel-title-group">
                <div class="panel-icon-pill" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h2 class="panel-title">HILO DE CONVERSACIÓN</h2>
                <BaseBadge variant="neutral" size="sm">
                    {{ comments.length }}
                </BaseBadge>
            </div>
        </div>

        <div class="panel-body-pad">
            <!-- CHAT THREAD BOX -->
            <div class="chat-thread-box">
                <div v-for="c in comments" :key="c.id" class="chat-bubble-row">
                    <div class="chat-bubble">
                        <div class="chat-meta">
                            <BaseBadge v-if="c.is_internal" variant="danger" size="sm">
                                Interno
                            </BaseBadge>
                            <span>{{ c.time_ago }}</span>
                            <strong class="author-name">{{ c.author }}</strong>
                        </div>
                        <div class="chat-message-text">{{ c.comment }}</div>
                    </div>
                    <BaseAvatar :name="c.author" size="sm" />
                </div>
            </div>

            <!-- COMPOSER CARD -->
            <div class="composer-card">
                <span class="composer-legend">Nuevo Mensaje</span>
                <textarea
                    v-model="newComment"
                    class="composer-textarea"
                    placeholder="Escribe un comentario..."
                    rows="3"
                    @keydown.enter.ctrl="sendComment"
                ></textarea>
                <div class="composer-footer">
                    <button class="visibility-toggle-btn" type="button" @click="toggleVisibility">
                        <span>{{ isInternal ? '🔒' : '👁' }}</span>
                        <span>{{ isInternal ? 'Solo técnicos (Interno)' : 'Visible para todos' }}</span>
                    </button>
                    <BaseButton
                        variant="primary"
                        size="sm"
                        :disabled="isSending || !newComment.trim()"
                        @click="sendComment"
                    >
                        <span>{{ isSending ? 'Enviando...' : 'Enviar' }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="send-svg">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </BaseButton>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.detail-panel {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: none !important;
}

.panel-header-row {
    padding: 18px 22px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.panel-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.panel-title {
    margin: 0;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
    letter-spacing: -0.01em;
}

.panel-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.panel-icon-pill svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.panel-body-pad {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.chat-thread-box {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--card-radius);
    padding: 24px;
    min-height: 220px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    overflow-y: auto;
    max-height: 440px;
}

.chat-bubble-row {
    display: flex;
    justify-content: flex-end;
    align-items: flex-start;
    gap: 10px;
}

.chat-bubble {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 16px 16px 4px 16px;
    padding: 12px 16px;
    max-width: 75%;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: none !important;
}

.chat-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    font-size: 11px;
    color: var(--text-muted);
}

.author-name {
    color: var(--text);
}

.chat-message-text {
    font-size: 14px;
    color: var(--text);
    line-height: 1.4;
    word-break: break-word;
}

.composer-card {
    border: var(--stroke-w) solid var(--primary);
    border-radius: var(--card-radius);
    padding: 14px 16px;
    background: var(--bg-card);
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    margin-top: 6px;
    box-shadow: none !important;
}

.composer-legend {
    position: absolute;
    top: -10px;
    left: 14px;
    background: var(--bg-card);
    padding: 0 6px;
    font-size: 11px;
    color: var(--primary);
    font-weight: 700 !important;
}

.composer-textarea {
    width: 100%;
    border: none;
    background: transparent;
    color: var(--text);
    font-size: 13px;
    resize: none;
    outline: none;
    min-height: 52px;
    font-family: inherit;
}

.composer-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.visibility-toggle-btn {
    height: 30px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.18s ease;
    box-shadow: none !important;
}

.visibility-toggle-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.send-svg {
    width: 14px;
    height: 14px;
}
</style>
