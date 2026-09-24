<script setup lang="ts">
const emit = defineEmits<{
    (e: 'downloadPreset', preset: 'inventory' | 'maintenance' | 'performance'): void;
}>();
</script>

<template>
    <div class="predefined-section-block">
        <h2 class="panel-title">Reportes Predefinidos</h2>

        <div class="predefined-cards-grid">
            <!-- TARJETA 1: INVENTARIO GENERAL -->
            <div class="predefined-card" @click="emit('downloadPreset', 'inventory')">
                <div class="predefined-card-head">
                    <div class="predefined-icon-box inventory">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                            <line x1="12" y1="22.08" x2="12" y2="12" />
                        </svg>
                    </div>
                    <button class="predefined-download-btn" type="button" title="Descargar reporte rápido">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </button>
                </div>

                <div class="predefined-copy">
                    <h3 class="predefined-title">Inventario General</h3>
                    <p class="predefined-desc">Valoración total, stock por almacén y alertas de stock bajo.</p>
                </div>

                <div class="predefined-footer-row">
                    <span class="predefined-status-time">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                        <span>Actualizado</span>
                    </span>
                    <span class="predefined-action-link">
                        <span>Generar</span>
                        <span>→</span>
                    </span>
                </div>
            </div>

            <!-- TARJETA 2: MANTENIMIENTOS -->
            <div class="predefined-card" @click="emit('downloadPreset', 'maintenance')">
                <div class="predefined-card-head">
                    <div class="predefined-icon-box maintenance">
                        <svg viewBox="0 0 24 24">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                        </svg>
                    </div>
                    <button class="predefined-download-btn" type="button" title="Descargar reporte rápido">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </button>
                </div>

                <div class="predefined-copy">
                    <h3 class="predefined-title">Mantenimientos</h3>
                    <p class="predefined-desc">Historial de reparaciones, costos acumulados y proyecciones.</p>
                </div>

                <div class="predefined-footer-row">
                    <span class="predefined-status-time">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                        <span>Actualizado</span>
                    </span>
                    <span class="predefined-action-link">
                        <span>Generar</span>
                        <span>→</span>
                    </span>
                </div>
            </div>

            <!-- TARJETA 3: RENDIMIENTO DEL EQUIPO -->
            <div class="predefined-card" @click="emit('downloadPreset', 'performance')">
                <div class="predefined-card-head">
                    <div class="predefined-icon-box performance">
                        <svg viewBox="0 0 24 24">
                            <path d="M18 20V10" />
                            <path d="M12 20V4" />
                            <path d="M6 20v-6" />
                        </svg>
                    </div>
                    <button class="predefined-download-btn" type="button" title="Descargar reporte rápido">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </button>
                </div>

                <div class="predefined-copy">
                    <h3 class="predefined-title">Rendimiento del Equipo</h3>
                    <p class="predefined-desc">KPIs de técnicos, tiempos de respuesta y cierre de tickets.</p>
                </div>

                <div class="predefined-footer-row">
                    <span class="predefined-status-time">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                        <span>Tiempo real</span>
                    </span>
                    <span class="predefined-action-link">
                        <span>Generar</span>
                        <span>→</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.predefined-section-block {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.panel-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.predefined-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.predefined-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    cursor: pointer;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.predefined-card:hover {
    border-color: var(--stroke-hover);
}

.predefined-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.predefined-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    box-shadow: none !important;
}

.predefined-icon-box.inventory {
    background: rgba(16, 185, 129, 0.14);
    color: #10b981;
}

.predefined-icon-box.maintenance {
    background: rgba(245, 158, 11, 0.14);
    color: #f59e0b;
}

.predefined-icon-box.performance {
    background: rgba(99, 102, 241, 0.14);
    color: #6366f1;
}

.predefined-icon-box svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.predefined-download-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke-subtle);
    background: var(--bg-sub);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: all 0.18s ease;
    box-shadow: none !important;
}

.predefined-download-btn:hover {
    color: var(--text);
    border-color: var(--stroke);
}

.predefined-download-btn svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.predefined-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.predefined-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.predefined-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.45;
}

.predefined-footer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    font-size: 11px;
    color: var(--text-dim);
}

.predefined-status-time {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.predefined-status-time svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.predefined-action-link {
    font-size: 12px;
    font-weight: 700;
    color: #6366f1;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
}

@media (max-width: 980px) {
    .predefined-cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>
