<script setup lang="ts">
export interface SystemModule {
    name: string;
    description: string;
    icon: string;
}

defineProps<{
    modules: SystemModule[];
}>();
</script>

<template>
    <div class="about-subpane-view">
        <div class="about-header-text">
            <h2 class="about-title">Módulos del Sistema</h2>
            <p class="about-description">Estructura modular activa en esta instancia institucional.</p>
        </div>

        <div class="modules-grid">
            <div
                v-for="mod in modules"
                :key="mod.name"
                class="module-spec-card"
            >
                <div class="module-spec-icon-box">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="14" y="14" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /></svg>
                </div>
                <div class="module-spec-copy">
                    <h3 class="module-spec-title">{{ mod.name }}</h3>
                    <span class="module-spec-desc">{{ mod.description }}</span>
                </div>
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
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
}
.module-spec-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    padding: 18px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: none !important;
}
.module-spec-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(79, 70, 229, 0.12);
    border: var(--stroke-w, 2px) solid rgba(79, 70, 229, 0.3);
    color: #4f46e5;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: none !important;
}
.module-spec-icon-box svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.module-spec-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.module-spec-title {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}
.module-spec-desc {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    line-height: 1.4;
}
</style>
