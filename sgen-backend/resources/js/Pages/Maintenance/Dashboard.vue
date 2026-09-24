<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface MaintenanceKpis {
    pending: number;
    inProcess: number;
    completed: number;
    upcoming: number;
    overdue: number;
    total: number;
}

interface MaintenanceItem {
    id: number;
    fecha: string;
    tipoMantenimiento: string;
    estado: string;
    descripcion: string;
    frecuencia: string;
    proximaFecha?: string;
    equipoCodigo?: string;
    equipoTipo?: string;
}

const props = defineProps<{
    kpis: MaintenanceKpis;
    proximos: MaintenanceItem[];
}>();

const formatDate = (dateStr: string) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
};

const tipoLabels: Record<string, string> = {
    preventivo: 'Preventivo',
    correctivo: 'Correctivo',
    predictivo: 'Predictivo',
};

const completionRate = computed(() => {
    if (props.kpis.total === 0) return 0;
    return Math.round((props.kpis.completed / props.kpis.total) * 100);
});
</script>

<template>
    <AppLayout>
        <Head title="Dashboard de Mantenimientos" />

        <div class="maintenance-dashboard">
            <section class="dashboard-header">
                <h1 class="dashboard-title">Dashboard de Mantenimientos</h1>
            </section>

            <section class="kpis-overview">
                <div class="kpi-large">
                    <div class="kpi-circle">
                        <span class="kpi-percent">{{ completionRate }}%</span>
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-name">Tasa de Completitud</div>
                        <div class="kpi-detail">{{ kpis.completed }} de {{ kpis.total }} completados</div>
                    </div>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card orange">
                        <div class="kpi-value">{{ kpis.pending }}</div>
                        <div class="kpi-label">Pendientes</div>
                    </div>
                    <div class="kpi-card blue">
                        <div class="kpi-value">{{ kpis.inProcess }}</div>
                        <div class="kpi-label">En Proceso</div>
                    </div>
                    <div class="kpi-card red">
                        <div class="kpi-value">{{ kpis.overdue }}</div>
                        <div class="kpi-label">Vencidos</div>
                    </div>
                    <div class="kpi-card green">
                        <div class="kpi-value">{{ kpis.upcoming }}</div>
                        <div class="kpi-label">Próximos 30 días</div>
                    </div>
                </div>
            </section>

            <section class="upcoming-section">
                <h2 class="section-title">Próximos Mantenimientos</h2>
                
                <div v-if="proximos.length > 0" class="upcoming-list">
                    <Link
                        v-for="item in proximos"
                        :key="item.id"
                        :href="`/mantenimientos/${item.id}`"
                        class="upcoming-card"
                    >
                        <div class="upcoming-date">
                            <span class="date-day">{{ formatDate(item.proximaFecha || item.fecha).split(' ')[0] }}</span>
                            <span class="date-month">{{ formatDate(item.proximaFecha || item.fecha).split(' ')[1] }}</span>
                        </div>
                        <div class="upcoming-info">
                            <div class="upcoming-equipo">{{ item.equipoCodigo }}</div>
                            <div class="upcoming-tipo">{{ tipoLabels[item.tipoMantenimiento] }}</div>
                            <div class="upcoming-desc">{{ item.descripcion }}</div>
                        </div>
                        <div class="upcoming-badge" :class="item.tipoMantenimiento">
                            {{ tipoLabels[item.tipoMantenimiento] }}
                        </div>
                    </Link>
                </div>

                <div v-else class="empty-upcoming">
                    <p>No hay mantenimientos próximos en los próximos 30 días</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.maintenance-dashboard {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding-bottom: 24px;
}

.dashboard-header {
    margin-bottom: 8px;
}

.dashboard-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.kpis-overview {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
}

.kpi-large {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.kpi-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--orange), var(--red));
    display: grid;
    place-items: center;
}

.kpi-percent {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
}

.kpi-info {
    flex: 1;
}

.kpi-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.kpi-detail {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.kpi-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 16px;
    text-align: center;
}

.kpi-card.orange { border-color: var(--orange); }
.kpi-card.blue { border-color: var(--blue); }
.kpi-card.red { border-color: var(--red); }
.kpi-card.green { border-color: var(--green); }

.kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
}

.kpi-card.orange .kpi-value { color: var(--orange); }
.kpi-card.blue .kpi-value { color: var(--blue); }
.kpi-card.red .kpi-value { color: var(--red); }
.kpi-card.green .kpi-value { color: var(--green); }

.kpi-label {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}

.upcoming-section {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 20px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    margin: 0 0 16px 0;
}

.upcoming-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.upcoming-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    background: var(--bg-elevated);
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.upcoming-card:hover {
    border-color: var(--stroke-hover, var(--orange));
    background: var(--stroke-subtle);
}

.upcoming-date {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 50px;
}

.date-day {
    font-size: 20px;
    font-weight: 700;
    color: var(--orange);
}

.date-month {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
}

.upcoming-info {
    flex: 1;
}

.upcoming-equipo {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.upcoming-tipo {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

.upcoming-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.upcoming-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.upcoming-badge.preventivo {
    background: rgba(34, 197, 94, 0.15);
    color: var(--green);
}

.upcoming-badge.correctivo {
    background: rgba(239, 68, 68, 0.15);
    color: var(--red);
}

.upcoming-badge.predictivo {
    background: rgba(59, 130, 246, 0.15);
    color: var(--blue);
}

.empty-upcoming {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
}

@media (max-width: 900px) {
    .kpis-overview {
        grid-template-columns: 1fr;
    }
}
</style>
