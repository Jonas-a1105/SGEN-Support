<script setup lang="ts">
import { computed } from 'vue';
import type { BitacoraActionItem } from '@/Composables/useAuditFilters';
import BitacoraEventRow from '@/Components/Audit/BitacoraEventRow.vue';

const props = defineProps<{
    actions: BitacoraActionItem[];
}>();

const emit = defineEmits<{
    (e: 'inspect', action: BitacoraActionItem): void;
}>();

interface DateGroup {
    dateKey: string;
    displayDate: string;
    items: BitacoraActionItem[];
}

const groupedActions = computed<DateGroup[]>(() => {
    const groups: Record<string, BitacoraActionItem[]> = {};

    props.actions.forEach((act) => {
        let key = 'Sin fecha';
        if (act.created_at) {
            const rawDate = act.created_at.substring(0, 10);
            key = rawDate;
        }
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(act);
    });

    const now = new Date();
    const todayStr = now.toISOString().substring(0, 10);
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().substring(0, 10);

    return Object.keys(groups).map((key) => {
        let display = key;
        if (key === todayStr) {
            display = 'HOY — ' + formatDateHeading(key);
        } else if (key === yesterdayStr) {
            display = 'AYER — ' + formatDateHeading(key);
        } else if (key !== 'Sin fecha') {
            display = formatDateHeading(key);
        }

        return {
            dateKey: key,
            displayDate: display,
            items: groups[key],
        };
    });
});

function formatDateHeading(dateStr: string): string {
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const year = parts[0];
    const monthIndex = parseInt(parts[1], 10) - 1;
    const day = parts[2];
    const months = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    const month = months[monthIndex] || parts[1];
    return `${day} ${month} ${year}`;
}
</script>

<template>
    <div class="audit-card-panel">
        <template v-if="groupedActions.length > 0">
            <div
                v-for="group in groupedActions"
                :key="group.dateKey"
                class="timeline-date-section"
            >
                <!-- SEPARADOR DE FECHA -->
                <div class="date-separator-wrap">
                    <span class="date-label-subtle">PERIODO</span>
                    <span class="date-badge-pill">{{ group.displayDate }}</span>
                    <div class="date-line-filler"></div>
                </div>

                <!-- CONTENEDOR DEL GRUPO DE TIMELINE -->
                <div class="timeline-group-container">
                    <BitacoraEventRow
                        v-for="(action, idx) in group.items"
                        :key="action.id"
                        :action="action"
                        :is-first="idx === 0"
                        :is-last="idx === group.items.length - 1"
                        @inspect="emit('inspect', $event)"
                    />
                </div>
            </div>
        </template>

        <!-- EMPTY STATE -->
        <div v-else class="empty-state">
            <div class="empty-icon-box">
                <svg viewBox="0 0 24 24">
                    <polyline points="12 8 12 12 14 14" />
                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                </svg>
            </div>
            <h3 class="empty-title">Sin movimientos registrados</h3>
            <p class="empty-desc">
                No se encontraron acciones operacionales que coincidan con la búsqueda o el filtro seleccionado.
            </p>
        </div>
    </div>
</template>

<style scoped>
.audit-card-panel {
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-card, #17181a);
    border-radius: var(--panel-radius, 18px);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    box-shadow: none !important;
}

.timeline-date-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.date-separator-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 4px;
    margin-bottom: 4px;
}

.date-label-subtle {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-dim, #60636d);
    font-weight: 700 !important;
}

.date-badge-pill {
    padding: 4px 14px;
    border-radius: 20px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    font-size: 12px;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    letter-spacing: 0.02em;
    white-space: nowrap;
}

.date-line-filler {
    flex: 1;
    height: 1px;
    background: var(--stroke-subtle, #23252a);
}

.timeline-group-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    padding-left: 6px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px;
    gap: 12px;
}

.empty-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--stroke-subtle, #23252a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    display: grid;
    place-items: center;
    color: var(--text-muted, #8e9199);
}

.empty-icon-box svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.empty-title {
    font-size: 15px;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}

.empty-desc {
    font-size: 13px;
    color: var(--text-muted, #8e9199);
    max-width: 360px;
    margin: 0;
    line-height: 1.5;
}
</style>
