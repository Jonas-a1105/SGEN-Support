<script setup lang="ts">
import { computed } from 'vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import type { BadgeVariant } from '@/Utils/badgeVariants';
import type { BitacoraActionItem } from '@/Composables/useAuditFilters';

const props = defineProps<{
    action: BitacoraActionItem;
    isFirst?: boolean;
    isLast?: boolean;
}>();

const emit = defineEmits<{
    (e: 'inspect', action: BitacoraActionItem): void;
}>();

const formattedTime = computed(() => {
    if (!props.action.created_at) return { time: '--:--', ampm: '' };
    const date = new Date(props.action.created_at);
    if (isNaN(date.getTime())) return { time: props.action.created_at, ampm: '' };

    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const hoursStr = hours.toString().padStart(2, '0');

    return {
        time: `${hoursStr}:${minutes}`,
        ampm,
    };
});

const entityType = computed(() => {
    const raw = (props.action.entidad || props.action.enlace_tipo || '').toLowerCase();
    if (raw.includes('soporte') || raw.includes('ticket')) return 'ticket';
    if (raw.includes('equipo')) return 'equipment';
    if (raw.includes('usuario') || raw.includes('user')) return 'user';
    if (raw.includes('departamento') || raw.includes('dept')) return 'dept';
    if (raw.includes('inventario') || raw.includes('item') || raw.includes('articulo')) return 'inventory';
    if (raw.includes('eliminar') || raw.includes('baja') || raw.includes('seguridad')) return 'security';
    return 'general';
});

const entityLabel = computed(() => {
    const map: Record<string, string> = {
        ticket: 'Soporte',
        equipment: 'Equipo',
        user: 'Usuario',
        dept: 'Departamento',
        inventory: 'Inventario',
        security: 'Seguridad',
        general: 'Sistema',
    };
    return map[entityType.value] || 'Sistema';
});

const entityBadgeVariant = computed<BadgeVariant>(() => {
    switch (entityType.value) {
        case 'ticket': return 'info';
        case 'equipment': return 'warning';
        case 'user': return 'accent';
        case 'dept': return 'neutral';
        case 'inventory': return 'success';
        case 'security': return 'danger';
        default: return 'neutral';
    }
});
</script>

<template>
    <div
        class="timeline-event-row"
        :class="{ 'is-first': isFirst, 'is-last': isLast }"
        @click="emit('inspect', action)"
    >
        <!-- COLUMNA DE LA HORA -->
        <div class="timeline-time-col">
            <span>{{ formattedTime.time }}</span>
            <span class="ampm">{{ formattedTime.ampm }}</span>
        </div>

        <!-- NODO Y BURBUJA CON LÍNEA CONECTORA -->
        <div class="timeline-node-wrapper">
            <div class="timeline-node-circle" :class="`type-${entityType}`">
                <!-- ICONO USUARIO -->
                <svg v-if="entityType === 'user'" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>

                <!-- ICONO TICKET / SOPORTE -->
                <svg v-else-if="entityType === 'ticket'" viewBox="0 0 24 24">
                    <path d="M4 7h16v10H4z" />
                    <line x1="7" y1="7" x2="7" y2="17" />
                    <line x1="17" y1="7" x2="17" y2="17" />
                    <line x1="8" y1="11" x2="16" y2="11" />
                </svg>

                <!-- ICONO EQUIPO -->
                <svg v-else-if="entityType === 'equipment'" viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>

                <!-- ICONO DEPARTAMENTO -->
                <svg v-else-if="entityType === 'dept'" viewBox="0 0 24 24">
                    <path d="M3 21h18" />
                    <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16" />
                    <line x1="9" y1="9" x2="10" y2="9" />
                    <line x1="9" y1="13" x2="10" y2="13" />
                    <line x1="14" y1="9" x2="15" y2="9" />
                    <line x1="14" y1="13" x2="15" y2="13" />
                </svg>

                <!-- ICONO INVENTARIO -->
                <svg v-else-if="entityType === 'inventory'" viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                    <line x1="12" y1="22.08" x2="12" y2="12" />
                </svg>

                <!-- ICONO SEGURIDAD / ELIMINACIÓN -->
                <svg v-else-if="entityType === 'security'" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>

                <!-- ICONO POR DEFECTO -->
                <svg v-else viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        </div>

        <!-- TARJETA DEL EVENTO -->
        <div class="timeline-event-card">
            <div class="timeline-event-content">
                <p class="timeline-event-title">
                    <strong>@{{ action.username }}</strong>
                    <span> {{ action.accion }}</span>
                </p>
                <div class="timeline-event-meta">
                    <BaseBadge :variant="entityBadgeVariant" size="sm">
                        {{ entityLabel }}
                    </BaseBadge>
                    <BaseBadge v-if="action.enlace_id || action.entidad_id" variant="code" size="sm">
                        #{{ action.enlace_id || action.entidad_id }}
                    </BaseBadge>
                    <span class="dot-sep">•</span>
                    <span class="meta-ip">IP: {{ action.ip_address || '127.0.0.1' }}</span>
                </div>
            </div>

            <button
                type="button"
                class="btn-inspect-arrow"
                title="Inspeccionar detalle del evento"
                @click.stop="emit('inspect', action)"
            >
                <svg viewBox="0 0 24 24">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.timeline-event-row {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    cursor: pointer;
}

