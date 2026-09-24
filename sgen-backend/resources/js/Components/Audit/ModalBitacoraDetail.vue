<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
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
    <div v-if="show && action" class="modal-backdrop" @click="emit('close')">
        <div class="modal-dialog" @click.stop>
            <!-- CABECERA MODAL -->
            <header class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon-badge">
                        <svg viewBox="0 0 24 24">
                            <polyline points="12 8 12 12 14 14" />
                            <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="modal-title">Detalle de Operación</h2>
                        <span class="modal-subtitle">Registro #{{ action.id }} en Bitácora</span>
                    </div>
                </div>
                <button
                    type="button"
                    class="btn-close"
                    title="Cerrar modal"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </header>

            <!-- CUERPO MODAL -->
            <div class="modal-body">
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

            <!-- PIE MODAL -->
            <footer class="modal-footer">
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
                    class="btn-close-action"
                    @click="emit('close')"
                >
                    Cerrar
                </button>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.72);
    backdrop-filter: blur(4px);
    z-index: 100;
    display: grid;
    place-items: center;
    padding: 16px;
    box-shadow: none !important;
}

.modal-dialog {
    width: 100%;
    max-width: 640px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    box-shadow: none !important;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.modal-title-group {
    display: flex;
    align-items: center;
    gap: 14px;
}

.modal-icon-badge {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--accent-soft, rgba(79, 70, 229, 0.12));
    border: var(--stroke-w, 2px) solid var(--orange, #4f46e5);
    color: var(--orange, #4f46e5);
    display: grid;
    place-items: center;
}

.modal-icon-badge svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.modal-title {
    font-size: 16px;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}

.modal-subtitle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}

.btn-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    background: var(--bg-sub, #1e2024);
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.btn-close:hover {
    color: var(--text, #f4f4f6);
    border-color: var(--stroke-hover, #454952);
}

.modal-body {
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.meta-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
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
    color: var(--text-muted, #8e9199);
    font-weight: 700 !important;
}

.meta-val {
    font-size: 13px;
    color: var(--text, #f4f4f6);
}

.user-accent {
    color: var(--orange, #4f46e5);
}

.mono {
    font-family: var(--font-mono, monospace);
}

.action-banner {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.action-text {
    font-size: 14px;
    font-weight: 600;
    color: var(--text, #f4f4f6);
    margin: 0;
    line-height: 1.4;
}

.diff-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.diff-columns {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.diff-box {
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 10px;
    background: var(--bg, #111214);
    overflow: hidden;
}

.diff-header {
    font-size: 11px;
    font-weight: 700 !important;
    text-transform: uppercase;
    padding: 6px 10px;
    letter-spacing: 0.05em;
}

.diff-header.old {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.diff-header.new {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.json-content {
    margin: 0;
    padding: 10px;
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    color: var(--text-muted, #8e9199);
    max-height: 160px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    background: var(--bg-card, #17181a);
}

.btn-entity-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 10px;
    border: var(--stroke-w, 2px) solid var(--orange, #4f46e5);
    background: var(--accent-soft, rgba(79, 70, 229, 0.12));
    color: var(--orange, #4f46e5);
    font-size: 12px;
    font-weight: 700 !important;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
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
    stroke-width: 2;
}

.btn-close-action {
    padding: 8px 18px;
    border-radius: 10px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-sub, #1e2024);
    color: var(--text, #f4f4f6);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.btn-close-action:hover {
    background: var(--stroke-subtle, #23252a);
    border-color: var(--stroke-hover, #454952);
}

@media (max-width: 640px) {
    .meta-grid,
    .diff-columns {
        grid-template-columns: 1fr;
    }
}
</style>
