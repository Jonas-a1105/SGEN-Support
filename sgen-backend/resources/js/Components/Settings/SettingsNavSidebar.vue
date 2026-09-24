<script setup lang="ts">
export type SettingsTabKey = 'general' | 'apariencia' | 'notificaciones' | 'seguridad' | 'cuenta';

defineProps<{
    modelValue: SettingsTabKey;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', key: SettingsTabKey): void;
}>();
</script>

<template>
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
                :class="{ active: modelValue === 'general' }"
                type="button"
                @click="emit('update:modelValue', 'general')"
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
                :class="{ active: modelValue === 'apariencia' }"
                type="button"
                @click="emit('update:modelValue', 'apariencia')"
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
                :class="{ active: modelValue === 'notificaciones' }"
                type="button"
                @click="emit('update:modelValue', 'notificaciones')"
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
                :class="{ active: modelValue === 'seguridad' }"
                type="button"
                @click="emit('update:modelValue', 'seguridad')"
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
                :class="{ active: modelValue === 'cuenta' }"
                type="button"
                @click="emit('update:modelValue', 'cuenta')"
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
</template>

<style scoped>
.config-nav-wrap {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.config-header-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    box-shadow: none !important;
}

.config-gear-badge {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: var(--text);
    flex-shrink: 0;
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
    color: var(--text);
    margin: 0;
}

.config-subtitle {
    font-size: 11px;
    color: var(--text-muted);
}

.config-nav-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.config-tab-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 12px;
    border: var(--stroke-w) solid transparent;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s ease;
    box-shadow: none !important;
}

.config-tab-btn:hover {
    background: var(--bg-card);
    border-color: var(--stroke);
}

.config-tab-btn.active {
    background: var(--bg-card);
    border-color: var(--stroke);
}

.tab-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.15s ease;
}

.tab-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.config-tab-btn.active .tab-icon {
    background: var(--blue, #2563eb);
    color: #ffffff;
    border-color: var(--blue, #2563eb);
}

.config-tab-copy {
    display: flex;
    flex-direction: column;
}

.config-tab-copy strong {
    font-size: 13px;
    color: var(--text);
}

.config-tab-copy span {
    font-size: 11px;
    color: var(--text-muted);
}
</style>
