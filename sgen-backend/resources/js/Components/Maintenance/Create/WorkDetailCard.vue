<script setup lang="ts">
import { ref } from 'vue';
import type { ChecklistTask } from './types';

const props = defineProps<{
    tipoMantenimiento: string;
    estado: string;
    descripcion: string;
    observaciones: string;
    checklist: ChecklistTask[];
}>();

const emit = defineEmits<{
    (e: 'update:tipoMantenimiento', val: string): void;
    (e: 'update:estado', val: string): void;
    (e: 'update:descripcion', val: string): void;
    (e: 'update:observaciones', val: string): void;
    (e: 'update:checklist', val: ChecklistTask[]): void;
}>();

const newTaskInput = ref('');
let nextTaskId = 100;

const addTask = () => {
    const text = newTaskInput.value.trim();
    if (!text) return;
    const updated = [...props.checklist, { id: nextTaskId++, text, done: false }];
    emit('update:checklist', updated);
    newTaskInput.value = '';
};

const removeTask = (id: number) => {
    emit('update:checklist', props.checklist.filter((t) => t.id !== id));
};

const toggleTask = (task: ChecklistTask) => {
    const updated = props.checklist.map((t) => (t.id === task.id ? { ...t, done: !t.done } : t));
    emit('update:checklist', updated);
};
</script>

<template>
    <div class="form-section-card">
        <div class="card-section-header">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
            <span>2. Detalle del Trabajo</span>
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label">Tipo de Mantenimiento</label>
                <div class="segmented-control">
                    <button
                        type="button"
                        class="segment-btn"
                        :class="{ active: tipoMantenimiento === 'preventivo' }"
                        @click="emit('update:tipoMantenimiento', 'preventivo')"
                    >
                        Preventivo
                    </button>
                    <button
                        type="button"
                        class="segment-btn"
                        :class="{ active: tipoMantenimiento === 'correctivo' }"
                        @click="emit('update:tipoMantenimiento', 'correctivo')"
                    >
                        Correctivo
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Estado Inicial</label>
                <select
                    :value="estado"
                    class="form-select"
                    @change="emit('update:estado', ($event.target as HTMLSelectElement).value)"
                >
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Progreso</option>
                    <option value="programado">Programado</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Descripción General</label>
            <textarea
                :value="descripcion"
                class="form-textarea"
                rows="3"
                placeholder="Describa el objetivo o la falla detectada..."
                required
                @input="emit('update:descripcion', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
        </div>

        <!-- CHECKLIST DE TAREAS -->
        <div class="checklist-box">
            <div class="checklist-header-row">
                <span class="checklist-title">
                    <svg viewBox="0 0 24 24" class="check-icon">
                        <polyline points="9 11 12 14 22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                    <span>Checklist de Tareas</span>
                </span>
                <span class="badge-code">{{ checklist.length }} tareas</span>
            </div>

            <div class="checklist-input-group">
                <input
                    v-model="newTaskInput"
                    type="text"
                    class="form-input"
                    placeholder="+ Añadir nueva tarea y presionar Enter..."
                    @keydown.enter.prevent="addTask"
                />
                <button class="btn-add-task" type="button" @click="addTask">
                    Añadir
                </button>
            </div>

            <div v-if="checklist.length > 0" class="checklist-items-list">
                <div
                    v-for="task in checklist"
                    :key="task.id"
                    class="checklist-item"
                    :class="{ done: task.done }"
                >
                    <label class="checklist-item-left">
                        <input
                            type="checkbox"
                            :checked="task.done"
                            class="task-checkbox"
                            @change="toggleTask(task)"
                        />
                        <span class="task-text">{{ task.text }}</span>
                    </label>
                    <button
                        type="button"
                        class="btn-remove-task"
                        title="Eliminar tarea"
                        @click="removeTask(task.id)"
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Observaciones Adicionales</label>
            <textarea
                :value="observaciones"
                class="form-textarea"
                rows="3"
                placeholder="Notas internas..."
                @input="emit('update:observaciones', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
        </div>
    </div>
</template>

<style scoped>
.form-section-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: none !important;
}

.card-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    padding-bottom: 14px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.card-section-header svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    color: var(--orange);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.02em;
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.segmented-control {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    background: var(--bg-sub);
    overflow: hidden;
    padding: 2px;
    gap: 2px;
    box-shadow: none !important;
}

.segment-btn {
    height: 36px;
    border: none;
    border-radius: 10px;
    background: transparent;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.segment-btn.active {
    background: var(--bg-card);
    color: var(--text);
    border: 1px solid var(--stroke);
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    border-color: var(--orange);
}

.checklist-box {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 4px;
}

.checklist-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.checklist-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}

.check-icon {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.badge-code {
    padding: 4px 8px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    border: 1px solid var(--stroke);
    font-size: 11px;
    color: var(--text-muted);
    box-shadow: none !important;
}

.checklist-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-add-task {
    height: 38px;
    padding: 0 16px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    box-shadow: none !important;
}

.btn-add-task:hover {
    background: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.checklist-items-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 4px;
}

.checklist-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 10px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    box-shadow: none !important;
}

.checklist-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    cursor: pointer;
}

.task-checkbox {
    cursor: pointer;
}

.task-text {
    color: var(--text);
}

.checklist-item.done .task-text {
    text-decoration: line-through;
    color: var(--text-muted);
}

.btn-remove-task {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0 4px;
    font-size: 16px;
    line-height: 1;
}

.btn-remove-task:hover {
    color: var(--red, #ef4444);
}

@media (max-width: 768px) {
    .form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
