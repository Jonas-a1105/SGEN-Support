<script setup lang="ts">
export interface VersionEntry {
    version: string;
    date: string;
    notes: string[];
}

defineProps<{
    versions: VersionEntry[];
}>();
</script>

<template>
    <div class="about-subpane-view">
        <div class="about-header-text">
            <h2 class="about-title">Historial de Versiones</h2>
            <p class="about-description">Registro de cambios, mejoras de rendimiento y actualizaciones del sistema.</p>
        </div>

        <div class="versions-timeline">
            <div
                v-for="ver in versions"
                :key="ver.version"
                class="version-card"
            >
                <div class="version-head">
                    <span class="version-tag">{{ ver.version }}</span>
                    <span class="version-date">{{ ver.date }}</span>
                </div>
                <ul class="version-notes-list">
                    <li v-for="(note, idx) in ver.notes" :key="idx" class="note-item">
                        {{ note }}
                    </li>
                </ul>
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

.versions-timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.version-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    padding: 18px;
    box-shadow: none !important;
}
.version-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.version-tag {
    font-size: 13px;
    font-weight: 700 !important;
    color: #2563eb;
    background: rgba(37, 99, 235, 0.12);
    border: var(--stroke-w, 2px) solid rgba(37, 99, 235, 0.3);
    padding: 2px 10px;
    border-radius: 6px;
}
.version-date {
    font-size: 12px;
    color: var(--text-dim, #60636d);
}

.version-notes-list {
    margin: 0;
    padding-left: 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.note-item {
    font-size: 13px;
    color: var(--text-muted, #8e9199);
    line-height: 1.4;
}
</style>
