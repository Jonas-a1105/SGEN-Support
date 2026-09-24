<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseButton, BaseBadge } from '@/Components/UI';
import type { DepartmentDetail } from './types';

defineProps<{
    department: DepartmentDetail;
}>();

const emit = defineEmits<{
    (e: 'edit'): void;
    (e: 'assign'): void;
}>();
</script>

<template>
    <header class="dept-page-header">
        <div class="dept-header-left">
            <Link href="/departamentos" class="dept-back-link">
                <svg class="header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver a Departamentos</span>
            </Link>

            <div class="dept-title-row">
                <div class="dept-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="2"></line>
                        <line x1="15" y1="22" x2="15" y2="2"></line>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="8" y1="10" x2="16" y2="10"></line>
                        <line x1="8" y1="14" x2="16" y2="14"></line>
                    </svg>
                </div>
                <div class="dept-title-text-group">
                    <div class="dept-headline-tags">
                        <h1 class="dept-main-title">{{ department.nombre }}</h1>
                        <BaseBadge variant="code" size="md">{{ department.code }}</BaseBadge>
                    </div>
                    <div class="dept-meta-info">
                        <span class="dept-location-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>{{ department.ubicacion || 'Sin ubicación física' }}</span>
                        </span>
                        <span v-if="department.createdAt" class="dept-created-date">
                            Registrado el {{ department.createdAt }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="dept-header-actions">
            <BaseButton variant="subtle" size="md" @click="emit('edit')">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Editar Info</span>
            </BaseButton>
            <BaseButton variant="primary" size="md" @click="emit('assign')">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Asignar Recurso</span>
            </BaseButton>
        </div>
    </header>
</template>

<style scoped>
.dept-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
}

.dept-header-left {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.dept-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.15s ease;
}

.dept-back-link:hover {
    color: var(--blue, #3b82f6);
}

.header-icon-svg {
    width: 16px;
    height: 16px;
}

.dept-title-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.dept-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba(37, 99, 235, 0.12);
    border: var(--stroke-w) solid rgba(37, 99, 235, 0.25);
    color: var(--blue, #3b82f6);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.dept-icon-box svg {
    width: 26px;
    height: 26px;
}

.dept-title-text-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.dept-headline-tags {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.dept-main-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    letter-spacing: -0.02em;
}

.dept-meta-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.dept-location-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--stroke-subtle);
    padding: 3px 10px;
    border-radius: 6px;
    border: var(--stroke-w) solid var(--stroke);
}

.dept-location-pill svg {
    width: 13px;
    height: 13px;
    color: var(--blue, #3b82f6);
}

.dept-created-date {
    font-size: 12px;
    color: var(--text-muted);
}

.dept-header-actions {
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
