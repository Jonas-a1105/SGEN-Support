<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton, BaseEmptyState, BaseSearchToolbar } from '@/Components/UI';
import type { DepartmentEmployee } from './types';

const props = defineProps<{
    empleados: DepartmentEmployee[];
}>();

const emit = defineEmits<{
    (e: 'assign'): void;
    (e: 'unlink', employee: DepartmentEmployee): void;
}>();

const employeeSearch = ref('');

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase();
    if (!q) return props.empleados;
    return props.empleados.filter((e) =>
        e.fullName.toLowerCase().includes(q) ||
        e.cargo.toLowerCase().includes(q) ||
        e.email.toLowerCase().includes(q) ||
        e.cedula.toLowerCase().includes(q)
    );
});
</script>

<template>
    <div class="tab-panel-employees">
        <BaseSearchToolbar
            v-model="employeeSearch"
            placeholder="Buscar colaboradores por nombre, cargo, email o cédula..."
        >
            <BaseButton variant="primary" size="md" @click="emit('assign')">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                <span>Vincular Colaborador</span>
            </BaseButton>
        </BaseSearchToolbar>

        <div v-if="filteredEmployees.length > 0" class="table-card">
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>COLABORADOR</th>
                            <th>CÉDULA</th>
                            <th>CARGO</th>
                            <th>CONTACTO</th>
                            <th>ROL</th>
                            <th class="text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="emp in filteredEmployees" :key="emp.id">
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-mini">{{ emp.initials }}</div>
                                    <div class="user-info">
                                        <span class="user-fullname">{{ emp.fullName }}</span>
                                        <span class="user-subtext">{{ emp.email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono-code">{{ emp.cedula || 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="role-badge">{{ emp.cargo }}</span>
                            </td>
                            <td>
                                <div class="contact-subinfo">
                                    <span>{{ emp.telefono || 'Sin teléfono' }}</span>
                                </div>
                            </td>
                            <td>
                                <BaseBadge variant="neutral" size="sm">{{ emp.rol }}</BaseBadge>
                            </td>
                            <td class="text-right">
                                <div class="table-actions">
                                    <Link :href="`/personal/${emp.id}`" class="action-btn-circle" title="Ver Perfil">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </Link>
                                    <button
                                        type="button"
                                        class="action-btn-circle action-danger"
                                        title="Desvincular del departamento"
                                        @click="emit('unlink', emp)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <BaseEmptyState
            v-else
            title="No se encontraron colaboradores"
            description="No hay personal asignado a este departamento que coincida con el criterio de búsqueda."
        >
            <BaseButton variant="primary" size="md" @click="emit('assign')">
                Vincular Primer Colaborador
            </BaseButton>
        </BaseEmptyState>
    </div>
</template>

<style scoped>
.tab-panel-employees {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.table-card {
    border-radius: 8px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.detail-table th {
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--stroke-subtle);
    border-bottom: var(--stroke-w) solid var(--stroke);
    letter-spacing: 0.05em;
}

.detail-table td {
    padding: 14px 16px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.detail-table tr:last-child td {
    border-bottom: none;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-mini {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue, #3b82f6);
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-fullname {
    font-weight: 600;
    color: var(--text);
}

.user-subtext {
    font-size: 11px;
    color: var(--text-muted);
}

.mono-code {
    font-family: monospace;
    font-size: 12px;
    color: var(--text-muted);
}

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    background: var(--stroke-subtle);
    color: var(--text-muted);
    border: var(--stroke-w) solid var(--stroke);
}

.contact-subinfo {
    font-size: 12px;
    color: var(--text-muted);
}

.text-right {
    text-align: right;
}

.table-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}

.action-btn-circle {
    display: inline-grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: none !important;
}

.action-btn-circle:hover {
    color: var(--blue, #3b82f6);
    border-color: var(--blue, #3b82f6);
}

.action-btn-circle.action-danger:hover {
    color: #ef4444;
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
}

.action-btn-circle svg {
    width: 14px;
    height: 14px;
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
}
</style>
