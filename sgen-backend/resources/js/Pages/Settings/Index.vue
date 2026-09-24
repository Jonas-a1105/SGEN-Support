<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TabGeneral from '@/Components/Settings/TabGeneral.vue';
import TabApariencia from '@/Components/Settings/TabApariencia.vue';
import TabNotificaciones from '@/Components/Settings/TabNotificaciones.vue';
import TabSeguridad from '@/Components/Settings/TabSeguridad.vue';
import TabMiCuenta from '@/Components/Settings/TabMiCuenta.vue';

interface SettingsData {
    language: string;
    timezone: string;
    date_format: string;
    theme: string;
    stroke_width: string;
    accent_color: string;
    push_enabled: boolean;
    email_tickets_enabled: boolean;
    email_weekly_digest: boolean;
}

const props = defineProps<{
    settings: SettingsData;
}>();

const activeTab = ref<'general' | 'apariencia' | 'notificaciones' | 'seguridad' | 'cuenta'>('apariencia');

function handleSaveSettings(payload: Partial<SettingsData>) {
    router.put('/configuracion', payload, {
        preserveScroll: true,
    });
}

function handleUpdatePassword(payload: {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
}) {
    router.post('/configuracion/password', payload, {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Configuración del Sistema" />

        <div class="module-wrapper">
            <div class="config-layout-grid">
                <!-- Left Nav Column -->
                <div class="config-sidebar-col">
                    <div class="config-nav-wrap">
                        <div class="config-header-box">
                            <div class="config-gear-badge">
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" /><path d="M19 12a7 7 0 0 0-.12-1.3l2-1.55-2-3.46-2.25.9A7.1 7.1 0 0 0 14.4 5L14 2h-4l-.4 3a7.1 7.1 0 0 0-2.23 1.6l-2.25-.9-2 3.46 2 1.55A7 7 0 0 0 5 12c0 .44.04.87.12 1.3l-2 1.55 2 3.46 2.25-.9A7.1 7.1 0 0 0 9.6 19l.4 3h4l.4-3a7.1 7.1 0 0 0 2.23-1.6l2.25.9 2-3.46-2-1.55c.08-.42.12-.86.12-1.3z" /></svg>
                            </div>
                            <div>
                                <h2 class="config-title">Configuración</h2>
                                <span class="config-subtitle">Gestiona tus preferencias globales.</span>
                            </div>
                        </div>

                        <div class="config-nav-list" role="tablist">
                            <button
                                class="config-tab-btn"
                                :class="{ active: activeTab === 'general' }"
                                type="button"
                                @click="activeTab = 'general'"
                            >
                                <div class="tab-icon">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><line x1="2" y1="12" x2="22" y2="12" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" /></svg>
                                </div>
                                <div class="config-tab-copy">
                                    <strong>General</strong>
                                    <span>Idioma y región</span>
                                </div>
                            </button>

                            <button
                                class="config-tab-btn"
                                :class="{ active: activeTab === 'apariencia' }"
                                type="button"
                                @click="activeTab = 'apariencia'"
                            >
                                <div class="tab-icon">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83M16.62 12l-5.74 9.94" /></svg>
                                </div>
                                <div class="config-tab-copy">
                                    <strong>Apariencia</strong>
                                    <span>Temas y colores</span>
                                </div>
                            </button>

                            <button
                                class="config-tab-btn"
                                :class="{ active: activeTab === 'notificaciones' }"
                                type="button"
                                @click="activeTab = 'notificaciones'"
                            >
                                <div class="tab-icon">
                                    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" /><path d="M13.73 21a2 2 0 0 1-3.46 0" /></svg>
                                </div>
                                <div class="config-tab-copy">
                                    <strong>Notificaciones</strong>
                                    <span>Alertas y correos</span>
                                </div>
                            </button>

                            <button
                                class="config-tab-btn"
                                :class="{ active: activeTab === 'seguridad' }"
                                type="button"
                                @click="activeTab = 'seguridad'"
                            >
                                <div class="tab-icon">
                                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
                                </div>
                                <div class="config-tab-copy">
                                    <strong>Seguridad</strong>
                                    <span>Contraseña y acceso</span>
                                </div>
                            </button>

                            <button
                                class="config-tab-btn"
                                :class="{ active: activeTab === 'cuenta' }"
                                type="button"
                                @click="activeTab = 'cuenta'"
                            >
                                <div class="tab-icon">
                                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg>
                                </div>
                                <div class="config-tab-copy">
                                    <strong>Mi Cuenta</strong>
                                    <span>Perfil y datos</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="system-version-tag">
                        Versión del Sistema v1.0.28
                    </div>
                </div>

                <!-- Right Content Column -->
                <div class="config-content-col">
                    <TabGeneral
                        v-if="activeTab === 'general'"
                        :language="settings.language"
                        :timezone="settings.timezone"
                        :date-format="settings.date_format"
                        @save="handleSaveSettings"
                    />

                    <TabApariencia
                        v-else-if="activeTab === 'apariencia'"
                        :theme="settings.theme"
                        :stroke-width="settings.stroke_width"
                        :accent-color="settings.accent_color"
                        @save="handleSaveSettings"
                    />

                    <TabNotificaciones
                        v-else-if="activeTab === 'notificaciones'"
                        :push-enabled="settings.push_enabled"
                        :email-tickets-enabled="settings.email_tickets_enabled"
                        :email-weekly-digest="settings.email_weekly_digest"
                        @save="handleSaveSettings"
                    />

                    <TabSeguridad
                        v-else-if="activeTab === 'seguridad'"
                        @update-password="handleUpdatePassword"
                    />

                    <TabMiCuenta
                        v-else-if="activeTab === 'cuenta'"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.module-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
.config-layout-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 860px) {
    .config-layout-grid {
        grid-template-columns: 1fr;
    }
}

.config-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.config-nav-wrap {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    padding: 16px;
    box-shadow: none !important;
}
.config-header-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    margin-bottom: 14px;
}
.config-gear-badge {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #4f46e5;
    color: #ffffff;
    display: grid;
    place-items: center;
    box-shadow: none !important;
}
.config-gear-badge svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.config-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}
.config-subtitle {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.config-nav-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.config-tab-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    border: var(--stroke-w, 2px) solid transparent;
    background: transparent;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    text-align: left;
    box-shadow: none !important;
    transition: all 0.15s ease;
}
.tab-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--bg-sub, #1e2024);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.tab-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.config-tab-copy {
    display: flex;
    flex-direction: column;
}
.config-tab-copy strong {
    font-size: 13px;
    color: var(--text, #f4f4f6);
}
.config-tab-copy span {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}
.config-tab-btn:hover {
    background: var(--bg-sub, #1e2024);
    color: var(--text, #f4f4f6);
}
.config-tab-btn.active {
    background: var(--bg-sub, #1e2024);
    border-color: #4f46e5;
    color: var(--text, #f4f4f6);
}
.config-tab-btn.active .tab-icon {
    background: #4f46e5;
    color: #ffffff;
}

.system-version-tag {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 12px;
    padding: 10px;
    text-align: center;
    font-size: 11px;
    color: var(--text-dim, #60636d);
    box-shadow: none !important;
}
</style>
