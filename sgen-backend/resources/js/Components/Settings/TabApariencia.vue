<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = defineProps<{
    theme: string;
    strokeWidth: string;
    accentColor: string;
}>();

const emit = defineEmits<{
    (e: 'save', payload: { theme: string; stroke_width: string; accent_color: string }): void;
}>();

const activeTheme = ref(props.theme);
const activeStroke = ref(props.strokeWidth);
const activeAccent = ref(props.accentColor);
const activeSidebarMode = ref<'hover' | 'manual'>('hover');

onMounted(() => {
    const stored = localStorage.getItem('sgen_sidebar_mode');
    if (stored === 'manual' || stored === 'hover') {
        activeSidebarMode.value = stored;
    }
});

function setSidebarMode(mode: 'hover' | 'manual') {
    activeSidebarMode.value = mode;
    localStorage.setItem('sgen_sidebar_mode', mode);
    window.dispatchEvent(new CustomEvent('sgen-sidebar-mode', { detail: mode }));
}

const accentPresets = [
    { hex: '#4f46e5', title: 'Índigo Institucional', class: 'swatch-indigo' },
    { hex: '#2563eb', title: 'Azul Real', class: 'swatch-blue' },
    { hex: '#10b981', title: 'Verde Esmeralda', class: 'swatch-green' },
    { hex: '#f59e0b', title: 'Ámbar Cálido', class: 'swatch-amber' },
    { hex: '#8b5cf6', title: 'Púrpura Vibrante', class: 'swatch-purple' },
    { hex: '#ef4444', title: 'Carmesí', class: 'swatch-red' },
];

function save() {
    localStorage.setItem('sgen_sidebar_mode', activeSidebarMode.value);
    window.dispatchEvent(new CustomEvent('sgen-sidebar-mode', { detail: activeSidebarMode.value }));
    emit('save', {
        theme: activeTheme.value,
        stroke_width: activeStroke.value,
        accent_color: activeAccent.value,
    });
}
</script>

<template>
    <div class="settings-subpane">
        <div class="subpane-header">
            <h3 class="section-title">Apariencia</h3>
            <span class="section-subtitle">Personaliza los temas visuales, grosor de bordes y colores de acento.</span>
        </div>

        <form class="settings-form" @submit.prevent="save">
            <!-- Selector de Tema -->
            <div class="setting-block">
                <span class="block-label">Tema de interfaz</span>
                <div class="pill-group">
                    <button
                        type="button"
                        class="pill-btn"
                        :class="{ selected: activeTheme === 'dark' }"
                        @click="activeTheme = 'dark'"
                    >
                        <svg viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" /></svg>
                        <span>Oscuro</span>
                    </button>
                    <button
                        type="button"
                        class="pill-btn"
                        :class="{ selected: activeTheme === 'light' }"
                        @click="activeTheme = 'light'"
                    >
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5" /><line x1="12" y1="1" x2="12" y2="3" /><line x1="12" y1="21" x2="12" y2="23" /><line x1="4.22" y1="4.22" x2="5.64" y2="5.64" /><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" /><line x1="1" y1="12" x2="3" y2="12" /><line x1="21" y1="12" x2="23" y2="12" /><line x1="4.22" y1="19.78" x2="5.64" y2="18.36" /><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" /></svg>
                        <span>Claro</span>
                    </button>
                </div>
            </div>

            <!-- Grosor de Bordes -->
            <div class="setting-block">
                <span class="block-label">Grosor de bordes del sistema</span>
                <div class="pill-group">
                    <button
                        type="button"
                        class="pill-btn"
                        :class="{ selected: activeStroke === '2px' }"
                        @click="activeStroke = '2px'"
                    >
                        <span>2px (Estándar)</span>
                    </button>
                    <button
                        type="button"
                        class="pill-btn"
                        :class="{ selected: activeStroke === '1px' }"
                        @click="activeStroke = '1px'"
                    >
                        <span>1px (Fino)</span>
                    </button>
                </div>
            </div>

            <!-- Comportamiento del Menú Lateral -->
            <div class="setting-block">
                <span class="block-label">Comportamiento del menú lateral</span>
                <div class="pill-group">
                    <button
                        type="button"
                        class="pill-btn"
                        :class="{ selected: activeSidebarMode === 'hover' }"
                        @click="setSidebarMode('hover')"
                    >
                        <svg viewBox="0 0 24 24"><path d="M3 3l7 18 3-7 7-3L3 3z" /></svg>
                        <span>Hover Dinámico</span>
                    </button>
                    <button
                        type="button"
                        class="pill-btn"
                        :class="{ selected: activeSidebarMode === 'manual' }"
                        @click="setSidebarMode('manual')"
                    >
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                        <span>Manual / Fijo</span>
                    </button>
                </div>
                <span class="setting-hint">
                    {{ activeSidebarMode === 'hover'
                        ? 'El menú se expande automáticamente al pasar el cursor y se colapsa al salir.'
                        : 'El menú permanece fijo. Solo se abre o cierra manualmente al pulsar el botón del menú lateral.' }}
                </span>
            </div>

            <!-- Color de Acento -->
            <div class="setting-block">
                <span class="block-label">Color de acento institucional</span>
                <div class="color-options-row">
                    <input
                        v-model="activeAccent"
                        type="text"
                        class="form-input hex-input"
                        required
                    />
                    <input
                        v-model="activeAccent"
                        type="color"
                        class="native-color-picker"
                        aria-label="Selector de acento"
                    />
                    <div class="color-swatches-grid">
                        <button
                            v-for="preset in accentPresets"
                            :key="preset.hex"
                            type="button"
                            class="color-swatch-circle"
                            :class="[preset.class, { selected: activeAccent === preset.hex }]"
                            :title="preset.title"
                            @click="activeAccent = preset.hex"
                        ></button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn-save" type="submit">Aplicar y Guardar</button>
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
.setting-block {
    margin-bottom: 22px;
    padding: 16px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 12px;
    box-shadow: none !important;
}
.block-label {
    display: block;
    font-size: 13px;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin-bottom: 12px;
}
.pill-group {
    display: flex;
    gap: 10px;
}
.pill-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    color: var(--text-muted, #8e9199);
    font-size: 13px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}
.pill-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.pill-btn.selected {
    border-color: #4f46e5;
    color: #ffffff;
    background: rgba(79, 70, 229, 0.15);
}

.color-options-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.hex-input {
    max-width: 120px;
    font-family: monospace;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 8px;
    padding: 8px 12px;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}
.native-color-picker {
    width: 38px;
    height: 38px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 8px;
}
.color-swatches-grid {
    display: flex;
    gap: 8px;
}
.color-swatch-circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    box-shadow: none !important;
}
.color-swatch-circle.selected {
    border-color: #ffffff;
}
.swatch-indigo { background: #4f46e5; }
.swatch-blue { background: #2563eb; }
.swatch-green { background: #10b981; }
.swatch-amber { background: #f59e0b; }
.swatch-purple { background: #8b5cf6; }
.swatch-red { background: #ef4444; }

.form-actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}
.setting-hint {
    display: block;
    margin-top: 10px;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    line-height: 1.4;
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
