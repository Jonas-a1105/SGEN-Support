<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseButton, BaseCard } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    equipment: EquipmentDetail;
}>();

const emit = defineEmits<{
    (e: 'reassign'): void;
}>();
</script>

<template>
    <BaseCard title="Ubicación y Asignación" variant="glass" padding="md">
        <template #actions>
            <BaseButton variant="subtle" size="sm" @click="emit('reassign')">
                Reasignar
            </BaseButton>
        </template>

        <div class="custody-info-stack">
            <div class="custody-item">
                <div class="custody-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="2"></line>
                        <line x1="15" y1="22" x2="15" y2="2"></line>
                    </svg>
                </div>
                <div class="custody-details">
                    <span class="custody-label">DEPARTAMENTO</span>
                    <Link
                        v-if="equipment.departmentId"
                        :href="`/departamentos/${equipment.departmentId}`"
                        class="custody-link"
                    >
                        {{ equipment.departmentName }}
                    </Link>
                    <span v-else class="custody-unassigned">No Asignado</span>
                    <span class="custody-sub">{{ equipment.physicalLocation || 'Sin ubicación específica' }}</span>
                </div>
            </div>

            <div class="custody-item">
                <div class="custody-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="custody-details">
                    <span class="custody-label">CUSTODIO ASIGNADO</span>
                    <Link
                        v-if="equipment.employeeId"
                        :href="`/personal/${equipment.employeeId}`"
                        class="custody-link"
                    >
                        {{ equipment.employeeName }}
                    </Link>
                    <span v-else class="custody-unassigned">Sin custodio directo</span>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.custody-info-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.custody-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
}

.custody-icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.custody-icon svg {
    width: 18px;
    height: 18px;
}

.custody-icon.blue {
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
}

.custody-icon.orange {
    background: rgba(249, 115, 22, 0.12);
    color: #f97316;
}

.custody-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.custody-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: var(--text-dim);
}

.custody-link {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.custody-link:hover {
    color: var(--brand);
}

.custody-unassigned {
    font-size: 13px;
    color: var(--text-muted);
}

.custody-sub {
    font-size: 12px;
    color: var(--text-muted);
}
</style>
