<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TabOverview, { type TechnicalCard } from '@/Components/About/TabOverview.vue';
import TabModules, { type SystemModule } from '@/Components/About/TabModules.vue';
import TabTechnical from '@/Components/About/TabTechnical.vue';
import TabVersions, { type VersionEntry } from '@/Components/About/TabVersions.vue';
import TabCredits from '@/Components/About/TabCredits.vue';

interface AboutData {
    app_name: string;
    app_version: string;
    system_status: string;
    technical_cards: TechnicalCard[];
    modules: SystemModule[];
    environment: Record<string, string>;
    versions: VersionEntry[];
}

const props = defineProps<{
    about: AboutData;
}>();

type TabKey = 'overview' | 'modules' | 'technical' | 'versions' | 'credits';
const activeTab = ref<TabKey>('overview');

function selectTab(tab: TabKey) {
    activeTab.value = tab;
}
</script>

<template>
    <AppLayout>
        <Head title="Acerca del Sistema" />

        <div class="module-wrapper">
            <div class="about-layout-card">
                <!-- COLUMNA IZQUIERDA: MENÚ DE NAVEGACIÓN Y METADATOS -->
                <div class="about-sidebar-col">
                    <div>
                        <div class="about-brand-wrap">
                            <div class="about-logo-box" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2" /><polyline points="2 17 12 22 22 17" /><polyline points="2 12 12 17 22 12" /></svg>
                            </div>
                            <div class="about-brand-copy">
                                <b>{{ about.app_name }}</b>
                                <span>{{ about.app_version }}</span>
                            </div>
                        </div>

                        <div class="about-nav-list" role="tablist">
                            <button
                                class="about-nav-tab"
                                :class="{ active: activeTab === 'overview' }"
                                type="button"
                                @click="selectTab('overview')"
                            >
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" /></svg>
                                <span>Visión General</span>
                            </button>

                            <button
                                class="about-nav-tab"
                                :class="{ active: activeTab === 'modules' }"
                                type="button"
                                @click="selectTab('modules')"
                            >
                                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="14" y="14" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /></svg>
                                <span>Módulos</span>
                            </button>

                            <button
                                class="about-nav-tab"
                                :class="{ active: activeTab === 'technical' }"
                                type="button"
                                @click="selectTab('technical')"
                            >
                                <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" /><line x1="8" y1="21" x2="16" y2="21" /><line x1="12" y1="17" x2="12" y2="21" /></svg>
                                <span>Info. Técnica</span>
                            </button>

                            <button
                                class="about-nav-tab"
                                :class="{ active: activeTab === 'versions' }"
                                type="button"
                                @click="selectTab('versions')"
                            >
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                                <span>Versiones</span>
                            </button>

                            <button
                                class="about-nav-tab"
                                :class="{ active: activeTab === 'credits' }"
                                type="button"
                                @click="selectTab('credits')"
                            >
                                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                                <span>Créditos</span>
                            </button>
                        </div>
                    </div>

                    <div class="about-footer-col">
                        <span class="license-badge">
                            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
                            <span>Licencia MIT</span>
                        </span>
                        <span>© 2026 SGEN-Support Inc.</span>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: PANELES DE CONTENIDO -->
                <div class="about-main-col">
                    <TabOverview
                        v-if="activeTab === 'overview'"
                        :technical-cards="about.technical_cards"
                        :system-status="about.system_status"
                    />

                    <TabModules
                        v-else-if="activeTab === 'modules'"
                        :modules="about.modules"
                    />

                    <TabTechnical
                        v-else-if="activeTab === 'technical'"
                        :environment="about.environment"
                    />

                    <TabVersions
                        v-else-if="activeTab === 'versions'"
                        :versions="about.versions"
                    />

                    <TabCredits
                        v-else-if="activeTab === 'credits'"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.module-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 8px 0;
}

.about-layout-card {
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-card, #17181a);
    border-radius: 20px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 240px 1fr;
    min-height: 600px;
    box-shadow: none !important;
}

.about-sidebar-col {
    border-right: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    padding: 24px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: var(--bg-card, #17181a);
}

.about-brand-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.about-logo-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #0f172a;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: #ffffff;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: none !important;
}

.about-logo-box svg {
    width: 22px;
    height: 22px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.about-brand-copy b {
    font-size: 14px;
    font-weight: 700;
    color: var(--text, #f4f4f6);
    display: block;
}

.about-brand-copy span {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.about-nav-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.about-nav-tab {
    height: 42px;
    padding: 0 14px;
    border-radius: 12px;
    border: var(--stroke-w, 2px) solid transparent;
    background: transparent;
    color: var(--text-muted, #8e9199);
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    text-align: left;
    width: 100%;
    transition: all 0.18s ease;
    box-shadow: none !important;
}

.about-nav-tab:hover {
    background: var(--stroke-subtle, #23252a);
    color: var(--text, #f4f4f6);
}

.about-nav-tab.active {
    background: var(--bg-sub, #1e2024);
    border-color: var(--stroke, #31343a);
    color: var(--orange, #2563eb);
}

.about-nav-tab svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.about-footer-col {
    padding-top: 16px;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 11px;
    color: var(--text-dim, #60636d);
}

.license-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-muted, #8e9199);
}

.license-badge svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.about-main-col {
    padding: 32px 36px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    overflow-y: auto;
}

@media (max-width: 900px) {
    .about-layout-card {
        grid-template-columns: 1fr;
    }
    .about-sidebar-col {
        border-right: none;
        border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    }
}
</style>
