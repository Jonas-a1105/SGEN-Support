<script setup lang="ts">
// TechnicalProtocolCard - Prescribes formal IT maintenance steps
</script>

<template>
    <div class="protocol-card">
        <svg class="protocol-watermark" viewBox="0 0 24 24">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <polyline points="9 12 11 14 15 10"></polyline>
        </svg>
        <div>
            <h3 class="protocol-title">Protocolo Técnico</h3>
            <p class="protocol-desc">Sigue estos pasos para garantizar la calidad del servicio y la auditoría.</p>
        </div>
        <div class="protocol-steps-list">
            <div class="protocol-step-item">
                <span class="protocol-step-badge">1</span>
                <span>Documenta el estado inicial del equipo (daños físicos, suciedad).</span>
            </div>
            <div class="protocol-step-item">
                <span class="protocol-step-badge">2</span>
                <span>Si reemplazas piezas, anota el número de parte antiguo y nuevo.</span>
            </div>
            <div class="protocol-step-item">
                <span class="protocol-step-badge">3</span>
                <span>Adjunta fotos del "Antes" y "Después" en la pestaña de Archivos.</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.protocol-card {
    border: var(--stroke-w) solid var(--primary);
    border-radius: var(--panel-radius);
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(79, 70, 229, 0.75));
    color: #ffffff;
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: relative;
    overflow: hidden;
    box-shadow: none !important;
}
.protocol-watermark {
    position: absolute;
    right: -10px;
    bottom: -10px;
    width: 90px;
    height: 90px;
    stroke: #ffffff;
    fill: none;
    stroke-width: 1.5;
    opacity: 0.12;
    pointer-events: none;
}
.protocol-title {
    margin: 0 0 4px 0;
    font-size: 15px !important;
    font-weight: 700 !important;
}
.protocol-desc {
    margin: 0;
    font-size: 12px;
    opacity: 0.85;
    line-height: 1.4;
}
.protocol-steps-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.protocol-step-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 12px;
    line-height: 1.4;
}
.protocol-step-badge {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: grid;
    place-items: center;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}
</style>
