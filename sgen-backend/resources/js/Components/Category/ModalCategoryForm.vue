<script setup lang="ts">
import { ref, watch } from 'vue';
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
    <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal" role="dialog" aria-modal="true" :aria-label="category ? 'Editar Categoría' : 'Nueva Categoría'">
            <div class="modal-head">
                <h3 class="modal-title">{{ category ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
                <button class="icon-close" type="button" @click="emit('close')">×</button>
            </div>

            <form @submit.prevent="handleSubmit">
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

                <div class="modal-actions">
                    <button class="btn-cancel" type="button" @click="emit('close')">Cancelar</button>
                    <button class="btn-save" type="submit">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: grid;
    place-items: center;
    z-index: 100;
    padding: 16px;
    box-shadow: none !important;
}
.modal {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    width: 100%;
    max-width: 500px;
    padding: 24px;
    box-shadow: none !important;
}
.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.modal-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}
.icon-close {
    background: transparent;
    border: none;
    color: var(--text-muted, #8e9199);
    font-size: 20px;
    cursor: pointer;
}
.form-group {
    margin-bottom: 16px;
}
.form-label {
    display: block;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin-bottom: 6px;
}
.form-input, .form-textarea {
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
.form-input:focus, .form-textarea:focus {
    border-color: #0d6efd;
}
.icon-presets-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
}
.icon-preset-btn {
    height: 42px;
    border-radius: 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    display: grid;
    place-items: center;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    box-shadow: none !important;
}
.icon-preset-btn svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.icon-preset-btn.selected {
    border-color: #0d6efd;
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.1);
}
.color-picker-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.hex-input {
    max-width: 110px;
    font-family: monospace;
}
.native-color-picker {
    width: 40px;
    height: 40px;
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
.swatch-red { background: #dc3545; }
.swatch-yellow { background: #ffc107; }
.swatch-gray { background: #6c757d; }
.swatch-green { background: #198754; }
.swatch-blue { background: #0d6efd; }
.swatch-purple { background: #8b5cf6; }

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}
.btn-cancel {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text, #f4f4f6);
    border-radius: 10px;
    padding: 10px 18px;
    cursor: pointer;
    font-size: 13px;
    box-shadow: none !important;
}
.btn-save {
    background: #0d6efd;
    border: var(--stroke-w, 2px) solid #0d6efd;
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700 !important;
    box-shadow: none !important;
}
</style>
