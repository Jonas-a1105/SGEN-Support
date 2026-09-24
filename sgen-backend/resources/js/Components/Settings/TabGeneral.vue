<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    language: string;
    timezone: string;
    dateFormat: string;
}>();

const emit = defineEmits<{
    (e: 'save', payload: { language: string; timezone: string; date_format: string }): void;
}>();

const lang = ref(props.language);
const tz = ref(props.timezone);
const df = ref(props.dateFormat);

function save() {
    emit('save', {
        language: lang.value,
        timezone: tz.value,
        date_format: df.value,
    });
}
</script>

<template>
    <div class="settings-subpane">
        <div class="subpane-header">
            <h3 class="section-title">General</h3>
            <span class="section-subtitle">Idioma, región y formatos de fecha del sistema.</span>
        </div>

        <form class="settings-form" @submit.prevent="save">
            <div class="form-group">
                <label class="form-label" for="selectLanguage">Idioma de la interfaz</label>
                <select id="selectLanguage" v-model="lang" class="form-select">
                    <option value="es">Español (Latinoamérica)</option>
                    <option value="en">English (United States)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="selectTimezone">Zona horaria</label>
                <select id="selectTimezone" v-model="tz" class="form-select">
                    <option value="America/Caracas">America/Caracas (UTC-4)</option>
                    <option value="America/Bogota">America/Bogota (UTC-5)</option>
                    <option value="America/Mexico_City">America/Mexico_City (UTC-6)</option>
                    <option value="America/Argentina/Buenos_Aires">America/Buenos_Aires (UTC-3)</option>
                    <option value="UTC">UTC (Universal Time)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="selectDateFormat">Formato de fecha</label>
                <select id="selectDateFormat" v-model="df" class="form-select">
                    <option value="d/m/Y">DD/MM/AAAA (Ej: 23/09/2026)</option>
                    <option value="Y-m-d">AAAA-MM-DD (Ej: 2026-09-23)</option>
                    <option value="m/d/Y">MM/DD/AAAA (Ej: 09/23/2026)</option>
                </select>
            </div>

            <div class="form-actions">
                <button class="btn-save" type="submit">Guardar Cambios</button>
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
.form-group {
    margin-bottom: 18px;
}
.form-label {
    display: block;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin-bottom: 6px;
}
.form-select {
    width: 100%;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    padding: 10px 14px;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}
.form-select:focus {
    border-color: #4f46e5;
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
    transition: all 0.15s ease;
}
.btn-save:hover {
    background: #4338ca;
    border-color: #4338ca;
}
</style>
