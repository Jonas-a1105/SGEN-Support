<script setup lang="ts">
import { useTheme } from '@/Composables/useTheme';
import BaseButton from '@/Components/UI/BaseButton.vue';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const {
    theme,
    borderWidth,
    accentColor,
    applyTheme,
    applyBorderWidth,
    applyAccentColor,
} = useTheme();

const presets = [
    { hex: '#74b300', class: 'swatch-lime' },
    { hex: '#ff5a00', class: 'swatch-orange' },
    { hex: '#0070f3', class: 'swatch-blue' },
    { hex: '#10b981', class: 'swatch-emerald' },
    { hex: '#7928ca', class: 'swatch-purple' },
    { hex: '#f5a623', class: 'swatch-amber' },
    { hex: '#ec4899', class: 'swatch-pink' },
];

const onColorInput = (e: Event) => {
    const target = e.target as HTMLInputElement | null;
    if (target) {
        applyAccentColor(target.value);
    }
};
</script>

<template>
    <div v-if="show" class="appearance-backdrop show" @click.self="emit('close')">
        <div class="appearance-panel" role="dialog" aria-modal="true" aria-labelledby="appearanceTitle">
            <div class="appearance-head">
                <div>
                    <div class="appearance-kicker">Personalización</div>
                    <h3 id="appearanceTitle" class="appearance-title">Estilo, Color y Bordes</h3>
                </div>
                <button class="icon-close" type="button" aria-label="Cerrar modal" @click="emit('close')">×</button>
            </div>

            <div class="appearance-grid">
                <!-- Grosor de bordes -->
                <div class="appearance-block">
                    <span class="appearance-label">Grosor de bordes de interfaz</span>
                    <div class="mode-buttons">
                        <button
                            class="border-btn"
                            :class="{ active: borderWidth === '2px' }"
                            type="button"
                            @click="applyBorderWidth('2px')"
                        >
                            2px (Estándar)
                        </button>
                        <button
                            class="border-btn"
                            :class="{ active: borderWidth === '1px' }"
                            type="button"
                            @click="applyBorderWidth('1px')"
                        >
                            1px (Fino)
                        </button>
                    </div>
                </div>

                <!-- Color de acento -->
                <div class="appearance-block">
                    <span class="appearance-label">Color principal de acento</span>
                    <div class="color-editor">
                        <input
                            type="color"
                            class="color-input"
                            :value="accentColor"
                            @input="onColorInput"
                            aria-label="Selector de color hex"
                        />
                        <div class="color-meta">
                            <span class="accent-hex">{{ accentColor.toUpperCase() }}</span>
                            <span class="subtle">Se actualiza en gradientes, barras e indicadores.</span>
                        </div>
                    </div>
                    <div class="swatches">
                        <button
                            v-for="item in presets"
                            :key="item.hex"
                            type="button"
                            class="swatch"
                            :class="item.class"
                            :title="item.hex"
                            @click="applyAccentColor(item.hex)"
                        />
                    </div>
                </div>

                <!-- Tema claro / oscuro -->
                <div class="appearance-block">
                    <span class="appearance-label">Tema del Dashboard</span>
                    <div class="mode-buttons">
                        <button
                            class="mode-btn"
                            :class="{ active: theme === 'dark' }"
                            type="button"
                            @click="applyTheme('dark')"
                        >
                            Oscuro
                        </button>
                        <button
                            class="mode-btn"
                            :class="{ active: theme === 'light' }"
                            type="button"
                            @click="applyTheme('light')"
                        >
                            Claro
                        </button>
                    </div>
                </div>
            </div>

            <div class="appearance-footer">
                <span class="footer-note">Ajustes guardados localmente</span>
                <BaseButton variant="primary" size="md" @click="emit('close')">
                    Completar
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<style scoped>
.appearance-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: grid;
    place-items: center;
    z-index: 90;
    transition: opacity var(--transition-normal);
}

.appearance-panel {
    width: min(460px, calc(100vw - 32px));
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: 24px;
    padding: 22px;
}

.appearance-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: 12px;
}

.appearance-kicker {
    font-size: 11px;
    color: var(--orange);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 2px;
    font-weight: 700;
}

.appearance-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.icon-close {
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    transition: color var(--transition-fast);
}

.icon-close:hover {
    color: var(--text);
}

.appearance-grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-top: 14px;
}

.appearance-block {
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--radius-lg);
    padding: 14px;
    background: transparent;
}

.appearance-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 10px;
    display: block;
    font-weight: 500;
}

.color-editor {
    display: flex;
    align-items: center;
    gap: 12px;
}

.color-input {
    width: 44px;
    height: 38px;
    padding: 2px;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    background: transparent;
    cursor: pointer;
}

.color-meta {
    min-width: 0;
}

.accent-hex {
    font-size: 13px;
    display: block;
    font-weight: 700;
    color: var(--text);
    font-family: var(--font-mono);
}

.subtle {
    font-size: 12px;
    color: var(--text-muted);
}

.swatches {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.swatch {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: var(--stroke-w) solid var(--stroke);
    cursor: pointer;
    transition: transform 0.16s ease;
}

.swatch:hover {
    transform: scale(1.15);
}

.swatch-lime { background-color: #74b300; }
.swatch-orange { background-color: #ff5a00; }
.swatch-blue { background-color: #0070f3; }
.swatch-emerald { background-color: #10b981; }
.swatch-purple { background-color: #7928ca; }
.swatch-amber { background-color: #f5a623; }
.swatch-pink { background-color: #ec4899; }

.mode-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.mode-btn, .border-btn {
    height: 38px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    border-radius: var(--radius-md);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mode-btn:hover, .border-btn:hover {
    border-color: var(--stroke-hover);
}

.mode-btn.active, .border-btn.active {
    border-color: var(--orange);
    color: var(--orange);
    background: var(--accent-soft);
    font-weight: 600;
}

.appearance-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.footer-note {
    font-size: 12px;
    color: var(--text-muted);
}
</style>
