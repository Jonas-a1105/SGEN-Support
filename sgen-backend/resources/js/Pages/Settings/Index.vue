<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TabGeneral from '@/Components/Settings/TabGeneral.vue';
import TabApariencia from '@/Components/Settings/TabApariencia.vue';
import TabNotificaciones from '@/Components/Settings/TabNotificaciones.vue';
import TabSeguridad from '@/Components/Settings/TabSeguridad.vue';
import TabMiCuenta from '@/Components/Settings/TabMiCuenta.vue';
import SettingsNavSidebar, { type SettingsTabKey } from '@/Components/Settings/SettingsNavSidebar.vue';

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

const activeTab = ref<SettingsTabKey>('apariencia');

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
                    <SettingsNavSidebar v-model="activeTab" />

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
