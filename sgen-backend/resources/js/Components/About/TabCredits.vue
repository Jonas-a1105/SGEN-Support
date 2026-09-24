<script setup lang="ts">
// Credits component
</script>

<template>
    <div class="about-subpane-view">
        <div class="about-header-text">
            <h2 class="about-title">Créditos y Licencia</h2>
            <p class="about-description">Información sobre el equipo de ingeniería y licencias de software utilizadas.</p>
        </div>

        <div class="credits-card">
            <div class="credit-section">
                <h3 class="credit-heading">Ingeniería de Software</h3>
                <p class="credit-text">
                    Desarrollado para la optimización de procesos de soporte técnico, gestión de incidencias de TI y trazabilidad de inventarios corporativos.
                </p>
            </div>

            <div class="credit-section">
                <h3 class="credit-heading">Patrones y Estándares de Calidad</h3>
                <ul class="credit-list">
                    <li>Arquitectura Limpia y Diseño Orientado al Dominio (Clean Architecture / DDD).</li>
                    <li>Principio de Responsabilidad Única (SRP) estricto por capa y componente.</li>
                    <li>Cero estilos en línea y tipografía unificada sin forzar mayúsculas.</li>
                    <li>Eliminación total de sombras difuminadas para garantizar una interfaz nítida y moderna.</li>
                </ul>
            </div>

            <div class="credit-section license-section">
                <div class="license-badge">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
                    <span>Licencia MIT</span>
                </div>
                <p class="license-text">
                    Distribuido bajo los términos de la Licencia MIT. Libre para uso, modificación y distribución conforme a las directrices de código abierto.
                </p>
                <span class="copyright-notice">© 2026 SGEN-Support Inc. Todos los derechos reservados.</span>
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
    margin: 0;
}

.credits-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: none !important;
}
.credit-heading {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 8px 0;
}
.credit-text {
    font-size: 13px;
    color: var(--text-muted, #8e9199);
    line-height: 1.5;
    margin: 0;
}
.credit-list {
    margin: 0;
    padding-left: 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    color: var(--text-muted, #8e9199);
    font-size: 13px;
    line-height: 1.4;
}

.license-section {
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    padding-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.license-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    align-self: flex-start;
    padding: 4px 10px;
    border-radius: 6px;
    background: rgba(37, 99, 235, 0.12);
    border: var(--stroke-w, 2px) solid rgba(37, 99, 235, 0.3);
    color: #2563eb;
    font-size: 11px;
    font-weight: 700 !important;
}
.license-badge svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.license-text {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin: 0;
}
.copyright-notice {
    font-size: 11px;
    color: var(--text-dim, #60636d);
}
</style>
