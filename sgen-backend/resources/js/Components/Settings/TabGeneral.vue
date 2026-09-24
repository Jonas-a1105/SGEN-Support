<script setup lang="ts">
import { ref } from 'vue';
import { BaseButton, BaseCombobox } from '@/Components/UI';
import type { ComboboxOption } from '@/Components/UI';

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

const languageOptions: ComboboxOption[] = [
    { value: 'es', label: 'Español (Latinoamérica)' },
    { value: 'en', label: 'English (United States)' },
];

const timezoneOptions: ComboboxOption[] = [
    { value: 'America/Caracas', label: 'America/Caracas (UTC-4)' },
    { value: 'America/Bogota', label: 'America/Bogota (UTC-5)' },
    { value: 'America/Mexico_City', label: 'America/Mexico_City (UTC-6)' },
    { value: 'America/Argentina/Buenos_Aires', label: 'America/Buenos_Aires (UTC-3)' },
    { value: 'UTC', label: 'UTC (Universal Time)' },
];

const dateFormatOptions: ComboboxOption[] = [
    { value: 'd/m/Y', label: 'DD/MM/AAAA (Ej: 23/09/2026)' },
    { value: 'Y-m-d', label: 'AAAA-MM-DD (Ej: 2026-09-23)' },
    { value: 'm/d/Y', label: 'MM/DD/AAAA (Ej: 09/23/2026)' },
];

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
            <div class="form-stack">
                <BaseCombobox
                    v-model="lang"
                    label="Idioma de la interfaz"
                    :options="languageOptions"
                    :searchable="false"
                />

                <BaseCombobox
                    v-model="tz"
                    label="Zona horaria"
                    :options="timezoneOptions"
                    :searchable="true"
                />

                <BaseCombobox
                    v-model="df"
                    label="Formato de fecha"
                    :options="dateFormatOptions"
                    :searchable="false"
                />
            </div>

            <div class="form-actions">
                <BaseButton variant="primary" size="md" type="submit">
                    Guardar Cambios
                </BaseButton>
            </div>
        </form>
    </div>
</template>

<style scoped>
.settings-subpane {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 24px;
    box-shadow: none !important;
}

.subpane-header {
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.section-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text);
    margin: 0 0 4px 0;
}

.section-subtitle {
    font-size: 12px;
    color: var(--text-muted);
}

.form-stack {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}
</style>
