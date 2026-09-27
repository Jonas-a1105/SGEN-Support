<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { MyWorkData } from '@/Types/DashboardMetrics';

/**
 * Vista personal del día: lo que el usuario tiene que resolver HOY,
 * encima del panorama global que sigue vivo debajo.
 */
defineProps<{ myWork: MyWorkData }>();
</script>

<template>
    <section class="mywork-card">
        <header class="card-head">
            <h3>Mi trabajo hoy</h3>
            <span class="sub">Tu operación del día, en directo</span>
        </header>

        <div class="chips-row">
            <span class="chip chip-pendiente">
                <b class="chip-numero">{{ myWork.mis_pendientes }}</b>
                <span class="chip-label">tus pendientes</span>
            </span>
            <span class="chip chip_proceso">
                <b class="chip-numero">{{ myWork.mis_en_proceso }}</b>
                <span class="chip-label">en proceso</span>
            </span>
            <span class="chip chip_resueltos">
                <b class="chip-numero">{{ myWork.mis_resueltos_mes }}</b>
                <span class="chip-label">cerrados este mes</span>
            </span>
            <span class="chip chip_proximas">
                <b class="chip-numero">{{ myWork.proximas_ordenes_mias }}</b>
                <span class="chip-label">próximas órdenes</span>
            </span>
        </div>

        <div v-if="myWork.mis_equipos.length > 0" class="equipos-linea">
            <h4>A tu cargo ahora mismo</h4>
            <ul>
                <li v-for="equipo in myWork.mis_equipos" :key="equipo.id" class="equipo-fila">
                    <code>{{ equipo.codigo }}</code>
                    <span>{{ equipo.nombre }}</span>
                    <Link :href="'/equipos/' + equipo.id" class="ver-ficha">Ficha</Link>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.mywork-card {
    padding: 18px 20px;
    background: var(--bg-card);
    border: 2px solid var(--stroke);
    border-radius: 14px;
    margin-bottom: var(--space-4);
}
.card-head { display: flex; flex-direction: column; gap: 2px; margin-bottom: 12px; }
.card-head h3 { margin: 0; font-size: 15px; color: var(--text); }
.sub { font-size: 12px; color: var(--text-muted); }
.chips-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
.chip { display: flex; flex-direction: column; gap: 2px; padding: 10px 12px; border-radius: 11px; text-align: center; font-size: 11px; }
.chip-numero { font-size: 20px; font-weight: 700; }
.chip-pendiente { background: rgba(245, 158, 11, 0.1); color: #b45309; }
.chip_proceso { background: rgba(59, 130, 246, 0.1); color: var(--blue, #2563eb); }
.chip_resueltos { background: rgba(16, 185, 129, 0.1); color: #059669; }
.chip_proximas { background: rgba(217, 70, 239, 0.08); color: #a21caf; }
.equipos-linea h4 { margin: 0 0 6px; font-size: 12px; letter-spacing: 0.05em; text-transform: uppercase; color: var(--text-muted); }
.equipo-fila { display: flex; gap: 12px; align-items: center; font-size: 12.5px; padding: 7px 10px; border-radius: 9px; background: var(--bg-sub); margin-bottom: 4px; }
.equipo-fila code { font-family: monospace; color: var(--orange); font-size: 12px; }
.ver-ficha { margin-left: auto; font-size: 11.5px; color: var(--color-primary, #10b981); text-decoration: none; }
</style>
