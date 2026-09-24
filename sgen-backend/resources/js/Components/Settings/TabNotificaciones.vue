<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    pushEnabled: boolean;
    emailTicketsEnabled: boolean;
    emailWeeklyDigest: boolean;
}>();

const emit = defineEmits<{
    (e: 'save', payload: {
        push_enabled: boolean;
        email_tickets_enabled: boolean;
        email_weekly_digest: boolean;
    }): void;
}>();

const push = ref(props.pushEnabled);
const emailTickets = ref(props.emailTicketsEnabled);
const weeklyDigest = ref(props.emailWeeklyDigest);

function save() {
    emit('save', {
        push_enabled: push.value,
        email_tickets_enabled: emailTickets.value,
        email_weekly_digest: weeklyDigest.value,
    });
}
</script>

<template>
    <div class="settings-subpane">
        <div class="subpane-header">
            <h3 class="section-title">Notificaciones</h3>
            <span class="section-subtitle">Configura qué tipo de avisos deseas recibir en el sistema y por correo.</span>
        </div>

        <form class="settings-form" @submit.prevent="save">
            <div class="notification-option-row">
                <div class="option-copy">
                    <strong class="option-title">Notificaciones Push en Navegador</strong>
                    <span class="option-desc">Recibe alertas en tiempo real sobre asignación de tickets o cambios de estado.</span>
                </div>
                <label class="switch-wrap">
                    <input v-model="push" type="checkbox" class="switch-input" />
                    <div class="switch-rail"><div class="switch-dot"></div></div>
                </label>
            </div>

            <div class="notification-option-row">
                <div class="option-copy">
                    <strong class="option-title">Alertas por Correo Electrónico</strong>
                    <span class="option-desc">Envío de notificaciones inmediatas a tu bandeja al crearse o resolverse tickets.</span>
                </div>
                <label class="switch-wrap">
                    <input v-model="emailTickets" type="checkbox" class="switch-input" />
                    <div class="switch-rail"><div class="switch-dot"></div></div>
                </label>
            </div>

            <div class="notification-option-row">
                <div class="option-copy">
                    <strong class="option-title">Resumen Semanal de Actividad</strong>
                    <span class="option-desc">Recibe cada lunes una compilación de incidencias atendidas y métricas del equipo.</span>
                </div>
                <label class="switch-wrap">
                    <input v-model="weeklyDigest" type="checkbox" class="switch-input" />
                    <div class="switch-rail"><div class="switch-dot"></div></div>
                </label>
            </div>

            <div class="form-actions">
                <button class="btn-save" type="submit">Guardar Preferencias</button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.settings-subpane {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    padding: 24px;
    box-shadow: none !important;
}
.subpane-header {
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}
.section-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 4px 0;
}
.section-subtitle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.notification-option-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 12px;
    margin-bottom: 14px;
    box-shadow: none !important;
}
.option-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.option-title {
    font-size: 13px;
    color: var(--text, #f4f4f6);
}
.option-desc {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.switch-wrap {
    cursor: pointer;
    user-select: none;
}
.switch-input {
    display: none;
}
.switch-rail {
    width: 38px;
    height: 22px;
    border-radius: 12px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    position: relative;
    transition: all 0.2s ease;
    box-shadow: none !important;
}
.switch-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--text-muted, #8e9199);
    position: absolute;
    top: 2px;
    left: 2px;
    transition: transform 0.2s ease, background 0.2s ease;
}
.switch-input:checked + .switch-rail {
    background: rgba(79, 70, 229, 0.2);
    border-color: #4f46e5;
}
.switch-input:checked + .switch-rail .switch-dot {
    transform: translateX(16px);
    background: #4f46e5;
}

.form-actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}
.btn-save {
    background: #4f46e5;
    border: var(--stroke-w, 2px) solid #4f46e5;
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 700 !important;
    cursor: pointer;
    box-shadow: none !important;
}
</style>
