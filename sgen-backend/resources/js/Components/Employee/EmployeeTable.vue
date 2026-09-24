<script setup lang="ts">
import type { EmployeeItem } from '@/Composables/useEmployeeFilters';

defineProps<{
    employees: EmployeeItem[];
}>();

const emit = defineEmits<{
    (e: 'edit', employee: EmployeeItem): void;
    (e: 'delete', employee: EmployeeItem): void;
}>();
</script>

<template>
    <section class="employee-table-card" aria-label="Directorio de personal en tabla">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>EMPLEADO</th>
                        <th>CARGO</th>
                        <th>DEPARTAMENTO</th>
                        <th>CORREO</th>
                        <th>CÉDULA / ID</th>
                        <th>ESTADO ACCESO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="emp in employees" :key="emp.numericId">
                        <td>
                            <div class="emp-table-user">
                                <div class="emp-table-avatar" :class="emp.tint">
                                    {{ emp.initials }}
                                </div>
                                <div class="emp-table-info">
                                    <strong class="emp-table-name">{{ emp.fullName }}</strong>
                                    <span class="emp-table-id">{{ emp.id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ emp.position }}</td>
                        <td>
                            <span class="badge-code">{{ emp.dept }}</span>
                        </td>
                        <td class="text-muted-cell">{{ emp.email }}</td>
                        <td>{{ emp.idDoc }}</td>
                        <td>
                            <span class="access-pill" :class="emp.userAccount ? 'active' : 'inactive'">
                                {{ emp.userAccount || 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="table-actions-group">
                                <button
                                    class="btn-mini-action edit"
                                    @click="emit('edit', emp)"
                                    type="button"
                                    title="Editar ficha"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button
                                    class="btn-mini-action delete"
                                    @click="emit('delete', emp)"
                                    type="button"
                                    title="Eliminar registro"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
.employee-table-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    overflow: hidden;
    box-shadow: none !important;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.custom-table th {
    padding: 12px 16px;
    color: var(--text-dim);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-bottom: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
}

.custom-table td {
    padding: 12px 16px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    color: var(--text);
    vertical-align: middle;
}

.custom-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.emp-table-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.emp-table-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 11px;
    font-weight: 700;
    border: var(--stroke-w) solid var(--stroke);
}

.emp-table-avatar.blue {
    background: rgba(37, 99, 235, 0.15);
    color: #3b82f6;
}

.emp-table-avatar.green {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.emp-table-avatar.purple {
    background: rgba(139, 92, 246, 0.15);
    color: #8b5cf6;
}

.emp-table-avatar.orange {
    background: rgba(249, 115, 22, 0.15);
    color: #f97316;
}

.emp-table-avatar.amber {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.emp-table-info {
    display: flex;
    flex-direction: column;
}

.emp-table-name {
    color: var(--text);
    font-size: 13px;
}

.emp-table-id {
    font-size: 11px;
    color: var(--text-muted);
}

.badge-code {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
}

.text-muted-cell {
    color: var(--text-muted);
}

.access-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.access-pill.active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.access-pill.inactive {
    background: var(--stroke-subtle);
    color: var(--text-muted);
}

.table-actions-group {
    display: flex;
    gap: 6px;
}

.btn-mini-action {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-mini-action svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.btn-mini-action:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.btn-mini-action.delete:hover {
    color: var(--red);
    border-color: var(--red);
}
</style>