.timeline-time-col {
    width: 68px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    font-size: 13px;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    line-height: 1.15;
    flex-shrink: 0;
}

.timeline-time-col span.ampm {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
    font-weight: 600 !important;
}

.timeline-node-wrapper {
    position: relative;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 38px;
    height: 38px;
}

/* LÍNEA VERTICAL CONECTORA */
.timeline-event-row::before {
    content: "";
    position: absolute;
    left: 102px;
    top: -14px;
    bottom: -14px;
    width: 2px;
    background: var(--stroke-subtle, #23252a);
    z-index: 1;
}

.timeline-event-row.is-first::before {
    top: 50%;
}

.timeline-event-row.is-last::before {
    bottom: 50%;
}

.timeline-node-circle {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-card, #17181a);
    z-index: 2;
    transition: transform 0.2s ease, border-color 0.2s ease;
    box-shadow: none !important;
}

.timeline-node-circle.type-user {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.35);
}

.timeline-node-circle.type-ticket {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.35);
}

.timeline-node-circle.type-equipment {
    background: rgba(249, 115, 22, 0.12);
    color: #f97316;
    border-color: rgba(249, 115, 22, 0.35);
}

.timeline-node-circle.type-dept {
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
    border-color: rgba(139, 92, 246, 0.35);
}

.timeline-node-circle.type-inventory {
    background: rgba(8, 145, 178, 0.12);
    color: #0891b2;
    border-color: rgba(8, 145, 178, 0.35);
}

.timeline-node-circle.type-security {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.35);
}

.timeline-node-circle.type-general {
    background: var(--stroke-subtle, #23252a);
    color: var(--text-muted, #8e9199);
}

.timeline-node-circle svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.timeline-event-card {
    flex: 1;
    min-width: 0;
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    background: var(--bg-sub, #1e2024);
    border-radius: var(--card-radius, 14px);
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    transition: border-color 0.2s ease, background 0.2s ease;
    box-shadow: none !important;
}

.timeline-event-row:hover .timeline-event-card {
    border-color: var(--stroke, #31343a);
    background: rgba(255, 255, 255, 0.03);
}

.timeline-event-row:hover .timeline-node-circle {
    transform: scale(1.08);
}

.timeline-event-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.timeline-event-title {
    font-size: 13px;
    color: var(--text, #f4f4f6);
    line-height: 1.4;
    margin: 0;
    word-break: break-word;
}

.timeline-event-title strong {
    color: var(--orange, #4f46e5);
    font-weight: 700 !important;
}

.timeline-event-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--text-muted, #8e9199);
    flex-wrap: wrap;
}

.dot-sep {
    color: var(--text-dim, #60636d);
}

.meta-ip {
    font-family: var(--font-mono, monospace);
    color: var(--text-dim, #60636d);
}

.btn-inspect-arrow {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    background: var(--bg-card, #17181a);
    color: var(--text-muted, #8e9199);
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: none !important;
}

.timeline-event-row:hover .btn-inspect-arrow {
    background: var(--orange, #4f46e5);
    border-color: var(--orange, #4f46e5);
    color: #ffffff;
}

.btn-inspect-arrow svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}

@media (max-width: 768px) {
    .timeline-time-col {
        width: 52px;
        font-size: 11px;
    }
    .timeline-event-row::before {
        left: 86px;
    }
    .timeline-event-card {
        padding: 10px 14px;
    }
}
</style>
