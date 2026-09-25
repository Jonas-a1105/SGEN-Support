<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseAvatar, BaseDataTable, type DataTableColumn } from '@/Components/UI';
import type { EmployeeItem } from '@/Composables/useEmployeeFilters';

defineProps<{
    employees: EmployeeItem[];
}>();

const emit = defineEmits<{
    (e: 'edit', employee: EmployeeItem): void;
    (e: 'delete', employee: EmployeeItem): void;
}>();

const columns: DataTableColumn[] = [
    { key: 'emp', label: 'EMPLEADO', sortable: true },
    { key: 'position', label: 'CARGO', sortable: true },
    { key: 'dept', label: 'DEPARTAMENTO' },
    { key: 'email', label: 'CORREO' },
    { key: 'idDoc', label: 'CÉDULA / ID', width: '130px' },
    { key: 'access', label: 'ESTADO ACCESO', width: '130px' },
    { key: 'actions', label: 'ACCIONES', width: '120px', align: 'right' },
];
</script>

<template>
    <section class="employee-table-wrap" aria-label="Directorio de personal en tabla">
        <BaseDataTable
            :columns="columns"
            :items="employees"
            :row-key="(emp) => emp.numericId"
            empty-title="No se encontraron colaboradores"
            empty-subtitle="Prueba con otros términos de búsqueda."
        >
            <template #cell-emp="{ item }">
                <div class="emp-table-user">
                    <BaseAvatar :name="item.fullName" :initials="item.initials" size="sm" />
                    <div class="emp-table-info">
                        <Link :href="`/personal/${item.numericId}`" class="emp-table-link">
                            <strong class="emp-table-name">{{ item.fullName }}</strong>
                        </Link>
                        <span class="emp-table-id">{{ item.id }}</span>
                    </div>
                </div>
            </template>

            <template #cell-position="{ item }">
                <span>{{ item.position }}</span>
            </template>

            <template #cell-dept="{ item }">
                <span class="badge-code">{{ item.dept }}</span>
            </template>

            <template #cell-email="{ item }">
                <span class="text-muted-cell">{{ item.email }}</span>
            </template>

            <template #cell-idDoc="{ item }">
                <span>{{ item.idDoc }}</span>
            </template>

            <template #cell-access="{ item }">
                <span class="access-pill" :class="item.userAccount ? 'active' : 'inactive'">
                    {{ item.userAccount || 'Inactivo' }}
                </span>
            </template>

            <template #cell-actions="{ item }">
                <div class="table-actions-group" @click.stop>
                    <Link
                        :href="`/personal/${item.numericId}`"
                        class="btn-mini-action view"
                        title="Ver perfil del colaborador"
                        @click.stop
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </Link>
                    <button
                        class="btn-mini-action edit"
                        @click.stop="emit('edit', item)"
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
                        @click.stop="emit('delete', item)"
                        type="button"
                        title="Eliminar registro"
                    >
                        <svg viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </BaseDataTable>
    </section>
</template>

<style scoped>
.emp-table-user {
    display: flex;
    align-items: center;
    gap: 10px;
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

.emp-table-link {
    text-decoration: none;
    color: inherit;
    display: inline-block;
}

.emp-table-link:hover .emp-table-name {
    color: #3b82f6;
}

.btn-mini-action:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.btn-mini-action.view:hover {
    color: #3b82f6;
    border-color: #3b82f6;
}

.btn-mini-action.delete:hover {
    color: var(--red);
    border-color: var(--red);
}
</style>
