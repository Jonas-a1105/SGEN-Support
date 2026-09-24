<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseCard } from '@/Components/UI';
import type { EmployeeDetail } from './types';

defineProps<{
    employee: EmployeeDetail;
}>();
</script>

<template>
    <div class="emp-sidebar-col">
        <BaseCard title="Datos de Contacto" variant="glass" padding="md">
            <div class="contact-card-stack">
                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <div class="contact-meta">
                        <span class="contact-label">CORREO ELECTRÓNICO</span>
                        <a :href="`mailto:${employee.email}`" class="contact-val email-link">
                            {{ employee.email }}
                        </a>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="contact-meta">
                        <span class="contact-label">TELÉFONO</span>
                        <span class="contact-val">{{ employee.telefono || 'Sin teléfono registrado' }}</span>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="2"></line>
                            <line x1="15" y1="22" x2="15" y2="2"></line>
                        </svg>
                    </div>
                    <div class="contact-meta">
                        <span class="contact-label">DEPARTAMENTO</span>
                        <Link
                            v-if="employee.departamentoId"
                            :href="`/departamentos/${employee.departamentoId}`"
                            class="contact-val dept-link"
                        >
                            {{ employee.departamentoNombre }}
                        </Link>
                        <span v-else class="contact-val muted">Sin departamento asignado</span>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="contact-meta">
                        <span class="contact-label">USUARIO DE SISTEMA</span>
                        <span class="contact-val mono">
                            {{ employee.username ? `@${employee.username}` : 'No vinculado a usuario' }}
                        </span>
                    </div>
                </div>
            </div>
        </BaseCard>
    </div>
</template>

<style scoped>
.emp-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.contact-card-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.contact-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.contact-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.contact-icon svg {
    width: 15px;
    height: 15px;
}

.contact-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.contact-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.05em;
}

.contact-val {
    font-size: 13px;
    color: var(--text);
    font-weight: 500;
}

.contact-val.muted {
    color: var(--text-muted);
    font-style: italic;
}

.contact-val.mono {
    font-family: monospace;
    color: var(--blue, #3b82f6);
    font-weight: 600;
}

.email-link {
    text-decoration: none;
    color: var(--text);
    transition: color 0.15s ease;
}

.email-link:hover {
    color: var(--blue, #3b82f6);
}

.dept-link {
    text-decoration: none;
    color: var(--blue, #3b82f6);
    font-weight: 600;
    transition: underline 0.15s ease;
}

.dept-link:hover {
    text-decoration: underline;
}
</style>
