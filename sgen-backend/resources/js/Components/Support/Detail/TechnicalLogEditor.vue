<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseBadge, BaseButton } from '@/Components/UI';

const props = defineProps<{
    ticketId: number;
}>();

const editorText = ref('');
const isSubmitting = ref(false);

const insertTag = (tag: string) => {
    editorText.value = editorText.value ? `${editorText.value}\n${tag}` : tag;
};

const applyFormat = (type: 'bold' | 'italic' | 'list' | 'code') => {
    switch (type) {
        case 'bold':
            editorText.value += '**texto en negrita**';
            break;
        case 'italic':
            editorText.value += '*texto en cursiva*';
            break;
        case 'list':
            editorText.value += '\n• ';
            break;
        case 'code':
            editorText.value += '`código`';
            break;
    }
};

const submitLog = () => {
    if (!editorText.value.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    router.post(
        `/soportes/${props.ticketId}/comentarios`,
        {
            comentario: editorText.value,
            es_interno: true,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                editorText.value = '';
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="log-editor-box">
        <div class="editor-header-row">
            <div class="panel-title-group">
                <div class="panel-icon-pill" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                </div>
                <h2 class="panel-title">NUEVA ENTRADA TÉCNICA</h2>
            </div>
            <BaseBadge variant="neutral" size="sm">
                PRIVADO
            </BaseBadge>
        </div>

        <div class="editor-toolbar">
            <button class="tool-btn" type="button" title="Negrita" @click="applyFormat('bold')">
                <b>B</b>
            </button>
            <button class="tool-btn" type="button" title="Cursiva" @click="applyFormat('italic')">
                <i>I</i>
            </button>
            <button class="tool-btn" type="button" title="Lista viñeta" @click="applyFormat('list')">
                <svg viewBox="0 0 24 24">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <circle cx="4" cy="6" r="1.5"></circle>
                    <circle cx="4" cy="12" r="1.5"></circle>
                    <circle cx="4" cy="18" r="1.5"></circle>
                </svg>
            </button>
            <button class="tool-btn" type="button" title="Código" @click="applyFormat('code')">
                &lt;&gt;
            </button>
        </div>

        <textarea
            v-model="editorText"
            class="editor-textarea"
            placeholder="Describa el procedimiento realizado, hallazgos técnicos o recomendaciones..."
        ></textarea>

        <div class="quick-tags-row">
            <button class="quick-tag-chip diag" type="button" @click="insertTag('[Diagnóstico]: ')">
                + Diagnóstico
            </button>
            <button class="quick-tag-chip repar" type="button" @click="insertTag('[Reparación]: ')">
                + Reparación
            </button>
            <button class="quick-tag-chip piezas" type="button" @click="insertTag('[Piezas sustituidas]: ')">
                + Piezas
            </button>
            <button class="quick-tag-chip pruebas" type="button" @click="insertTag('[Pruebas de estrés]: ')">
                + Pruebas
            </button>
        </div>

        <div class="editor-footer-row">
            <BaseButton
                variant="primary"
                size="md"
                :disabled="isSubmitting || !editorText.trim()"
                @click="submitLog"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-check-svg">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ isSubmitting ? 'Registrando...' : 'Registrar Avance' }}</span>
            </BaseButton>
        </div>
    </div>
</template>

<style scoped>
.log-editor-box {
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    background: var(--bg-card);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: none !important;
}

.editor-header-row {
    padding: 16px 20px;
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
    font-size: 14px !important;
    font-weight: 700 !important;
    color: var(--text);
    letter-spacing: -0.01em;
}

.panel-icon-pill {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.panel-icon-pill svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.editor-toolbar {
    padding: 8px 16px;
    background: var(--bg-sub);
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    gap: 6px;
}

.tool-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 12px;
    transition: all 0.16s ease;
    box-shadow: none !important;
}

.tool-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.tool-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.editor-textarea {
    width: 100%;
    min-height: 120px;
    padding: 16px;
    background: transparent;
    border: none;
    color: var(--text);
    font-size: 13px;
    outline: none;
    resize: vertical;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    font-family: inherit;
    box-shadow: none !important;
}

.quick-tags-row {
    padding: 12px 16px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

.quick-tag-chip {
    padding: 4px 10px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.16s ease;
}

.quick-tag-chip.diag {
    color: var(--color-blue, #2563eb);
    border-color: rgba(37, 99, 235, 0.4);
}

.quick-tag-chip.repar {
    color: var(--primary);
    border-color: rgba(79, 70, 229, 0.4);
}

.quick-tag-chip.piezas {
    color: var(--color-yellow, #d97706);
    border-color: rgba(217, 119, 6, 0.4);
}

.quick-tag-chip.pruebas {
    color: var(--color-red, #dc2626);
    border-color: rgba(220, 38, 38, 0.4);
}

.quick-tag-chip:hover {
    background: var(--stroke-subtle);
}

.editor-footer-row {
    padding: 12px 16px;
    display: flex;
    justify-content: flex-end;
    background: var(--bg-sub);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.btn-check-svg {
    width: 16px;
    height: 16px;
}
</style>
