<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseButton, BaseBadge } from '@/Components/UI';
import type { EmployeeDetail } from './types';

defineProps<{
    employee: EmployeeDetail;
}>();

const emit = defineEmits<{
    (e: 'edit'): void;
}>();

const getRoleBadgeVariant = (rol: string): 'accent' | 'success' | 'info' | 'neutral' => {
    switch (rol.toLowerCase()) {
        case 'administrador':
            return 'accent';
        case 'tecnico':
            return 'success';
        case 'consultor':
            return 'info';
        default:
            return 'neutral';
    }
};
</script>

<template>
    <header class="emp-page-header">
        <div class="emp-header-left">
            <Link href="/personal" class="emp-back-link">
                <svg class="header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver al Directorio de Personal</span>
            </Link>

            <div class="emp-title-row">
                <div class="emp-avatar-circle">
                    {{ employee.initials }}
                </div>
                <div class="emp-title-text-group">
                    <div class="emp-headline-tags">
                        <h1 class="emp-main-title">{{ employee.fullName }}</h1>
                        <BaseBadge variant="code" size="md">{{ employee.formattedId }}</BaseBadge>
                        <BaseBadge :variant="getRoleBadgeVariant(employee.rol)" size="md">
                            {{ employee.rol }}
                        </BaseBadge>
                    </div>
                    <div class="emp-meta-info">
                        <span class="emp-cargo-pill">{{ employee.cargo || 'Sin cargo asignado' }}</span>
                        <span v-if="employee.cedula" class="emp-cedula-pill">
                            Cédula: {{ employee.cedula }}
                        </span>
                        <span v-if="employee.departamentoNombre" class="emp-dept-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="2"></line>
                                <line x1="15" y1="22" x2="15" y2="2"></line>
                            </svg>
                            {{ employee.departamentoNombre }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="emp-header-actions">
            <BaseButton variant="subtle" size="md" @click="emit('edit')">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Editar Información</span>
            </BaseButton>
            <Link :href="`/soportes/crear?empleado_id=${employee.id}`">
                <BaseButton variant="primary" size="md">
                    <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Crear Ticket</span>
                </BaseButton>
            </Link>
        </div>
    </header>
</template>

<style scoped>
.emp-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
}

.emp-header-left {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.emp-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.15s ease;
}

.emp-back-link:hover {
    color: var(--blue, #3b82f6);
}

.header-icon-svg {
    width: 16px;
    height: 16px;
}

.emp-title-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.emp-avatar-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--blue, #3b82f6);
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.emp-title-text-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.emp-headline-tags {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.emp-main-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    letter-spacing: -0.02em;
}

.emp-meta-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.emp-cargo-pill {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
}

.emp-cedula-pill {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--stroke-subtle);
    padding: 2px 8px;
    border-radius: 6px;
    border: var(--stroke-w) solid var(--stroke);
}

.emp-dept-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: var(--blue, #3b82f6);
    background: rgba(37, 99, 235, 0.08);
    padding: 2px 8px;
    border-radius: 6px;
    border: var(--stroke-w) solid rgba(37, 99, 235, 0.2);
}

.emp-dept-pill svg {
    width: 13px;
    height: 13px;
}

.emp-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>
