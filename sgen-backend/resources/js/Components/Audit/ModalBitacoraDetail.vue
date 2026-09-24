<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import type { BitacoraActionItem } from '@/Composables/useAuditFilters';

const props = defineProps<{
    show: boolean;
    action: BitacoraActionItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const entityLink = computed(() => {
    if (!props.action) return null;
    const type = (props.action.enlace_tipo || props.action.entidad || '').toLowerCase();
    const id = props.action.enlace_id || props.action.entidad_id;
    if (!id) return null;

    if (type.includes('soporte') || type.includes('ticket')) {
        return { label: `Ver Soporte #${id}`, url: `/soportes/${id}` };
    }
    if (type.includes('equipo')) {
        return { label: `Ver Equipo #${id}`, url: `/equipos` };
    }
    if (type.includes('usuario')) {
        return { label: `Ver Directorio Usuarios`, url: `/usuarios` };
    }
    if (type.includes('departamento')) {
        return { label: `Ver Departamentos`, url: `/departamentos` };
    }
    if (type.includes('inventario')) {
        return { label: `Ver Inventario`, url: `/inventario` };
    }
    return null;
});

function navigateToEntity(url: string) {
    emit('close');
    router.visit(url);
}

function formatJson(val: Record<string, unknown> | null): string {
    if (!val) return 'Sin cambios registrados';
    try {
        return JSON.stringify(val, null, 2);
    } catch {
        return String(val);
    }
}
</script>

<template>
    <BaseModal
        :is-open="show && !!action"
        :title="action ? `Detalle de Operación #${action.id}` : 'Detalle de Operación'"
        max-width="lg"
        @close="emit('close')"
    >
        <div v-if="action" class="bitacora-detail-content">
            <!-- GRID DE METADATOS CLAVE -->
            <div class="meta-grid">
                <div class="meta-card">
                    <span class="meta-label">Usuario</span>
                    <b class="meta-val user-accent">@{{ action.username }}</b>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Módulo</span>
                    <b class="meta-val">{{ action.entidad || action.enlace_tipo || 'General' }}</b>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Dirección IP</span>
                    <b class="meta-val mono">{{ action.ip_address || '127.0.0.1' }}</b>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Fecha y Hora</span>
                    <b class="meta-val">{{ action.created_at }}</b>
                </div>
            </div>

            <!-- ACCIÓN REALIZADA -->
            <div class="action-banner">
                <span class="meta-label">Descripción de la Operación</span>
                <p class="action-text">{{ action.accion }}</p>
            </div>

            <!-- INSPECTOR DE CAMBIOS (DIFF) -->
            <div v-if="action.datos_anteriores || action.datos_nuevos" class="diff-section">
                <span class="meta-label">Trazabilidad de Cambios</span>
                <div class="diff-columns">
                    <div class="diff-box">
                        <div class="diff-header old">Estado Anterior</div>
                        <pre class="json-content">{{ formatJson(action.datos_anteriores) }}</pre>
                    </div>
                    <div class="diff-box">
                        <div class="diff-header new">Estado Nuevo</div>
                        <pre class="json-content">{{ formatJson(action.datos_nuevos) }}</pre>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="modal-footer-inner">
                <button
                    v-if="entityLink"
                    type="button"
                    class="btn-entity-link"
                    @click="navigateToEntity(entityLink.url)"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                    <span>{{ entityLink.label }}</span>
                </button>
                <div v-else></div>

                <button
                    type="button"
                    class="btn-cancel"
                    @click="emit('close')"
                >
                    Cerrar
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
.bitacora-detail-content {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

@media (max-width: 580px) {
    .meta-grid {
        grid-template-columns: 1fr;
    }
}

.meta-card {
    background: var(--bg-sub);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-dim);
    font-weight: 700;
}

.meta-val {
    font-size: 13px;
    color: var(--text);
}

.meta-val.user-accent {
    color: var(--orange, #4f46e5);
}

.meta-val.mono {
    font-family: var(--font-mono, monospace);
    font-size: 12px;
}

.action-banner {
    background: var(--bg-sub);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle);
    border-radius: 12px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.action-text {
    font-size: 13px;
    color: var(--text);
    margin: 0;
    line-height: 1.5;
}

.diff-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.diff-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

@media (max-width: 580px) {
    .diff-columns {
        grid-template-columns: 1fr;
    }
}

.diff-box {
    background: var(--bg-sub);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.diff-header {
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle);
}

.diff-header.old {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
}

.diff-header.new {
    color: #10b981;
    background: rgba(16, 185, 129, 0.08);
}

.json-content {
    padding: 12px;
    margin: 0;
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    color: var(--text);
    overflow-x: auto;
    max-height: 180px;
    white-space: pre-wrap;
    word-break: break-all;
}

.modal-footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.btn-entity-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--accent-soft, rgba(79, 70, 229, 0.12));
    border: var(--stroke-w, 2px) solid var(--orange, #4f46e5);
    color: var(--orange, #4f46e5);
    font-size: 12px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}

.btn-entity-link:hover {
    background: var(--orange, #4f46e5);
    color: #ffffff;
}

.btn-entity-link svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}
</style>
