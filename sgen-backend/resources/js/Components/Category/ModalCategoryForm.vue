<script setup lang="ts">
import { ref, watch } from 'vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
import type { CategoryItem } from '@/Composables/useCategoryFilters';

const props = defineProps<{
    show: boolean;
    category?: CategoryItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', payload: {
        id?: number;
        nombre: string;
        descripcion: string | null;
        icono: string;
        color: string;
    }): void;
}>();

const formName = ref('');
const formDesc = ref('');
const formIcon = ref('hardware');
const formColor = ref('#0d6efd');

const colorPresets = [
    { hex: '#dc3545', title: 'Rojo Hardware', class: 'swatch-red' },
    { hex: '#ffc107', title: 'Amarillo Impresora', class: 'swatch-yellow' },
    { hex: '#6c757d', title: 'Gris Periféricos', class: 'swatch-gray' },
    { hex: '#198754', title: 'Verde Red', class: 'swatch-green' },
    { hex: '#0d6efd', title: 'Azul Software', class: 'swatch-blue' },
    { hex: '#8b5cf6', title: 'Púrpura Servidores', class: 'swatch-purple' },
];

watch(
    () => props.category,
    (cat) => {
        if (cat) {
            formName.value = cat.nombre;
            formDesc.value = cat.descripcion || '';
            formIcon.value = cat.icono || 'hardware';
            formColor.value = cat.color || '#0d6efd';
        } else {
            formName.value = '';
            formDesc.value = '';
            formIcon.value = 'hardware';
            formColor.value = '#0d6efd';
        }
    },
    { immediate: true }
);

function handleSubmit() {
    emit('save', {
        id: props.category?.id,
        nombre: formName.value.trim(),
        descripcion: formDesc.value.trim() || null,
        icono: formIcon.value,
        color: formColor.value,
    });
}
</script>

<template>
    <BaseModal
        :is-open="show"
        :title="category ? 'Editar Categoría' : 'Nueva Categoría'"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSubmit" class="category-modal-form">
            <div class="form-group">
                <label class="form-label" for="inputCatName">Nombre de la Categoría</label>
                <input
                    id="inputCatName"
                    v-model="formName"
                    type="text"
                    class="form-input"
                    placeholder="Ej: Hardware, Red, Servidores..."
                    required
                />
            </div>

            <div class="form-group">
                <label class="form-label" for="inputCatDesc">Descripción</label>
                <textarea
                    id="inputCatDesc"
                    v-model="formDesc"
                    class="form-textarea"
                    rows="3"
                    placeholder="Indique brevemente el tipo de incidencias que agrupa..."
                ></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Icono Representativo</label>
                <div class="icon-presets-grid">
                    <button
                        type="button"
                        class="icon-preset-btn"
                        :class="{ selected: formIcon === 'hardware' }"
                        title="Hardware / Chip"
                        @click="formIcon = 'hardware'"
                    >
                        <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" /><rect x="9" y="9" width="6" height="6" /><line x1="9" y1="1" x2="9" y2="4" /><line x1="15" y1="1" x2="15" y2="4" /><line x1="9" y1="20" x2="9" y2="23" /><line x1="15" y1="20" x2="15" y2="23" /><line x1="20" y1="9" x2="23" y2="9" /><line x1="20" y1="15" x2="23" y2="15" /><line x1="1" y1="9" x2="4" y2="9" /><line x1="1" y1="15" x2="4" y2="15" /></svg>
                    </button>
                    <button
                        type="button"
                        class="icon-preset-btn"
                        :class="{ selected: formIcon === 'printer' }"
                        title="Impresora"
                        @click="formIcon = 'printer'"
                    >
                        <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect x="6" y="14" width="12" height="8" /></svg>
                    </button>
                    <button
                        type="button"
                        class="icon-preset-btn"
                        :class="{ selected: formIcon === 'help' }"
                        title="Otro / Pregunta"
                        @click="formIcon = 'help'"
                    >
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                    </button>
                    <button
                        type="button"
                        class="icon-preset-btn"
                        :class="{ selected: formIcon === 'mouse' }"
                        title="Periféricos / Mouse"
                        @click="formIcon = 'mouse'"
                    >
                        <svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="7" /><line x1="12" y1="6" x2="12" y2="10" /></svg>
                    </button>
                    <button
                        type="button"
                        class="icon-preset-btn"
                        :class="{ selected: formIcon === 'wifi' }"
                        title="Red / Conectividad"
                        @click="formIcon = 'wifi'"
                    >
                        <svg viewBox="0 0 24 24"><path d="M5 12.55a11 11 0 0 1 14.08 0" /><path d="M1.42 9a16 16 0 0 1 21.16 0" /><path d="M8.53 16.11a6 6 0 0 1 6.95 0" /><line x1="12" y1="20" x2="12.01" y2="20" /></svg>
                    </button>
                    <button
                        type="button"
                        class="icon-preset-btn"
                        :class="{ selected: formIcon === 'window' }"
                        title="Software / Sistema"
                        @click="formIcon = 'window'"
                    >
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" /><line x1="3" y1="9" x2="21" y2="9" /><line x1="9" y1="21" x2="9" y2="9" /></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="inputCatHex">Color Identificador</label>
                <div class="color-picker-row">
                    <input
                        id="inputCatHex"
                        v-model="formColor"
                        type="text"
                        class="form-input hex-input"
                        required
                    />
                    <input
                        v-model="formColor"
                        type="color"
                        class="native-color-picker"
                        aria-label="Selector nativo de color"
                    />
                    <div class="color-swatches-grid">
                        <button
                            v-for="preset in colorPresets"
                            :key="preset.hex"
                            type="button"
                            class="color-swatch-circle"
                            :class="[preset.class, { selected: formColor === preset.hex }]"
                            :title="preset.title"
                            @click="formColor = preset.hex"
                        ></button>
                    </div>
                </div>
            </div>

            <div class="form-actions-row">
                <button class="btn-cancel" type="button" @click="emit('close')">Cancelar</button>
                <button class="btn-submit" type="submit">
                    <span>Guardar Categoría</span>
                </button>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.category-modal-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.icon-presets-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
    margin-top: 6px;
}

.icon-preset-btn {
    height: 44px;
    border-radius: 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.15s ease;
}

.icon-preset-btn svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.icon-preset-btn:hover {
    border-color: var(--stroke-hover, #454952);
    color: var(--text, #f4f4f6);
}

.icon-preset-btn.selected {
    border-color: var(--blue, #0d6efd);
    background: rgba(13, 110, 253, 0.15);
    color: #ffffff;
}

.color-picker-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 6px;
}

.hex-input {
    width: 100px;
    text-align: center;
    font-family: var(--font-mono, monospace);
    font-weight: 700;
}

.native-color-picker {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0;
    box-shadow: none !important;
}

.color-swatches-grid {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
}

.color-swatch-circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    box-shadow: none !important;
    transition: transform 0.15s ease;
}

.color-swatch-circle:hover {
    transform: scale(1.15);
}

.color-swatch-circle.selected {
    border-color: var(--text, #ffffff);
}

.swatch-red { background: #dc3545; }
.swatch-yellow { background: #ffc107; }
.swatch-gray { background: #6c757d; }
.swatch-green { background: #198754; }
.swatch-blue { background: #0d6efd; }
.swatch-purple { background: #8b5cf6; }
</style>
