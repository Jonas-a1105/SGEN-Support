<script setup lang="ts">
defineProps<{
    environment: Record<string, string>;
}>();
</script>

<template>
    <div class="about-subpane-view">
        <div class="about-header-text">
            <h2 class="about-title">Información Técnica</h2>
            <p class="about-description">Detalles del entorno de ejecución, servidor y base de datos.</p>
        </div>

        <div class="tech-table-card">
            <div class="tech-row">
                <span class="tech-label">Sistema Operativo</span>
                <span class="tech-val">{{ environment.os || 'Windows / Linux' }}</span>
            </div>
            <div class="tech-row">
                <span class="tech-label">Software de Servidor</span>
                <span class="tech-val">{{ environment.server || 'Apache / PHP Built-in' }}</span>
            </div>
            <div class="tech-row">
                <span class="tech-label">Versión de PHP</span>
                <span class="tech-val code">{{ environment.php_version }}</span>
            </div>
            <div class="tech-row">
                <span class="tech-label">Motor de Base de Datos</span>
                <span class="tech-val">{{ environment.database }}</span>
            </div>
            <div class="tech-row">
                <span class="tech-label">Límite de Memoria (memory_limit)</span>
                <span class="tech-val code">{{ environment.memory_limit }}</span>
            </div>
            <div class="tech-row">
                <span class="tech-label">Tamaño Máximo de Subida (upload_max_filesize)</span>
                <span class="tech-val code">{{ environment.upload_max_filesize }}</span>
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
.tech-table-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: none !important;
}
.tech-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}
.tech-row:last-child {
    border-bottom: none;
}
.tech-label {
    font-size: 13px;
    color: var(--text-muted, #8e9199);
}
.tech-val {
    font-size: 13px;
    font-weight: 600 !important;
    color: var(--text, #f4f4f6);
}
.tech-val.code {
    font-family: monospace;
    background: var(--bg-card, #17181a);
    padding: 2px 8px;
    border-radius: 6px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
}
</style>
