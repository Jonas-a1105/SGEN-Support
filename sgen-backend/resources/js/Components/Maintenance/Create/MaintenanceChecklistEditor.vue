<script setup lang="ts">
import { ref } from 'vue';
import type { ChecklistTask } from './types';

const props = defineProps<{
    checklist: ChecklistTask[];
}>();

const emit = defineEmits<{
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
</template>

<style scoped>
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

.form-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--radius-sm, 6px);
    border: 1px solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.form-input:focus {
    border-color: var(--orange);
}

.btn-add-task {
    height: 38px;
    padding: 0 16px;
    border-radius: var(--radius-sm, 6px);
    border: 1px solid var(--stroke);
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
    border-color: var(--stroke);
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
    border-radius: var(--radius-sm, 6px);
    border: 1px solid var(--stroke);
    background: var(--bg-card);
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
</style>
