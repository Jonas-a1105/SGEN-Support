<script setup lang="ts">
export interface TechnicalCard {
    kicker: string;
    title: string;
    subtitle: string;
    type: string;
}

defineProps<{
    technicalCards: TechnicalCard[];
    systemStatus: string;
}>();
</script>

<template>
    <div class="about-subpane-view">
        <div class="about-header-text">
            <h1 class="about-title">Acerca del Sistema</h1>
            <p class="about-description">
                Sistema de Gestión de Soporte Técnico y Mantenimiento. Centraliza incidencias, inventario y personal en una plataforma unificada y escalable.
            </p>
        </div>

        <!-- Cuadrícula de 4 tarjetas técnicas -->
        <div class="tech-specs-grid">
            <div
                v-for="card in technicalCards"
                :key="card.kicker"
                class="tech-spec-card"
            >
                <div class="tech-spec-icon-box" :class="card.type">
                    <svg v-if="card.type === 'backend'" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6" /><polyline points="8 6 2 12 8 18" /></svg>
                    <svg v-else-if="card.type === 'frontend'" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" /><line x1="3" y1="9" x2="21" y2="9" /><line x1="9" y1="21" x2="9" y2="9" /></svg>
                    <svg v-else-if="card.type === 'database'" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" /><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" /></svg>
                    <svg v-else viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" /><rect x="9" y="9" width="6" height="6" /><line x1="9" y1="1" x2="9" y2="4" /><line x1="15" y1="1" x2="15" y2="4" /><line x1="9" y1="20" x2="9" y2="23" /><line x1="15" y1="20" x2="15" y2="23" /><line x1="20" y1="9" x2="23" y2="9" /><line x1="20" y1="15" x2="23" y2="15" /><line x1="1" y1="9" x2="4" y2="9" /><line x1="1" y1="15" x2="4" y2="15" /></svg>
                </div>
                <div class="tech-spec-copy">
                    <span class="tech-spec-kicker">{{ card.kicker }}</span>
                    <h3 class="tech-spec-title">{{ card.title }}</h3>
                    <span class="tech-spec-sub">{{ card.subtitle }}</span>
                </div>
            </div>
        </div>

        <!-- Banner de estado general -->
        <div class="system-health-banner">
            <div class="health-pulse-icon-box">
                <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12" /></svg>
            </div>
            <div class="health-copy-block">
                <span class="health-kicker">ESTADO GENERAL</span>
                <h3 class="health-status-value">{{ systemStatus }}</h3>
            </div>
        </div>
    </div>
</template>

<style scoped>
.about-header-text {
    margin-bottom: 24px;
}
.about-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 6px 0;
}
.about-description {
    font-size: 13px;
    color: var(--text-muted, #8e9199);
    line-height: 1.5;
    margin: 0;
}

.tech-specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.tech-spec-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: none !important;
}
.tech-spec-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    border: var(--stroke-w, 2px) solid transparent;
    flex-shrink: 0;
    box-shadow: none !important;
}
.tech-spec-icon-box svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tech-spec-icon-box.backend {
    background: rgba(37, 99, 235, 0.12);
    border-color: rgba(37, 99, 235, 0.3);
    color: #2563eb;
}
.tech-spec-icon-box.frontend {
    background: rgba(16, 185, 129, 0.12);
    border-color: rgba(16, 185, 129, 0.3);
    color: #10b981;
}
.tech-spec-icon-box.database {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.3);
    color: #f59e0b;
}
.tech-spec-icon-box.architecture {
    background: rgba(139, 92, 246, 0.12);
    border-color: rgba(139, 92, 246, 0.3);
    color: #8b5cf6;
}

.tech-spec-copy {
    display: flex;
    flex-direction: column;
}
.tech-spec-kicker {
    font-size: 10px;
    letter-spacing: 0.05em;
    color: var(--text-muted, #8e9199);
}
.tech-spec-title {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 2px 0;
}
.tech-spec-sub {
    font-size: 11px;
    color: var(--text-dim, #60636d);
}

.system-health-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: rgba(16, 185, 129, 0.08);
    border: var(--stroke-w, 2px) solid rgba(16, 185, 129, 0.3);
    border-radius: 14px;
    box-shadow: none !important;
}
.health-pulse-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.15);
    display: grid;
    place-items: center;
    color: #10b981;
}
.health-pulse-icon-box svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
}
.health-copy-block {
    display: flex;
    flex-direction: column;
}
.health-kicker {
    font-size: 10px;
    letter-spacing: 0.05em;
    color: var(--text-muted, #8e9199);
}
.health-status-value {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #10b981;
    margin: 2px 0 0 0;
}
</style>
