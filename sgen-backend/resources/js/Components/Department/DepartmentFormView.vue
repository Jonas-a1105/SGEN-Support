<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { DepartmentItem } from '@/Composables/useDepartmentFilters';
import DepartmentManagerWidget from './DepartmentManagerWidget.vue';
import ModalSelectDepartmentLeader, { type LeadershipCandidate } from './ModalSelectDepartmentLeader.vue';

const props = defineProps<{
    editDepartment?: DepartmentItem | null;
    candidates: LeadershipCandidate[];
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'save', payload: {
        nombre: string;
        ubicacion: string;
        descripcion: string;
        jefe_area_nombre: string | null;
        jefe_area_id: number | null;
    }): void;
}>();

const name = ref('');
const location = ref('');
const desc = ref('');
const managerName = ref('');
const managerId = ref<number | null>(null);

const showCandidatesModal = ref(false);

watch(
    () => props.editDepartment,
    (dept) => {
        if (dept) {
            name.value = dept.name || '';
            location.value = dept.location || '';
            desc.value = dept.desc || '';
            managerName.value = dept.manager || '';
            managerId.value = dept.managerId || null;
        } else {
            name.value = '';
            location.value = '';
            desc.value = '';
            managerName.value = '';
            managerId.value = null;
        }
    },
    { immediate: true }
);

const managerInitials = computed(() => {
    if (!managerName.value) return '--';
    const parts = managerName.value.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
    }
    return managerName.value.substring(0, 2).toUpperCase();
});

const selectCandidate = (cand: LeadershipCandidate) => {
    managerName.value = cand.name;
    managerId.value = cand.id;
    showCandidatesModal.value = false;
};

const clearManager = () => {
    managerName.value = '';
    managerId.value = null;
};

const handleSubmit = () => {
    emit('save', {
        nombre: name.value,
        ubicacion: location.value || 'Edificio Central',
        descripcion: desc.value || `Gestión y administración del área de ${name.value.toLowerCase()}.`,
        jefe_area_nombre: managerName.value || null,
        jefe_area_id: managerId.value,
    });
};
</script>

<template>
    <section class="department-form-view" aria-label="Formulario de departamento">
        <!-- CABECERA DE LA VISTA -->
        <div class="module-header">
            <div class="header-breadcrumbs">
                <button class="btn-back" @click="emit('back')" type="button">
                    <span>←</span>
                    <span>Volver a Departamentos</span>
                </button>
                <div class="header-title-row">
                    <div class="module-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="2"></line>
                            <line x1="15" y1="22" x2="15" y2="2"></line>
                        </svg>
                    </div>
                    <div>
                        <h1 class="module-title">{{ editDepartment ? 'Editar Departamento' : 'Crear Nuevo Departamento' }}</h1>
                        <p class="module-subtitle">
                            {{ editDepartment ? `Modifica los parámetros organizativos de ${editDepartment.name}.` : 'Establece una nueva unidad organizativa.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="form-container-card">
            <div class="form-grid-inner">
                <div class="form-group">
                    <label class="form-label" for="inputDeptName">Nombre del Departamento</label>
                    <input
                        type="text"
                        class="form-input"
                        id="inputDeptName"
                        v-model="name"
                        placeholder="Ej. Departamento de Seguridad"
                        required
                        autocomplete="off"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label" for="inputDeptLocation">Ubicación Física</label>
                    <input
                        type="text"
                        class="form-input"
                        id="inputDeptLocation"
                        v-model="location"
                        placeholder="Ej. Edificio Central, Piso 2"
                        autocomplete="off"
                    />
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="textDeptDesc">Descripción del Departamento</label>
                    <textarea
                        class="form-textarea"
                        id="textDeptDesc"
                        v-model="desc"
                        rows="3"
                        placeholder="Describe el objetivo y alcance de esta unidad operativa..."
                    ></textarea>
                </div>

                <!-- RESPONSABLE / JEFE DE ÁREA WIDGET (Componente Modular) -->
                <div class="form-group full-width">
                    <label class="form-label">Responsable del Departamento</label>
                    <DepartmentManagerWidget
                        :manager-name="managerName"
                        :manager-initials="managerInitials"
                        @assign="showCandidatesModal = true"
                        @clear="clearManager"
                    />
                </div>
            </div>

            <div class="form-actions-row">
                <button class="btn-cancel" @click="emit('back')" type="button">Cancelar</button>
                <button class="btn-submit" type="submit">Guardar Cambios</button>
            </div>
        </form>

        <!-- MODAL DE SELECCIÓN DE LÍDER (Componente Modular) -->
        <ModalSelectDepartmentLeader
            :is-open="showCandidatesModal"
            :candidates="candidates"
            @close="showCandidatesModal = false"
            @select="selectCandidate"
        />
    </section>
</template>

<style scoped>
.department-form-view {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.header-breadcrumbs {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.btn-back {
    align-self: flex-start;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text);
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: var(--stroke-subtle);
    border-color: var(--stroke-hover);
}

.header-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}

.module-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--blue);
    display: grid;
    place-items: center;
    color: #ffffff;
    box-shadow: none !important;
}

.module-icon-box svg {
    width: 22px;
    height: 22px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.module-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: var(--text);
    margin: 0 0 2px 0;
}

.module-subtitle {
    font-size: 12px;
    color: var(--text-dim);
    margin: 0;
}

.form-container-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 24px;
    box-shadow: none !important;
}

.form-grid-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

@media (max-width: 640px) {
    .form-grid-inner {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.form-input,
.form-textarea {
    width: 100%;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    color: var(--text);
    font-size: 13px;
    padding: 10px 14px;
    outline: none;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.form-input {
    height: 40px;
}

.form-input:focus,
.form-textarea:focus {
    border-color: var(--blue);
}

.form-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.btn-cancel {
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.btn-submit {
    background: var(--blue);
    border: var(--stroke-w) solid var(--blue);
    color: #ffffff;
    font-size: 13px;
    padding: 8px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    box-shadow: none !important;
    transition: opacity 0.2s ease;
}

.btn-submit:hover {
    opacity: 0.9;
}
</style>
